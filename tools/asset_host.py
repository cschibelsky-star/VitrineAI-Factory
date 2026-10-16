"""Isolated authenticated Asset Intake ASGI host.

Deployment is intentionally fail-closed: public key, issuer, audience and
project grants must be provisioned out-of-band by the approved platform.
This host never creates bearer tokens and never reads a signing private key.
"""
import json
import os
from pathlib import Path

from fastmcp import FastMCP
from fastmcp.server.auth.providers.bearer import BearerAuthProvider

from asset_fastmcp import bind_existing_host
from asset_intake import Grant


def required_env(name: str) -> str:
    value = os.environ.get(name, "").strip()
    if not value:
        raise RuntimeError("missing configuration: " + name)
    return value


def build_host():
    public_key_path = Path(required_env("ASSET_AUTH_PUBLIC_KEY_FILE"))
    config_path = Path(required_env("ASSET_GRANTS_FILE"))
    state_root = Path(required_env("ASSET_STATE_DIR"))
    if not public_key_path.is_file() or not config_path.is_file():
        raise RuntimeError("auth or grants file missing")
    auth = BearerAuthProvider(
        public_key=public_key_path.read_text(encoding="utf-8"),
        issuer=required_env("ASSET_AUTH_ISSUER"),
        audience=required_env("ASSET_AUTH_AUDIENCE"),
    )
    config = json.loads(config_path.read_text(encoding="utf-8"))
    if not isinstance(config, list) or not config:
        raise RuntimeError("at least one explicit project grant required")
    grants = []
    for entry in config:
        if set(entry) != {"client_id", "project", "branch", "checkout", "destinations", "max_bytes"}:
            raise RuntimeError("malformed grant")
        checkout = Path(entry["checkout"])
        if not checkout.is_absolute() or ".." in checkout.parts:
            raise RuntimeError("invalid checkout")
        if not isinstance(entry["destinations"], list) or not entry["destinations"]:
            raise RuntimeError("invalid destinations")
        grants.append(Grant(
            entry["client_id"], entry["project"], entry["branch"], checkout,
            tuple(entry["destinations"]), int(entry["max_bytes"]),
        ))
    server = FastMCP("Factory Asset Intake HML", auth=auth)
    bind_existing_host(server, state_root=state_root, policies=tuple(grants))
    return server


# ASGI factory so an operator can validate config before opening a listener.
def make_app():
    return build_host().http_app(path="/mcp", json_response=True, stateless_http=True)
