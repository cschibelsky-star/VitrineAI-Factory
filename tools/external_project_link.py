"""Read-only discovery and safe registration of existing external HML projects.

No clone, DNS, deployment, git checkout, secret access or host mutation.
A verified operator supplies existing project facts; registration is an atomic
metadata operation in a Factory-owned directory, never in the source project.
"""
import json
import os
from pathlib import Path
import re
import tempfile
from urllib.parse import urlsplit

PROJECT_ID = re.compile(r"^[a-z0-9][a-z0-9_-]{1,79}$")
BRANCH = re.compile(r"^[a-zA-Z0-9][a-zA-Z0-9._/-]{0,150}$")
REPO = re.compile(r"^(?:https://github\\.com/|git@github\\.com:)[a-zA-Z0-9_.-]+/[a-zA-Z0-9_.-]+(?:\\.git)?$")
ALLOWED_KEYS = frozenset({"id", "repository", "branch", "hml_url", "operational_project_id", "workspace_root", "mode"})

def validate(data):
    if not isinstance(data, dict) or set(data) != ALLOWED_KEYS:
        raise ValueError("invalid project manifest")
    if not PROJECT_ID.fullmatch(data["id"]) or not PROJECT_ID.fullmatch(data["operational_project_id"]):
        raise ValueError("invalid project identifier")
    if not REPO.fullmatch(data["repository"]):
        raise ValueError("unsupported repository")
    if not BRANCH.fullmatch(data["branch"]) or data["branch"] in {"main", "master", "production"}:
        raise ValueError("review branch required")
    parsed = urlsplit(data["hml_url"])
    if parsed.scheme != "https" or not parsed.hostname or parsed.port or parsed.username or parsed.password or parsed.path not in ("", "/") or parsed.query or parsed.fragment:
        raise ValueError("HML must be an existing HTTPS origin")
    if not parsed.hostname.endswith(".hml.vitrineiapro.com.br"):
        raise ValueError("HML domain outside owned zone")
    root = Path(data["workspace_root"])
    if not root.is_absolute() or ".." in root.parts or not str(root).startswith("/srv/projects/"):
        raise ValueError("invalid workspace root")
    if data["mode"] != "linked_existing_read_only":
        raise ValueError("external project must start in read-only mode")
    return dict(data)

def link_existing(data, *, registry_dir, inspect_project, probe_hml):
    data = validate(data)
    # Trusted host adapters: no URLs or arbitrary commands from the caller.
    actual = inspect_project(data["operational_project_id"])
    if actual.get("workspace_root") != data["workspace_root"]:
        raise ValueError("workspace differs from operational registry")
    if actual.get("repository") != data["repository"]:
        raise ValueError("repository differs from operational registry")
    if actual.get("head_branch") != data["branch"]:
        raise ValueError("branch differs from active HML checkout")
    if not probe_hml(data["hml_url"]):
        raise ValueError("existing HML is not reachable")
    folder = Path(registry_dir).resolve(strict=True)
    if folder.stat().st_mode & 0o022:
        raise PermissionError("unsafe registry permissions")
    target = folder / (data["id"] + ".json")
    if target.exists() or target.is_symlink():
        raise FileExistsError("project already registered; explicit reviewed update required")
    payload = (json.dumps(data, indent=2, sort_keys=True, ensure_ascii=False) + "\n").encode()
    temp = None
    try:
        with tempfile.NamedTemporaryFile(mode="wb", dir=folder, prefix=".linked-", delete=False) as f:
            temp = Path(f.name)
            os.fchmod(f.fileno(), 0o600)
            f.write(payload)
            f.flush()
            os.fsync(f.fileno())
        # link is exclusive: cannot overwrite concurrent existing manifest
        os.link(temp, target)
        fd = os.open(folder, os.O_RDONLY | os.O_DIRECTORY)
        try:
            os.fsync(fd)
        finally:
            os.close(fd)
    finally:
        if temp is not None:
            temp.unlink(missing_ok=True)
    return {"status": "linked_read_only", "id": data["id"], "manifest": str(target), "hml_url": data["hml_url"]}
