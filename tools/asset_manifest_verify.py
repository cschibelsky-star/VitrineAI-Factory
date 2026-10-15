#!/usr/bin/env python3
"""Verify a committed asset manifest against immutable project files.

Use in project CI after assets have been imported and committed.
No network access, no secrets and no production mutation.
"""
import argparse
import hashlib
import json
from pathlib import Path

def verify(root, manifest):
    root = Path(root).resolve(strict=True)
    entries = json.loads(Path(manifest).read_text(encoding="utf-8"))
    if not isinstance(entries, list) or not entries:
        raise ValueError("manifest must contain at least one asset")
    results = []
    for item in entries:
        rel = Path(item["path"])
        if rel.is_absolute() or ".." in rel.parts or not rel.parts:
            raise ValueError("invalid asset path")
        target = root / rel
        if target.is_symlink() or not target.is_file() or not target.resolve().is_relative_to(root):
            raise ValueError("asset missing or unsafe")
        digest = hashlib.sha256()
        with target.open("rb") as stream:
            for chunk in iter(lambda: stream.read(1024 * 1024), b""):
                digest.update(chunk)
        if digest.hexdigest() != item["sha256"].lower():
            raise ValueError("checksum mismatch: " + str(rel))
        results.append({"path": str(rel), "sha256": digest.hexdigest(), "ok": True})
    return results

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", required=True)
    parser.add_argument("--manifest", required=True)
    args = parser.parse_args()
    print(json.dumps(verify(args.root, args.manifest), indent=2))
