from __future__ import annotations

import json
import os
from dataclasses import dataclass
from urllib import error, request


@dataclass(frozen=True)
class GitHubResponse:
    status: int
    data: dict


class GitHubCopilotAdapter:
    """Minimal GitHub/Copilot adapter using the official REST API.

    Credentials are read only from GITHUB_TOKEN. The adapter never logs tokens.
    """

    def __init__(self, token: str | None = None, api_base: str = "https://api.github.com") -> None:
        self.token = token or os.getenv("GITHUB_TOKEN", "")
        self.api_base = api_base.rstrip("/")
        if not self.token:
            raise ValueError("GITHUB_TOKEN is required")

    def _call(self, method: str, path: str, payload: dict | None = None) -> GitHubResponse:
        body = None if payload is None else json.dumps(payload).encode("utf-8")
        req = request.Request(
            self.api_base + path,
            data=body,
            method=method,
            headers={
                "Accept": "application/vnd.github+json",
                "Authorization": f"Bearer {self.token}",
                "X-GitHub-Api-Version": "2022-11-28",
                "User-Agent": "vitrine-factory-agent-bridge",
                "Content-Type": "application/json",
            },
        )
        try:
            with request.urlopen(req, timeout=30) as response:
                raw = response.read()
                data = json.loads(raw.decode("utf-8")) if raw else {}
                return GitHubResponse(response.status, data)
        except error.HTTPError as exc:
            raw = exc.read()
            data = json.loads(raw.decode("utf-8")) if raw else {"message": str(exc)}
            raise RuntimeError(f"github_api_error:{exc.code}:{data.get('message', 'unknown')}") from exc

    def create_issue_and_delegate(
        self,
        *,
        repository: str,
        title: str,
        body: str,
        base_branch: str = "main",
        custom_instructions: str = "",
    ) -> dict:
        owner, repo = repository.split("/", 1)
        payload = {
            "title": title,
            "body": body,
            "assignees": ["copilot-swe-agent[bot]"],
            "agent_assignment": {
                "target_repo": repository,
                "base_branch": base_branch,
                "custom_instructions": custom_instructions,
                "custom_agent": "",
                "model": "",
            },
        }
        return self._call("POST", f"/repos/{owner}/{repo}/issues", payload).data

    def approve_workflow_run(self, repository: str, run_id: int) -> None:
        owner, repo = repository.split("/", 1)
        self._call("POST", f"/repos/{owner}/{repo}/actions/runs/{run_id}/approve")

    def merge_pull_request(self, repository: str, pr_number: int, expected_head_sha: str) -> dict:
        owner, repo = repository.split("/", 1)
        payload = {"merge_method": "squash", "sha": expected_head_sha}
        return self._call("PUT", f"/repos/{owner}/{repo}/pulls/{pr_number}/merge", payload).data

    def pull_request(self, repository: str, pr_number: int) -> dict:
        owner, repo = repository.split("/", 1)
        return self._call("GET", f"/repos/{owner}/{repo}/pulls/{pr_number}").data

    def workflow_runs_for_branch(self, repository: str, branch: str) -> list[dict]:
        owner, repo = repository.split("/", 1)
        path = f"/repos/{owner}/{repo}/actions/runs?branch={branch}&event=pull_request"
        return self._call("GET", path).data.get("workflow_runs", [])
