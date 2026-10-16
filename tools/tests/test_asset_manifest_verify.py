import hashlib
import importlib.util
import json
import tempfile
import unittest
from pathlib import Path

MODULE = Path(__file__).resolve().parents[1] / "asset_manifest_verify.py"
spec = importlib.util.spec_from_file_location("asset_manifest_verify", MODULE)
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)

class ManifestTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name)
        (self.root / "assets").mkdir()
        self.asset = self.root / "assets" / "sample.png"
        self.asset.write_bytes(b"test")
        self.manifest = self.root / "manifest.json"
        self.manifest.write_text(json.dumps([{"path": "assets/sample.png", "sha256": hashlib.sha256(b"test").hexdigest()}]))

    def test_valid(self):
        self.assertTrue(mod.verify(self.root, self.manifest)[0]["ok"])

    def test_modified_asset(self):
        self.asset.write_bytes(b"tampered")
        with self.assertRaises(ValueError):
            mod.verify(self.root, self.manifest)

    def test_traversal(self):
        self.manifest.write_text(json.dumps([{"path": "../outside.png", "sha256": "0"*64}]))
        with self.assertRaises(ValueError):
            mod.verify(self.root, self.manifest)

    def test_empty_manifest(self):
        self.manifest.write_text("[]")
        with self.assertRaises(ValueError):
            mod.verify(self.root, self.manifest)

if __name__ == "__main__":
    unittest.main()
