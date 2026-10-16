import hashlib
import io
import json
import multiprocessing
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from asset_runtime import PrivateRuntime
from asset_intake import Grant


def reserve_in_child(root, grant, queue):
    runtime = PrivateRuntime(root, (grant,), lambda ctx: ctx)
    try:
        with runtime.reserve(grant): queue.put('unexpected')
    except PermissionError:
        queue.put('blocked')

class RuntimeTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.base = Path(self.temp.name)
        self.projects = []
        self.grants = []
        for name in ('project-a', 'project-b'):
            checkout = self.base / name
            checkout.mkdir()
            subprocess.run(['git', 'init', '-q', '-b', 'asset-review', str(checkout)], check=True)
            self.git(checkout, '-c', 'user.name=Test', '-c', 'user.email=test@example.invalid',
                     'commit', '-qm', 'baseline', '--allow-empty')
            self.projects.append(checkout)
            self.grants.append(Grant('owner', name, 'asset-review', checkout, ('public/assets',), 1024))
        self.runtime = PrivateRuntime(self.base / 'state', self.grants, lambda ctx: 'owner' if ctx == 'verified' else None)
        self.data = bytes.fromhex('89504e470d0a1a0a') + b'fixture'
        self.sha = hashlib.sha256(self.data).hexdigest()
    def git(self, checkout, *args):
        return subprocess.check_output(['git', '-C', str(checkout), *args], stderr=subprocess.DEVNULL)
    def upload(self, context='verified', **overrides):
        args = dict(project='project-a', branch='asset-review', stream=io.BytesIO(self.data), sha256=self.sha)
        args.update(overrides)
        return self.runtime.accept_upload(context, **args)
    def test_provider_to_intake_two_projects_persistent_receipts(self):
        for grant in self.grants:
            upload = self.upload(project=grant.project)
            result = self.runtime.intake.receive('verified', file_reference=upload['file_reference'],
                project=grant.project, branch=grant.branch, destination='public/assets/logo.png', sha256=self.sha)
            self.assertEqual(result['receipt']['sha256'], self.sha)
            self.assertEqual((grant.checkout / 'public/assets/logo.png').read_bytes(), self.data)
            self.git(grant.checkout, 'add', 'public')
            self.git(grant.checkout, '-c', 'user.name=Test', '-c', 'user.email=test@example.invalid', 'commit', '-qm', 'asset')
            self.assertEqual(hashlib.sha256(self.git(grant.checkout, 'show', 'HEAD:public/assets/logo.png')).hexdigest(), self.sha)
        with self.runtime.db() as db:
            events = [json.loads(row[0]) for row in db.execute('SELECT event FROM audit ORDER BY id')]
        self.assertEqual([e['status'] for e in events], ['received', 'authorized', 'staged'] * 2)
        self.assertEqual(list((self.base / 'state/intake').iterdir()), [])
        restarted = PrivateRuntime(self.base / 'state', self.grants, lambda ctx: 'owner')
        self.assertIsNotNone(restarted.lookup(upload['file_reference']))
    def test_unknown_identity_project_branch(self):
        for kwargs in [dict(context=None), dict(project='other'), dict(branch='main')]:
            with self.assertRaises(PermissionError): self.upload(**kwargs)
        self.assertEqual(list((self.base / 'state/uploads').iterdir()), [])
    def test_bad_checksum_and_limit_remove_partial_upload(self):
        with self.assertRaises(ValueError): self.upload(sha256='0'*64)
        with self.assertRaises(ValueError): self.upload(stream=io.BytesIO(b'x'*1025))
        self.assertEqual(list((self.base / 'state/uploads').iterdir()), [])
    def test_quota_is_durable(self):
        self.runtime.daily_bytes = 1024
        self.upload()
        with self.assertRaises(PermissionError): self.upload()
        restarted = PrivateRuntime(self.base / 'state', self.grants, lambda ctx: 'owner', daily_bytes=1024)
        with self.assertRaises(PermissionError): restarted.charge('owner', 1)
    def test_exclusive_lock_between_processes(self):
        ctx = multiprocessing.get_context('spawn')
        queue = ctx.Queue()
        with self.runtime.reserve(self.grants[0]):
            child = ctx.Process(target=reserve_in_child, args=(self.base / 'state', self.grants[0], queue))
            child.start()
            child.join(timeout=10)
            if child.is_alive():
                child.terminate()
                child.join()
                self.fail('lock test hung')
            self.assertEqual(child.exitcode, 0)
            self.assertEqual(queue.get(timeout=2), 'blocked')
        queue.close()
        with self.runtime.reserve(self.grants[0]): pass
    def test_reference_acl_expiry_and_no_path_leak(self):
        upload = self.upload(ttl=1)
        reference = upload['file_reference']
        self.assertNotIn(str(self.base), json.dumps(upload))
        with self.runtime.db() as db:
            db.execute('UPDATE files SET expires=0 WHERE reference=?', (reference,))
        with self.assertRaises(PermissionError), self.runtime.intake.open_reference('verified', reference): pass
    def test_state_permissions_and_symlinks(self):
        unsafe = self.base / 'unsafe'
        unsafe.mkdir(mode=0o755)
        with self.assertRaises(PermissionError): PrivateRuntime(unsafe, (), lambda ctx: ctx)
        link = self.base / 'link'
        link.symlink_to(self.base / 'state', target_is_directory=True)
        with self.assertRaises(PermissionError): PrivateRuntime(link, (), lambda ctx: ctx)
    def test_real_registration(self):
        class Server:
            def tool(self, *, name): return lambda fn: fn
        upload = self.upload()
        tool = self.runtime.register(Server(), lambda: 'verified')
        self.assertEqual(tool(upload['file_reference'], 'project-a', 'asset-review', 'public/assets/logo.png', self.sha)['status'], 'staged')

if __name__ == '__main__': unittest.main()
