import importlib.util
from pathlib import Path
import sys
import tempfile
import types
import unittest
from unittest.mock import patch
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from asset_fastmcp import bind_existing_host, request_principal

class BindingTests(unittest.TestCase):
    def test_unconfigured_host_denied_without_state_creation(self):
        with tempfile.TemporaryDirectory() as tmp:
            state = Path(tmp) / 'state'
            with self.assertRaises(PermissionError):
                bind_existing_host(types.SimpleNamespace(auth=None), state_root=state, policies=())
            self.assertFalse(state.exists())
    def test_token_scope_identity_expiry(self):
        import time
        token = types.SimpleNamespace(client_id='owner', scopes=['factory:assets:write'], expires_at=time.time()+60)
        deps = types.ModuleType('fastmcp.server.dependencies')
        deps.get_access_token = lambda: token
        with patch.dict(sys.modules, {'fastmcp.server.dependencies': deps}):
            self.assertEqual(request_principal().client_id, 'owner')
            for bad in [None, types.SimpleNamespace(client_id='owner', scopes=[], expires_at=time.time()+60),
                        types.SimpleNamespace(client_id='owner', scopes=['factory:assets:write'], expires_at=0),
                        types.SimpleNamespace(client_id='owner', scopes=['factory:assets:write'], expires_at=None),
                        types.SimpleNamespace(client_id='unknown', scopes=['factory:assets:write'], expires_at=time.time()+60)]:
                token = bad
                with self.assertRaises(PermissionError): request_principal()
