"""Private runtime services for an existing authenticated Factory MCP host.

No listener, credentials, automatic deployment or public upload endpoint.
Policies and identity extraction are injected by the trusted host at startup.
"""
from contextlib import contextmanager
from dataclasses import replace
import fcntl
import hashlib
import json
import os
from pathlib import Path
import secrets
import sqlite3
import time
from asset_ingest import MAX_BYTES
from asset_intake import AssetIntake, Grant, register_mcp
from asset_reference_store import StoredFile, StoredReferenceResolver


class PrivateRuntime:
    def __init__(self, state_root, policies, principal, *, daily_bytes=100 * 1024 * 1024):
        self.root = Path(state_root)
        if self.root.is_symlink():
            raise PermissionError('state directory cannot be a symlink')
        self.root.mkdir(mode=0o700, parents=False, exist_ok=True)
        self.root = self.root.resolve(strict=True)
        self._private(self.root)
        if not isinstance(daily_bytes, int) or daily_bytes <= 0:
            raise ValueError('positive daily quota required')
        self.daily_bytes = daily_bytes
        self.policies = tuple(policies)
        self.principal = principal
        for name in ('uploads', 'intake', 'locks'):
            directory = self.root / name
            if directory.is_symlink():
                raise PermissionError('unsafe runtime directory')
            directory.mkdir(mode=0o700, exist_ok=True)
            self._private(directory)
        self.db_path = self.root / 'catalog.sqlite'
        fd = os.open(self.db_path, os.O_WRONLY | os.O_CREAT | os.O_NOFOLLOW, 0o600)
        os.close(fd)
        self._private(self.db_path)
        with self.db() as db:
            db.executescript('''
              CREATE TABLE IF NOT EXISTS files (
                reference TEXT PRIMARY KEY, path TEXT NOT NULL, principal TEXT NOT NULL,
                expires REAL NOT NULL, sha256 TEXT NOT NULL, bytes INTEGER NOT NULL);
              CREATE TABLE IF NOT EXISTS usage (
                principal TEXT NOT NULL, day TEXT NOT NULL, bytes INTEGER NOT NULL,
                PRIMARY KEY(principal, day));
              CREATE TABLE IF NOT EXISTS audit (
                id INTEGER PRIMARY KEY, created REAL NOT NULL, event TEXT NOT NULL);
            ''')
        resolver = StoredReferenceResolver(self.root / 'uploads', self.lookup, self.identity)
        self.intake = AssetIntake(authorize=self.authorize, open_reference=resolver,
            reserve=self.reserve, audit=self.audit, intake_root=self.root / 'intake')

    @staticmethod
    def _private(path):
        info = path.stat()
        if info.st_uid != os.geteuid() or info.st_mode & 0o077:
            raise PermissionError('runtime state must be owner-only')

    @contextmanager
    def db(self):
        db = sqlite3.connect(self.db_path, timeout=5)
        try:
            db.execute('PRAGMA synchronous=FULL')
            with db:
                yield db
        finally:
            db.close()

    def identity(self, context):
        identity = self.principal(context)
        if not isinstance(identity, str) or not identity:
            raise PermissionError('verified identity required')
        return identity

    def authorize(self, context, project, branch):
        identity = self.identity(context)
        matches = [p for p in self.policies if isinstance(p, Grant) and
                   (p.principal, p.project, p.branch) == (identity, project, branch)]
        if len(matches) != 1:
            raise PermissionError('project/branch not granted')
        grant = matches[0]
        checkout = Path(grant.checkout).resolve(strict=True)
        if checkout == self.root or checkout.is_relative_to(self.root) or self.root.is_relative_to(checkout):
            raise PermissionError('checkout and runtime state must be separate')
        return replace(grant, checkout=checkout)

    def lookup(self, reference):
        with self.db() as db:
            row = db.execute('SELECT path, principal, expires FROM files WHERE reference=?', (reference,)).fetchone()
        return StoredFile(row[0], frozenset({row[1]}), row[2]) if row else None

    def charge(self, identity, amount):
        # Conservative quota: failed attempts and imports count their reservation.
        day = time.strftime('%Y-%m-%d', time.gmtime())
        with self.db() as db:
            db.execute('BEGIN IMMEDIATE')
            row = db.execute('SELECT bytes FROM usage WHERE principal=? AND day=?', (identity, day)).fetchone()
            used = row[0] if row else 0
            if used + amount > self.daily_bytes:
                raise PermissionError('daily quota exceeded')
            db.execute('INSERT INTO usage VALUES (?,?,?) ON CONFLICT(principal,day) DO UPDATE SET bytes=excluded.bytes',
                       (identity, day, used + amount))

    @contextmanager
    def reserve(self, grant):
        key = hashlib.sha256(str(Path(grant.checkout).resolve()).encode()).hexdigest()
        fd = os.open(self.root / 'locks' / key, os.O_RDWR | os.O_CREAT | os.O_NOFOLLOW, 0o600)
        try:
            try:
                fcntl.flock(fd, fcntl.LOCK_EX | fcntl.LOCK_NB)
            except BlockingIOError as exc:
                raise PermissionError('checkout busy') from exc
            self.charge(grant.principal, grant.max_bytes)
            yield
        finally:
            os.close(fd)

    def audit(self, event):
        # Fixed fields only; never persist a provider reference or raw bytes.
        allowed = {'principal', 'project', 'branch', 'path', 'sha256', 'status',
                   'base_commit', 'bytes', 'requires_git_review', 'requires_hml_validation'}
        if set(event) - allowed:
            raise ValueError('unexpected audit field')
        with self.db() as db:
            db.execute('INSERT INTO audit(created,event) VALUES (?,?)',
                       (time.time(), json.dumps(event, sort_keys=True)))

    def accept_upload(self, context, *, project, branch, stream, sha256, ttl=900):
        """Internal provider bridge, not an MCP tool. Stream must be original bytes.

        Called only by an existing authenticated upload/provider handler, after
        validating provider ownership. Never expose stream/base64/URL as tool args.
        """
        import re
        grant = self.authorize(context, project, branch)
        if not isinstance(sha256, str) or not re.fullmatch('[0-9a-fA-F]{64}', sha256):
            raise ValueError('invalid checksum')
        if not isinstance(ttl, int) or not 0 < ttl <= 3600 or not 0 < grant.max_bytes <= MAX_BYTES:
            raise ValueError('invalid bounds')
        self.charge(grant.principal, grant.max_bytes)
        reference = 'file_' + secrets.token_hex(24)
        path = self.root / 'uploads' / reference
        total = 0
        digest = hashlib.sha256()
        try:
            with path.open('xb') as out:
                os.chmod(path, 0o600)
                while chunk := stream.read(min(1024 * 1024, grant.max_bytes - total + 1)):
                    if not isinstance(chunk, bytes):
                        raise ValueError('binary stream required')
                    total += len(chunk)
                    if total > grant.max_bytes:
                        raise ValueError('upload exceeds quota')
                    out.write(chunk)
                    digest.update(chunk)
                out.flush()
                os.fsync(out.fileno())
            if digest.hexdigest() != sha256.lower():
                raise ValueError('upload checksum mismatch')
            with self.db() as db:
                db.execute('INSERT INTO files VALUES (?,?,?,?,?,?)',
                    (reference, reference, grant.principal, time.time() + ttl, digest.hexdigest(), total))
            return {'file_reference': reference, 'sha256': digest.hexdigest(), 'bytes': total}
        except BaseException:
            path.unlink(missing_ok=True)
            raise

    def register(self, server, get_context):
        return register_mcp(server, self.intake, get_context)
