#!/usr/bin/env python3
"""Factory asset ingestion: safe local binary intake for an authorized checkout."""
import argparse
import hashlib
import json
import os
from pathlib import Path
import shutil
import tempfile

MAX_BYTES = 25 * 1024 * 1024
SIGNATURES = {".png": (bytes.fromhex("89504e470d0a1a0a"),),
              ".webp": (b"RIFF",),
              ".jpg": (bytes.fromhex("ffd8ff"),),
              ".jpeg": (bytes.fromhex("ffd8ff"),),
              ".pdf": (b"%PDF-",)}

def ingest(source, checkout, destination, expected_sha256, *, allow_overwrite=False):
    root = Path(checkout).resolve(strict=True)
    if not (root / ".git").exists():
        raise ValueError("checkout must be a git working tree root")
    rel = Path(destination)
    if rel.is_absolute() or ".." in rel.parts or not rel.parts or any(p in ("", ".") for p in rel.parts):
        raise ValueError("invalid relative destination")
    target = root / rel
    if target.suffix.lower() not in SIGNATURES:
        raise ValueError("unsupported extension")
    parent = target.parent
    if any((root.joinpath(*rel.parts[:i])).is_symlink() for i in range(1, len(rel.parts) + 1)):
        raise ValueError("symlink destination")
    if target.exists() and not allow_overwrite:
        raise FileExistsError("destination exists")
    if not str(parent.resolve()).startswith(str(root) + os.sep):
        raise ValueError("destination escapes checkout")
    src = Path(source)
    if not src.is_file() or src.is_symlink():
        raise ValueError("source must be a regular file")
    if src.stat().st_size > MAX_BYTES:
        raise ValueError("file too large")
    digest = hashlib.sha256()
    with src.open("rb") as fh:
        head = fh.read(16)
        if target.suffix.lower() == ".webp":
            valid = head.startswith(b"RIFF") and head[8:12] == b"WEBP"
        else:
            valid = any(head.startswith(sig) for sig in SIGNATURES[target.suffix.lower()])
        if not valid:
            raise ValueError("file signature does not match extension")
        fh.seek(0)
        while chunk := fh.read(1024 * 1024):
            digest.update(chunk)
    if digest.hexdigest() != expected_sha256.lower():
        raise ValueError("SHA-256 mismatch")
    parent.mkdir(parents=True, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix=".asset-", dir=parent)
    try:
        with os.fdopen(fd, "wb") as out, src.open("rb") as inp:
            shutil.copyfileobj(inp, out)
        if hashlib.sha256(Path(tmp).read_bytes()).hexdigest() != expected_sha256.lower():
            raise ValueError("staged checksum mismatch")
        os.replace(tmp, target)
    finally:
        if os.path.exists(tmp):
            os.unlink(tmp)
    return {"path": str(rel), "sha256": digest.hexdigest(), "bytes": src.stat().st_size}

if __name__ == "__main__":
    ap = argparse.ArgumentParser()
    ap.add_argument("--source", required=True)
    ap.add_argument("--checkout", required=True)
    ap.add_argument("--destination", required=True)
    ap.add_argument("--sha256", required=True)
    ap.add_argument("--allow-overwrite", action="store_true")
    args = ap.parse_args()
    print(json.dumps(ingest(args.source, args.checkout, args.destination, args.sha256, allow_overwrite=args.allow_overwrite)))
