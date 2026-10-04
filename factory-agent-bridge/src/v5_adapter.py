from __future__ import annotations

import json
import os
from dataclasses import dataclass
from urllib import error, request


@dataclass(frozen=True)
class V5Response:
    status: int
    data: dict


class V5Adapter:
    """HTTP adapter for the Vitrine MCP V5 operational boundary.

    The adapter is intentionally generic: it sends named tool calls to the V5
    endpoint and never talks directly to Docker or the host filesystem.
    """

    def __init__(self, base_url: str | None = None, token: str | None = None) -> None:
        self.base_url = (base_url or os.getenv("V5_BASE_URL", "")).rstrip("/")
        self.token = token or os.getenv("V5_TOKEN", "")
        if not self.base_url:
            raise ValueError("V5_BASE_URL is required")
        if not self.token:
            raise ValueError("V5_TOKEN is required")

    def _call(self, tool: str, arguments: dict) -> V5Response:
        payload = json.dumps({"tool": tool, "arguments": arguments}).encode("utf-8")
        req = request.Request(
            self.base_url,
            data=payload,
            method="POST",
            headers={
                "Authorization": f"Bearer {self.token}",
                "Content-Type": "application/json",
                "User-Agent": "vitrine-factory-agent-bridge",
            },
        )
        try:
            with request.urlopen(req, timeout=60) as response:
                raw = response.read()
                data = json.loads(raw.decode("utf-8")) if raw else {}
                return V5Response(response.status, data)
        except error.HTTPError as exc:
            raw = exc.read()
            data = json.loads(raw.decode("utf-8")) if raw else {"message": str(exc)}
            raise RuntimeError(f"v5_api_error:{exc.code}:{data.get('message', 'unknown')}") from exc

    def project_status(self, project_id: str) -> dict:
        return self._call("project_status", {"project_id": project_id}).data

    def php_lint(self, project_id: str, path: str) -> dict:
        return self._call("project_php_lint", {"project_id": project_id, "path": path}).data

    def compose(self, *, project_id: str, compose_file: str, action: str, docker_project: str = "") -> dict:
        return self._call(
            "project_compose_explicit",
            {
                "project_id": project_id,
                "compose_file": compose_file,
                "action": action,
                "docker_project": docker_project,
            },
        ).data
