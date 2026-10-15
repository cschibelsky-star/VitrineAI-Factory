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
    return verified_token_principal(get_access_token())


def verified_token_principal(token):
    if token is None:
        raise PermissionError('authenticated request required')
    scopes = getattr(token, 'scopes', None)
    if not isinstance(scopes, (list, tuple, set, frozenset)) or ASSET_SCOPE not in scopes:
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
    register_binary_upload(server, runtime)
    return runtime, tool


def register_binary_upload(server, runtime):
    """Raw binary input on the same authenticated host, no separate listener.

    Custom FastMCP routes are not assumed to inherit MCP auth middleware.
    Verify the bearer token explicitly with the host's existing provider.
    """
    import re
    import asyncio
    import tempfile
    from pathlib import Path
    from asset_ingest import MAX_BYTES
    from starlette.responses import JSONResponse

    @server.custom_route('/factory/assets/upload', methods=['POST'], include_in_schema=False)
    async def factory_binary_upload(request):
        def response(payload, status):
            return JSONResponse(payload, status_code=status, headers={'Cache-Control':'no-store'})
        authorization = request.headers.get('authorization', '')
        scheme, separator, bearer = authorization.partition(' ')
        if not separator or scheme.lower() != 'bearer' or not bearer or len(bearer) > 16384:
            return response({'error':'authentication_required'}, 401)
        try:
            token = await server.auth.verify_token(bearer)
        except Exception:
            return response({'error':'authentication_failed'}, 401)
        if token is None:
            return response({'error':'authentication_failed'}, 401)
        try:
            context = verified_token_principal(token)
            if set(request.query_params.keys()) != {'project', 'branch'}:
                return response({'error':'invalid_request'}, 400)
            project, branch = request.query_params['project'], request.query_params['branch']
            if len(request.query_params.getlist('project')) != 1 or len(request.query_params.getlist('branch')) != 1:
                return response({'error':'invalid_request'}, 400)
            grant = runtime.authorize(context, project, branch)
            if not 0 < grant.max_bytes <= MAX_BYTES:
                raise PermissionError("invalid upload bounds")
            checksum = request.headers.get('x-asset-sha256', '')
            if not re.fullmatch('[0-9a-fA-F]{64}', checksum):
                return response({'error':'invalid_checksum'}, 400)
            if request.headers.get('content-type', '').split(';')[0].strip().lower() != 'application/octet-stream':
                return response({'error':'binary_content_type_required'}, 415)
            length = request.headers.get('content-length')
            if length is not None and (not length.isdecimal() or int(length) > grant.max_bytes):
                return response({'error':'size_limit'}, 413)
            # No await while holding a database transaction. A nonblocking file
            # lock caps concurrent transfers to one per authorized checkout.
            with runtime.lock_checkout(grant):
                with tempfile.TemporaryDirectory(prefix='upload-', dir=runtime.root/'intake') as directory:
                    path = Path(directory) / 'incoming'
                    total = 0
                    with path.open('xb') as out:
                        import os
                        os.chmod(path, 0o600)
                        async with asyncio.timeout(30):
                            async for chunk in request.stream():
                                total += len(chunk)
                                if total > grant.max_bytes:
                                    return response({'error':'size_limit'}, 413)
                                out.write(chunk)
                    context = verified_token_principal(token)
                    with path.open('rb') as stream:
                        receipt = runtime.accept_upload(context, project=project, branch=branch,
                                                        stream=stream, sha256=checksum)
                return response(receipt, 201)
        except TimeoutError:
            return response({'error':'transfer_timeout'}, 408)
        except PermissionError:
            return response({'error':'access_or_quota_denied'}, 403)
        except ValueError:
            return response({'error':'invalid_binary_or_checksum'}, 400)
        except Exception:
            # Never return internal paths, identity, bearer values or raw errors.
            return response({'error':'upload_failed'}, 500)
    return factory_binary_upload
