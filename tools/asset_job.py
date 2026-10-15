#!/usr/bin/env python3
"""Trusted Factory adapter entrypoint: process a materialized asset job.

Only run behind an authenticated connector. The job contains a local source
path already materialized by the connector; this is NOT a public upload API.
No git commit, push, merge, deploy, or network call is performed.
"""
import argparse
import json
from pathlib import Path
from asset_ingest import ingest

REQUIRED = {"source", "checkout", "destination", "sha256"}

def run(job_path):
    job_file = Path(job_path)
    if job_file.is_symlink() or not job_file.is_file():
        raise ValueError("job must be a regular file")
    job = json.loads(job_file.read_text(encoding="utf-8"))
    if not isinstance(job, dict) or set(job) != REQUIRED:
        raise ValueError("invalid job schema")
    if not all(isinstance(job[key], str) and job[key] for key in REQUIRED):
        raise ValueError("invalid job field")
    result = ingest(job["source"], job["checkout"], job["destination"], job["sha256"])
    return {"status": "staged", "asset": result, "requires_git_review": True, "requires_hml_validation": True}

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--job", required=True)
    args = parser.parse_args()
    print(json.dumps(run(args.job), ensure_ascii=False))
