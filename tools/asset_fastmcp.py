"""Fail-closed binding for the existing FastMCP 2.x operational host.

Does not create/replace an auth provider, listener, proxy route or credential.
"""
from dataclasses import dataclass
import time
from asset_runtime import PrivateRuntime

ASSET_SCOPE = 'factory:assets:write'

@dataclass(frozen=True)
class VerifiedPrincipal:
    client_id: str


def request_principal():
    # Use SDK's verified request-local token, NEVER Authorization/X-User headers.
    from fastmcp.server.dependencies import get_access_token
    token = get_access_token()
    if token is None:
        raise PermissionError('authenticated request required')
    if ASSET_SCOPE not in token.scopes:
        raise PermissionError('asset scope required')
    if token.expires_at is None or token.expires_at <= time.time():
        raise PermissionError('finite valid token lifetime required')
    if not isinstance(token.client_id, str) or token.client_id in ('', 'unknown'):
        raise PermissionError('verified client identity required')
    return VerifiedPrincipal(token.client_id)


def principal_identity(context):
    if not isinstance(context, VerifiedPrincipal):
        raise PermissionError('verified principal required')
    return context.client_id


def bind_existing_host(server, *, state_root, policies, daily_bytes=100 * 1024 * 1024):
    # Older V5 starts with FastMCP(name) and no auth provider. Refuse to enable
    # the binary tool there, even when an upstream proxy may authenticate traffic.
    if getattr(server, 'auth', None) is None:
        raise PermissionError('host must configure a validated FastMCP auth provider first')
    runtime = PrivateRuntime(state_root, policies, principal_identity, daily_bytes=daily_bytes)
    tool = runtime.register(server, request_principal)
    return runtime, tool
