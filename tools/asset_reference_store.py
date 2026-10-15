"""Concrete binary resolver for server-owned, authenticated upload catalogs.

Catalog entries are created by the upload service, never by MCP tool arguments.
No URLs, tokens or base64 transfer; files remain outside the project checkout.
"""
from contextlib import contextmanager
from dataclasses import dataclass
import os
from pathlib import Path
import stat
import time
from asset_ingest import safe_parts

@dataclass(frozen=True)
class StoredFile:
    path: str
    principals: frozenset[str]
    expires_at: float

class StoredReferenceResolver:
    def __init__(self, root, lookup, principal):
        self.root = Path(root).resolve(strict=True)
        info = self.root.stat()
        if info.st_uid != os.geteuid() or info.st_mode & 0o077:
            raise PermissionError('upload store must be owner-only')
        self.lookup = lookup
        self.principal = principal

    @contextmanager
    def __call__(self, context, reference):
        # principal() must extract identity from verified host context.
        identity = self.principal(context)
        if not identity:
            raise PermissionError('authentication required')
        record = self.lookup(reference)
        if not isinstance(record, StoredFile) or identity not in record.principals or record.expires_at <= time.time():
            raise PermissionError('file unavailable')
        parts = safe_parts(record.path)
        directory = os.open(self.root, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW)
        fd = None
        try:
            for part in parts[:-1]:
                child = os.open(part, os.O_RDONLY | os.O_DIRECTORY | os.O_NOFOLLOW, dir_fd=directory)
                os.close(directory)
                directory = child
            fd = os.open(parts[-1], os.O_RDONLY | os.O_NOFOLLOW | os.O_NONBLOCK, dir_fd=directory)
            if not stat.S_ISREG(os.fstat(fd).st_mode):
                raise ValueError('original must be a regular file')
            with os.fdopen(fd, 'rb') as stream:
                fd = None
                yield stream
        finally:
            if fd is not None:
                os.close(fd)
            os.close(directory)
