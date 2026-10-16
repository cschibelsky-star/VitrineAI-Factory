from contextlib import contextmanager
from pathlib import Path
import sys
import tempfile
import time
import unittest
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from asset_reference_store import StoredFile, StoredReferenceResolver

class ResolverTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.addCleanup(self.tmp.cleanup)
        self.root = Path(self.tmp.name)
        (self.root / 'original').write_bytes(b'original-binary\x00\xff')
        self.record = StoredFile('original', frozenset({'owner'}), time.time() + 100)
        self.resolver = StoredReferenceResolver(self.root, lambda ref: self.record if ref == 'file_owned' else None, lambda ctx: ctx)
    def test_original_bytes(self):
        with self.resolver('owner', 'file_owned') as stream:
            self.assertEqual(stream.read(), b'original-binary\x00\xff')
    def test_acl_missing_expired(self):
        for ctx, ref in [(None, 'file_owned'), ('other', 'file_owned'), ('owner', 'missing')]:
            with self.assertRaises(PermissionError), self.resolver(ctx, ref): pass
        self.record = StoredFile('original', frozenset({'owner'}), time.time() - 1)
        with self.assertRaises(PermissionError), self.resolver('owner', 'file_owned'): pass
    def test_symlink_and_traversal(self):
        (self.root / 'link').symlink_to(self.root / 'original')
        for path in ['link', '../original', '/tmp/original']:
            self.record = StoredFile(path, frozenset({'owner'}), time.time() + 100)
            with self.assertRaises((OSError, ValueError)), self.resolver('owner', 'file_owned'): pass
