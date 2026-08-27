from __future__ import annotations

import json
import os
from dataclasses import dataclass
from urllib import error, parse, request


@dataclass(frozen=True)
class GitHubResponse:
    status: int
    data: dict | list


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
            message = data.get("message", "unknown") if isinstance(data, dict) else "unknown"
            raise RuntimeError(f"github_api_error:{exc.code}:{message}") from exc

    @staticmethod
    def _repo(repository: str) -> tuple[str, str]:
        return tuple(repository.split("/", 1))  # type: ignore[return-value]

    def create_issue_and_delegate(
        self,
        *,
        repository: str,
        title: str,
        body: str,
        base_branch: str = "main",
        custom_instructions: str = "",
    ) -> dict:
        owner, repo = self._repo(repository)
        issue = self._call(
            "POST",
            f"/repos/{owner}/{repo}/issues",
            {"title": title, "body": body},
        ).data
        if not isinstance(issue, dict) or not issue.get("number"):
            raise RuntimeError("github_issue_create_failed")
        assignment = {
            "assignees": ["copilot-swe-agent[bot]"],
            "agent_assignment": {
                "target_repo": repository,
                "base_branch": base_branch,
                "custom_instructions": custom_instructions,
                "custom_agent": "",
                "model": "",
            },
        }
        return self._call(
            "POST",
            f"/repos/{owner}/{repo}/issues/{issue['number']}/assignees",
            assignment,
        ).data  # type: ignore[return-value]

    def pull_request(self, repository: str, pr_number: int) -> dict:
        owner, repo = self._repo(repository)
        data = self._call("GET", f"/repos/{owner}/{repo}/pulls/{pr_number}").data
        return data if isinstance(data, dict) else {}

    def pull_requests_for_issue(self, repository: str, issue_number: int) -> list[dict]:
        owner, repo = self._repo(repository)
        query = parse.quote(f"repo:{repository} is:pr is:open #{issue_number}")
        data = self._call("GET", f"/search/issues?q={query}").data
        items = data.get("items", []) if isinstance(data, dict) else []
        return [item for item in items if isinstance(item, dict)]

    def changed_files(self, repository: str, pr_number: int) -> list[str]:
        owner, repo = self._repo(repository)
        data = self._call("GET", f"/repos/{owner}/{repo}/pulls/{pr_number}/files?per_page=100").data
        if not isinstance(data, list):
            return []
        return [str(item.get("filename")) for item in data if isinstance(item, dict) and item.get("filename")]

    def create_validation_branch(self, repository: str, branch: str, head_sha: str) -> None:
        owner, repo = self._repo(repository)
        self._call("POST", f"/repos/{owner}/{repo}/git/refs", {"ref": f"refs/heads/{branch}", "sha": head_sha})

    def create_validation_pr(
        self,
        repository: str,
        *,
        branch: str,
        base_branch: str,
        head_sha: str,
        source_pr: int,
    ) -> dict:
        owner, repo = self._repo(repository)
        payload = {
            "title": f"ci: validate Copilot PR #{source_pr}",
            "head": branch,
            "base": base_branch,
            "draft": True,
            "body": (
                f"Factory CI relay for source PR #{source_pr}. "
                f"Validates exact head SHA `{head_sha}`. Never merge this relay."
            ),
        }
        data = self._call("POST", f"/repos/{owner}/{repo}/pulls", payload).data
        return data if isinstance(data, dict) else {}

    def close_pull_request(self, repository: str, pr_number: int) -> None:
        owner, repo = self._repo(repository)
        self._call("PATCH", f"/repos/{owner}/{repo}/pulls/{pr_number}", {"state": "closed"})

    def workflow_runs_for_branch(self, repository: str, branch: str, event: str = "pull_request_target") -> list[dict]:
        owner, repo = self._repo(repository)
        query = parse.urlencode({"branch": branch, "event": event, "per_page": 20})
        data = self._call("GET", f"/repos/{owner}/{repo}/actions/runs?{query}").data
        runs = data.get("workflow_runs", []) if isinstance(data, dict) else []
        return [item for item in runs if isinstance(item, dict)]

    def approve_workflow_run(self, repository: str, run_id: int) -> None:
        owner, repo = self._repo(repository)
        self._call("POST", f"/repos/{owner}/{repo}/actions/runs/{run_id}/approve")

    def merge_pull_request(self, repository: str, pr_number: int, expected_head_sha: str) -> dict:
        owner, repo = self._repo(repository)
        payload = {"merge_method": "squash", "sha": expected_head_sha}
        data = self._call("PUT", f"/repos/{owner}/{repo}/pulls/{pr_number}/merge", payload).data
        return data if isinstance(data, dict) else {}
