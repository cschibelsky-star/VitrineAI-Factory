#!/usr/bin/env python3
"""Bounded, no-clobber binary intake. Requires exclusive checkout ownership."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import re
import stat
import subprocess
import tempfile

MAX_BYTES = 25 * 1024 * 1024
SIGNATURES = {'.png': (bytes.fromhex('89504e470d0a1a0a'),),
              '.webp': (b'RIFF',), '.jpg': (bytes.fromhex('ffd8ff'),),
              '.jpeg': (bytes.fromhex('ffd8ff'),), '.pdf': (b'%PDF-',)}


def git(root, *args):
    # Do not inherit caller-supplied repository/index overrides.
    env = {k: v for k, v in os.environ.items() if not k.startswith('GIT_')}
    env['GIT_OPTIONAL_LOCKS'] = '0'
    try:
        return subprocess.check_output(['git', '-C', str(root), *args], env=env,
                                       stderr=subprocess.DEVNULL).decode().strip()
    except subprocess.CalledProcessError as exc:
        raise ValueError('invalid git checkout or branch') from exc


def clean_checkout(checkout, branch=None):
    root = Path(checkout).resolve(strict=True)
    if Path(git(root, 'rev-parse', '--show-toplevel')).resolve() != root:
        raise ValueError('checkout must be a git working tree root')
    if git(root, 'status', '--porcelain=v1', '--untracked-files=all', '--ignored'):
        raise ValueError('checkout must be clean (including ignored files)')
    actual = git(root, 'symbolic-ref', '--short', 'HEAD')
    if branch is not None and actual != branch:
        raise PermissionError('branch outside grant')
    return root


def safe_parts(destination):
    if not isinstance(destination, str) or not destination or '\\' in destination:
        raise ValueError('invalid destination')
    parts = destination.split('/')
    if any(p in ('', '.', '..') or p.lower() == '.git' or '\x00' in p for p in parts):
        raise ValueError('invalid destination')
    return parts


def ingest(source, checkout, destination, expected_sha256, *, allow_overwrite=False):
    if allow_overwrite:
        raise ValueError('overwrites require a separate reviewed replacement operation')
    if not isinstance(expected_sha256, str) or not re.fullmatch('[0-9a-fA-F]{64}', expected_sha256):
        raise ValueError('invalid SHA-256')
    root = clean_checkout(checkout)
    parts = safe_parts(destination)
    suffix = Path(parts[-1]).suffix.lower()
    if suffix not in SIGNATURES:
        raise ValueError('unsupported extension')
    srcfd = os.open(source, os.O_RDONLY | os.O_NOFOLLOW | os.O_NONBLOCK)
    staged = None
    parentfd = None
    try:
        info = os.fstat(srcfd)
        if not stat.S_ISREG(info.st_mode) or info.st_size > MAX_BYTES:
            raise ValueError('source must be a bounded regular file')
        # Stage outside the checkout; hash the exact bytes later linked into it.
        with tempfile.NamedTemporaryFile() as staged:
            digest = hashlib.sha256()
            total = 0
            head = b''
            with os.fdopen(srcfd, 'rb') as inp:
                srcfd = None
                while chunk := inp.read(1024 * 1024):
                    total += len(chunk)
                    if total > MAX_BYTES:
                        raise ValueError('file too large')
                    head = (head + chunk)[:16]
                    digest.update(chunk)
                    staged.write(chunk)
            valid = (head.startswith(b'RIFF') and head[8:12] == b'WEBP') if suffix == '.webp' else any(head.startswith(s) for s in SIGNATURES[suffix])
            if not valid or digest.hexdigest() != expected_sha256.lower():
                raise ValueError('signature or SHA-256 mismatch')
            staged.flush()
            clean_checkout(root)
            # Walk using directory descriptors, refusing symlinks at every level.
            parentfd = os.open(root, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
            for part in parts[:-1]:
                try:
                    os.mkdir(part, 0o755, dir_fd=parentfd)
                except FileExistsError:
                    pass
                child = os.open(part, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW, dir_fd=parentfd)
                os.close(parentfd)
                parentfd = child
            # Use an exclusive destination: never replace a concurrent file.
            # Keep the incomplete name private; link atomically in same directory.
            import secrets
            tempname = '.asset-' + secrets.token_hex(16)
            outfd = os.open(tempname, os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_NOFOLLOW, 0o600, dir_fd=parentfd)
            try:
                staged.seek(0)
                with os.fdopen(outfd, 'wb') as out:
                    while chunk := staged.read(1024 * 1024):
                        out.write(chunk)
                    out.flush()
                    os.fsync(out.fileno())
                    os.fchmod(out.fileno(), 0o644)
                os.link(tempname, parts[-1], src_dir_fd=parentfd, dst_dir_fd=parentfd, follow_symlinks=False)
                os.fsync(parentfd)
            finally:
                os.unlink(tempname, dir_fd=parentfd)
        return {'path': destination, 'sha256': digest.hexdigest(), 'bytes': total}
    finally:
        if srcfd is not None:
            os.close(srcfd)
        if parentfd is not None:
            os.close(parentfd)


if __name__ == '__main__':
    ap = argparse.ArgumentParser()
    ap.add_argument('--source', required=True)
    ap.add_argument('--checkout', required=True)
    ap.add_argument('--destination', required=True)
    ap.add_argument('--sha256', required=True)
    args = ap.parse_args()
    print(json.dumps(ingest(args.source, args.checkout, args.destination, args.sha256)))
