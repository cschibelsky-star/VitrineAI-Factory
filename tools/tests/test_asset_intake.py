import hashlib
import io
import os
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
from contextlib import contextmanager
from dataclasses import replace
from unittest.mock import patch
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from asset_intake import AssetIntake, Grant, register_mcp
from asset_ingest import ingest

class IntakeTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.base = Path(self.temp.name)
        self.root = self.base / 'project'
        self.root.mkdir()
        self.git('init', '-q', '-b', 'asset-review')
        self.commit('baseline', empty=True)
        self.intake_root = self.base / 'intake'
        self.intake_root.mkdir(mode=0o700)
        import base64
        self.data = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a5XkAAAAASUVORK5CYII=')
        self.sha = hashlib.sha256(self.data).hexdigest()
        self.grant = Grant('owner', 'project-a', 'asset-review', self.root, ('public/assets',))
        self.events = []
        self.opens = 0
        self.adapter = AssetIntake(authorize=self.authorize, open_reference=self.open_ref,
            reserve=self.reserve, audit=self.events.append, intake_root=self.intake_root)

    def git(self, *args):
        return subprocess.check_output(['git', '-C', str(self.root), *args], stderr=subprocess.DEVNULL).decode().strip()
    def commit(self, message, empty=False):
        self.git('-c', 'user.name=Test', '-c', 'user.email=test@example.invalid',
                 'commit', '-qm', message, *(['--allow-empty'] if empty else []))
    def authorize(self, context, project, branch):
        if context != 'verified-owner':
            raise PermissionError('authentication required')
        return self.grant
    @contextmanager
    def open_ref(self, context, ref):
        self.opens += 1
        if ref != 'file_owned':
            raise PermissionError('file not accessible')
        yield io.BytesIO(self.data)
    @contextmanager
    def reserve(self, grant):
        yield
    def receive(self, context='verified-owner', **overrides):
        request = dict(file_reference='file_owned', project='project-a', branch='asset-review',
                       destination='public/assets/logo.png', sha256=self.sha)
        request.update(overrides)
        return self.adapter.receive(context, **request)

    def test_success_original_bytes_receipt_cleanup_git_preserved(self):
        head = self.git('rev-parse', 'HEAD')
        result = self.receive()
        self.assertEqual((self.root / 'public/assets/logo.png').read_bytes(), self.data)
        self.assertEqual(result['receipt']['sha256'], self.sha)
        self.assertEqual(self.git('rev-parse', 'HEAD'), head)
        self.assertEqual(self.git('diff', '--cached'), '')
        self.assertEqual(list(self.intake_root.iterdir()), [])
        self.assertEqual([e['status'] for e in self.events], ['authorized', 'staged'])
    def test_unauthenticated_denied_before_resolver(self):
        with self.assertRaises(PermissionError): self.receive(context=None)
        self.assertEqual(self.opens, 0)
    def test_foreign_file_denied(self):
        with self.assertRaises(PermissionError): self.receive(file_reference='file_foreign')
        self.assertFalse((self.root / 'public').exists())
    def test_scope_project_branch_destination(self):
        for args in [dict(project='other'), dict(branch='other'), dict(destination='public/assets-evil/logo.png'),
                     dict(destination='public/assets'), dict(destination='../logo.png'), dict(destination='.git/logo.png')]:
            with self.subTest(args=args), self.assertRaises((PermissionError, ValueError)): self.receive(**args)
        self.assertEqual(self.opens, 0)
    def test_url_path_base64_not_reference(self):
        for ref in ['https://example.invalid/a', '/tmp/a', '../file', 'data:image/png;base64,xxx']:
            with self.subTest(ref=ref), self.assertRaises(ValueError): self.receive(file_reference=ref)
        self.assertEqual(self.opens, 0)
    def test_checksum_signature_failure_without_changes(self):
        with self.assertRaises(ValueError): self.receive(sha256='0' * 64)
        self.data = b'not an image'
        self.sha = hashlib.sha256(self.data).hexdigest()
        with self.assertRaises(ValueError): self.receive()
        self.assertEqual(self.git('status', '--porcelain'), '')
        self.assertEqual(list(self.intake_root.iterdir()), [])
    def test_quota_enforced_on_stream(self):
        self.grant = replace(self.grant, max_bytes=8)
        with self.assertRaises(ValueError): self.receive()
        self.assertEqual(self.git('status', '--porcelain'), '')
        self.assertEqual(list(self.intake_root.iterdir()), [])
    def test_dirty_ignored_tree_preserved(self):
        for name in ['work.txt', 'ignored.txt']:
            if name == 'ignored.txt': (self.root / '.git/info/exclude').write_text('ignored.txt\n')
            dirty = self.root / name
            dirty.write_bytes(b'preserve')
            with self.assertRaises(ValueError): self.receive()
            self.assertEqual(dirty.read_bytes(), b'preserve')
            dirty.unlink()
        self.assertEqual(self.opens, 0)
    def test_committed_destination_cannot_be_overwritten(self):
        self.receive()
        self.git('add', 'public')
        self.commit('asset')
        with self.assertRaises(FileExistsError): self.receive()
        self.assertEqual((self.root / 'public/assets/logo.png').read_bytes(), self.data)
        self.assertEqual(self.git('status', '--porcelain'), '')
    def test_symlink_parent_denied(self):
        outside = self.base / 'outside'
        outside.mkdir()
        (self.root / 'public').symlink_to(outside, target_is_directory=True)
        self.git('add', 'public')
        self.commit('symlink')
        with self.assertRaises(OSError): self.receive()
        self.assertEqual(list(outside.iterdir()), [])
    def test_concurrent_target_creation_not_overwritten(self):
        source = self.base / 'original.png'
        source.write_bytes(self.data)
        real_link = os.link
        def competing_link(src, dst, **kwargs):
            fd = os.open(dst, os.O_CREAT | os.O_EXCL | os.O_WRONLY, 0o600, dir_fd=kwargs['dst_dir_fd'])
            os.write(fd, b'concurrent-file')
            os.close(fd)
            return real_link(src, dst, **kwargs)
        with patch('asset_ingest.os.link', side_effect=competing_link), self.assertRaises(FileExistsError):
            ingest(source, self.root, 'public/assets/logo.png', self.sha)
        self.assertEqual((self.root / 'public/assets/logo.png').read_bytes(), b'concurrent-file')
        self.assertEqual(list((self.root / 'public/assets').glob('.asset-*')), [])
    def test_wrong_actual_branch_denied(self):
        self.git('checkout', '-qb', 'different')
        with self.assertRaises(PermissionError): self.receive()
        self.assertEqual(self.opens, 0)
    def test_quota_audit_failure_before_download(self):
        def denied(*args): raise PermissionError('denied')
        self.adapter.reserve = denied
        with self.assertRaises(PermissionError): self.receive()
        self.adapter.reserve = self.reserve
        self.adapter.audit = denied
        with self.assertRaises(PermissionError): self.receive()
        self.assertEqual(self.opens, 0)
    def test_mcp_registration_verified_context(self):
        class Server:
            def tool(inner, *, name):
                self.assertEqual(name, 'factory_asset_receive')
                return lambda fn: fn
        tool = register_mcp(Server(), self.adapter, lambda: 'verified-owner')
        result = tool('file_owned', 'project-a', 'asset-review', 'public/assets/logo.png', self.sha)
        self.assertEqual(result['status'], 'staged')
    def test_two_isolated_projects_git_roundtrip_http(self):
        # Local integration only; does not assert remote GitHub/HML validation.
        import functools
        import http.server
        import threading
        import urllib.request
        from asset_reference_store import StoredFile, StoredReferenceResolver
        import time
        upload_store = self.base / 'uploads'
        upload_store.mkdir(mode=0o700)
        (upload_store / 'original.png').write_bytes(self.data)
        record = StoredFile('original.png', frozenset({'owner'}), time.time() + 60)
        self.adapter.open_reference = StoredReferenceResolver(upload_store, lambda ref: record if ref == 'file_owned' else None, lambda ctx: 'owner' if ctx == 'verified-owner' else None)
        for project in ('project-a', 'project-b'):
            with self.subTest(project=project):
                if project == 'project-b':
                    self.root = self.base / project
                    self.root.mkdir()
                    self.git('init', '-q', '-b', 'asset-review')
                    self.commit('baseline', empty=True)
                self.grant = replace(self.grant, project=project, checkout=self.root)
                self.receive(project=project)
                self.git('add', 'public')
                self.commit('original asset')
                raw = subprocess.check_output(['git', '-C', str(self.root), 'show', 'HEAD:public/assets/logo.png'])
                self.assertEqual(hashlib.sha256(raw).hexdigest(), self.sha)
                handler = functools.partial(http.server.SimpleHTTPRequestHandler, directory=str(self.root / 'public'))
                server = http.server.ThreadingHTTPServer(('127.0.0.1', 0), handler)
                thread = threading.Thread(target=server.serve_forever, daemon=True)
                thread.start()
                try:
                    with urllib.request.urlopen(f'http://127.0.0.1:{server.server_port}/assets/logo.png') as response:
                        self.assertEqual(response.status, 200)
                        self.assertEqual(hashlib.sha256(response.read()).hexdigest(), self.sha)
                finally:
                    server.shutdown()
                    server.server_close()
                    thread.join()

if __name__ == '__main__': unittest.main()
