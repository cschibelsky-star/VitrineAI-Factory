"""Runs only in the SDK CI job. Real ASGI auth middleware, no live service."""
import hashlib
import io
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from fastmcp import FastMCP
from fastmcp.server.auth.providers.bearer import BearerAuthProvider, RSAKeyPair
import httpx
from asset_fastmcp import bind_existing_host, VerifiedPrincipal
from asset_intake import Grant

class SDKHTTPTests(unittest.IsolatedAsyncioTestCase):
    async def test_authenticated_binary_tool_and_denials(self):
        key = RSAKeyPair.generate()  # Ephemeral test credentials; never persisted.
        issuer = 'https://issuer.example.invalid'
        audience = 'https://assets.example.invalid/mcp'
        auth = BearerAuthProvider(public_key=key.public_key, issuer=issuer, audience=audience)
        server = FastMCP('isolated-asset-test', auth=auth)
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            checkout = root / 'project'
            subprocess.run(['git', 'init', '-q', '-b', 'asset-review', str(checkout)], check=True)
            subprocess.run(['git', '-C', str(checkout), '-c', 'user.name=Test', '-c', 'user.email=test@example.invalid', 'commit', '-qm', 'baseline', '--allow-empty'], check=True)
            grant = Grant('owner', 'project-a', 'asset-review', checkout, ('public/assets',), 1024)
            runtime, _ = bind_existing_host(server, state_root=root/'state', policies=(grant,))
            data = bytes.fromhex('89504e470d0a1a0a') + b'fixture'
            sha = hashlib.sha256(data).hexdigest()
            args = dict(file_reference='not-yet', project='project-a', branch='asset-review', destination='public/assets/logo.png', sha256=sha)
            app = server.http_app(path='/mcp', json_response=True, stateless_http=True)
            async with app.router.lifespan_context(app):
                async with httpx.AsyncClient(transport=httpx.ASGITransport(app=app), base_url='http://test', follow_redirects=True) as client:
                    async def call(token):
                        headers = {'Accept':'application/json, text/event-stream'}
                        if token: headers['Authorization'] = 'Bearer ' + token
                        return await client.post('/mcp', headers=headers, json={'jsonrpc':'2.0','id':1,'method':'tools/call','params':{'name':'factory_asset_receive','arguments':args}})
                    def token(subject='owner', scopes=None, **overrides):
                        return key.create_token(subject=subject, issuer=issuer, audience=audience,
                            scopes=scopes if scopes is not None else ['factory:assets:write'], **overrides)
                    async def upload(auth_token, *, body=data, checksum=sha, project='project-a'):
                        headers = {'Content-Type':'application/octet-stream', 'X-Asset-SHA256':checksum}
                        if auth_token: headers['Authorization'] = 'Bearer ' + auth_token
                        return await client.post('/factory/assets/upload', params={'project':project,'branch':'asset-review'}, headers=headers, content=body)
                    for invalid in [None, 'invalid', token(expires_in_seconds=-60)]:
                        self.assertEqual((await upload(invalid)).status_code, 401)
                    for denied in [token(subject='other'), token(scopes=['read']), token(scopes=[])]:
                        self.assertEqual((await upload(denied)).status_code, 403)
                    self.assertEqual((await upload(token(), project='other')).status_code, 403)
                    self.assertEqual((await upload(token(), checksum='0'*64)).status_code, 400)
                    self.assertEqual((await upload(token(), body=b'x'*1025)).status_code, 413)
                    async def chunks():
                        yield b'x'*600
                        yield b'x'*600
                    self.assertEqual((await upload(token(), body=chunks())).status_code, 413)
                    self.assertEqual(list((root/'state/intake').iterdir()), [])
                    uploaded = await upload(token())
                    self.assertEqual(uploaded.status_code, 201)
                    args['file_reference'] = uploaded.json()['file_reference']
                    self.assertEqual(uploaded.json()['sha256'], sha)
                    self.assertEqual(uploaded.headers['cache-control'], 'no-store')
                    for invalid in [None, 'invalid', token(expires_in_seconds=-60)]:
                        response = await call(invalid)
                        self.assertIn(response.status_code, (401,403))
                    for restricted in [token(scopes=['read']), token(subject='other')]:
                        response = await call(restricted)
                        self.assertEqual(response.status_code, 200)
                        self.assertTrue(response.json()['result']['isError'])
                    response = await call(token())
                    self.assertEqual(response.status_code, 200)
                    self.assertFalse(response.json()['result'].get('isError',False))
                    self.assertEqual((checkout/'public/assets/logo.png').read_bytes(), data)

if __name__ == '__main__': unittest.main()
