from __future__ import annotations

import os
import time
from pathlib import Path
from urllib import request
import json

import jwt


class GitHubCredentialProvider:
    """Resolve a short-lived GitHub token.

    Preferred mode: GitHub App installation token.
    Fallback mode: static GITHUB_TOKEN for lab use.
    """

    def __init__(self, api_base: str = "https://api.github.com") -> None:
        self.api_base = api_base.rstrip("/")
        self._cached_token: str | None = None
        self._expires_at: int = 0

    def _static_token(self) -> str | None:
        token = os.getenv("GITHUB_TOKEN", "").strip()
        return token or None

    def _app_settings(self) -> tuple[str, str, Path] | None:
        app_id = os.getenv("GITHUB_APP_ID", "").strip()
        installation_id = os.getenv("GITHUB_APP_INSTALLATION_ID", "").strip()
        key_path_raw = os.getenv("GITHUB_APP_PRIVATE_KEY_PATH", "").strip()
        if not (app_id and installation_id and key_path_raw):
            return None
        return app_id, installation_id, Path(key_path_raw)

    def _installation_token(self) -> str:
        settings = self._app_settings()
        if settings is None:
            raise ValueError("github_credentials_missing")
        app_id, installation_id, key_path = settings
        if not key_path.is_file():
            raise ValueError("github_app_private_key_missing")

        now = int(time.time())
        if self._cached_token and now < self._expires_at - 120:
            return self._cached_token

        private_key = key_path.read_text(encoding="utf-8")
        app_jwt = jwt.encode(
            {"iat": now - 60, "exp": now + 540, "iss": app_id},
            private_key,
            algorithm="RS256",
        )
        req = request.Request(
            f"{self.api_base}/app/installations/{installation_id}/access_tokens",
            data=b"{}",
            method="POST",
            headers={
                "Accept": "application/vnd.github+json",
                "Authorization": f"Bearer {app_jwt}",
                "X-GitHub-Api-Version": "2022-11-28",
                "User-Agent": "vitrine-factory-agent-bridge",
                "Content-Type": "application/json",
            },
        )
        with request.urlopen(req, timeout=30) as response:
            data = json.loads(response.read().decode("utf-8"))
        token = str(data.get("token", ""))
        expires_at = str(data.get("expires_at", ""))
        if not token:
            raise RuntimeError("github_app_installation_token_missing")

        # GitHub installation tokens are currently valid for one hour. Keep a conservative cache.
        self._cached_token = token
        self._expires_at = now + 50 * 60
        if not expires_at:
            self._expires_at = now + 45 * 60
        return token

    def token(self) -> str:
        if self._app_settings() is not None:
            return self._installation_token()
        token = self._static_token()
        if token:
            return token
        raise ValueError("github_credentials_missing")

    def mode(self) -> str:
        if self._app_settings() is not None:
            return "github_app"
        if self._static_token():
            return "static_token"
        return "missing"
