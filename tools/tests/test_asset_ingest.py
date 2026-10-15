import hashlib
import subprocess
import importlib.util
import tempfile
import unittest
from pathlib import Path

MODULE = Path(__file__).resolve().parents[1] / "asset_ingest.py"
spec = importlib.util.spec_from_file_location("asset_ingest", MODULE)
module = importlib.util.module_from_spec(spec)
spec.loader.exec_module(module)

class AssetIngestTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.addCleanup(self.tmp.cleanup)
        self.root = Path(self.tmp.name) / "checkout"
        self.root.mkdir(parents=True)
        subprocess.run(["git", "init", "-q", "-b", "intake-test", str(self.root)], check=True)
        self.source = Path(self.tmp.name) / "image.png"
        self.data = bytes.fromhex("89504e470d0a1a0a") + b"test-payload"
        self.source.write_bytes(self.data)
        self.sha = hashlib.sha256(self.data).hexdigest()

    def test_import(self):
        result = module.ingest(self.source, self.root, "assets/img/image.png", self.sha)
        self.assertEqual(result["sha256"], self.sha)
        self.assertEqual((self.root / "assets/img/image.png").read_bytes(), self.data)

    def test_wrong_checksum(self):
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "assets/img/image.png", "0" * 64)

    def test_path_traversal(self):
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "../escape.png", self.sha)

    def test_absolute_path(self):
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "/tmp/escape.png", self.sha)

    def test_invalid_magic(self):
        self.source.write_bytes(b"not a png")
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "assets/image.png", hashlib.sha256(b"not a png").hexdigest())

    def test_overwrite_denied(self):
        module.ingest(self.source, self.root, "assets/image.png", self.sha)
        with self.assertRaises((FileExistsError, ValueError)):
            module.ingest(self.source, self.root, "assets/image.png", self.sha)

    def test_symlink_denied(self):
        (self.root / "assets").symlink_to(Path(self.tmp.name), target_is_directory=True)
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "assets/image.png", self.sha)

    def test_symlink_target_with_overwrite_denied(self):
        outside = Path(self.tmp.name) / "outside.png"
        outside.write_bytes(b"keep")
        (self.root / "assets").mkdir()
        (self.root / "assets" / "image.png").symlink_to(outside)
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, self.root, "assets/image.png", self.sha, allow_overwrite=True)
        self.assertEqual(outside.read_bytes(), b"keep")

    def test_missing_git_root(self):
        with self.assertRaises((ValueError, OSError)):
            module.ingest(self.source, Path(self.tmp.name), "image.png", self.sha)

if __name__ == "__main__":
    unittest.main()

