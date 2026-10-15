import sys
import tempfile
import unittest
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from external_project_link import validate, link_existing

DATA = dict(id="vitrine-institutional",repository="git@github.com:cschibelsky-star/vitrine-ai-pro.git",
            branch="design/vitrine-showcase-approved-20261014",
            hml_url="https://site.hml.vitrineiapro.com.br/",
            operational_project_id="vitrine-site-hml",
            workspace_root="/srv/projects/vitrine-site-hml",mode="linked_existing_read_only")

def inspect(_):
    return dict(workspace_root=DATA["workspace_root"],repository=DATA["repository"],head_branch=DATA["branch"])

class LinkedProjectTests(unittest.TestCase):
    def test_verified_existing_project(self):
        with tempfile.TemporaryDirectory() as directory:
            item = link_existing(DATA,registry_dir=directory,inspect_project=inspect,probe_hml=lambda _: True)
            self.assertEqual(item["status"],"linked_read_only")
            self.assertTrue(Path(item["manifest"]).exists())
            with self.assertRaises(FileExistsError):
                link_existing(DATA,registry_dir=directory,inspect_project=inspect,probe_hml=lambda _: True)

    def test_refuses_untrusted_or_unavailable(self):
        for change in [
            {"hml_url":"http://site.hml.vitrineiapro.com.br/"},
            {"hml_url":"https://outside.example.com/"},
            {"workspace_root":"/etc/passwd"},
            {"mode":"production"},
            {"branch":"main"},
            {"repository":"https://evil.example.com/repo"},
        ]:
            with self.subTest(change=change):
                with self.assertRaises(ValueError):
                    validate(dict(DATA, **change))
        with tempfile.TemporaryDirectory() as directory:
            with self.assertRaises(ValueError):
                link_existing(DATA,registry_dir=directory,inspect_project=inspect,probe_hml=lambda _: False)
            with self.assertRaises(ValueError):
                link_existing(DATA,registry_dir=directory,inspect_project=lambda _: dict(inspect(None), head_branch="wrong"),probe_hml=lambda _: True)

if __name__=="__main__":
    unittest.main()
