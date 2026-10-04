from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "src"))

from github_auth import GitHubCredentialProvider


def test_auth_mode_missing(monkeypatch):
    for name in (
        "GITHUB_TOKEN",
        "GITHUB_APP_ID",
        "GITHUB_APP_INSTALLATION_ID",
        "GITHUB_APP_PRIVATE_KEY_PATH",
    ):
        monkeypatch.delenv(name, raising=False)
    provider = GitHubCredentialProvider()
    assert provider.mode() == "missing"


def test_auth_mode_static_token(monkeypatch):
    monkeypatch.setenv("GITHUB_TOKEN", "lab-token")
    monkeypatch.delenv("GITHUB_APP_ID", raising=False)
    monkeypatch.delenv("GITHUB_APP_INSTALLATION_ID", raising=False)
    monkeypatch.delenv("GITHUB_APP_PRIVATE_KEY_PATH", raising=False)
    provider = GitHubCredentialProvider()
    assert provider.mode() == "static_token"
    assert provider.token() == "lab-token"


def test_auth_mode_prefers_github_app(monkeypatch, tmp_path):
    key = tmp_path / "app.pem"
    key.write_text("not-used-by-mode-check", encoding="utf-8")
    monkeypatch.setenv("GITHUB_TOKEN", "lab-token")
    monkeypatch.setenv("GITHUB_APP_ID", "123")
    monkeypatch.setenv("GITHUB_APP_INSTALLATION_ID", "456")
    monkeypatch.setenv("GITHUB_APP_PRIVATE_KEY_PATH", str(key))
    provider = GitHubCredentialProvider()
    assert provider.mode() == "github_app"
