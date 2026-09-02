from __future__ import annotations

import hashlib
from dataclasses import dataclass, field

from policy import AutonomyPolicy


@dataclass
class TaskState:
    project: str
    repository: str
    title: str
    body: str
    base_branch: str = "main"
    provider: str = "copilot"
    state: str = "received"
    issue_number: int | None = None
    pr_number: int | None = None
    head_sha: str | None = None
    evidence: list[dict] = field(default_factory=list)

    @property
    def task_key(self) -> str:
        raw = "\n".join(
            [self.project, self.repository, self.base_branch, self.provider, self.title, self.body]
        ).encode("utf-8")
        return hashlib.sha256(raw).hexdigest()


class AutonomousOrchestrator:
    """Policy-driven coordinator. External side effects are delegated to adapters."""

    def __init__(self, *, policy: AutonomyPolicy, github, v5, store=None) -> None:
        self.policy = policy
        self.github = github
        self.v5 = v5
        self.store = store

    def _persist(self, task: TaskState, *, merge_sha: str | None = None) -> None:
        if self.store is not None:
            self.store.save(task.task_key, task, merge_sha=merge_sha)

    def _restore_existing(self, task: TaskState) -> bool:
        if self.store is None:
            return False
        existing = self.store.task_payload(task.task_key)
        if not existing:
            return False
        task.state = existing.get("state", task.state)
        task.issue_number = existing.get("issue_number")
        task.pr_number = existing.get("pr_number")
        task.head_sha = existing.get("head_sha")
        task.evidence = existing.get("evidence", [])
        task.evidence.append({"type": "idempotency", "reused": True})
        return True

    def delegate(self, task: TaskState) -> TaskState:
        if self._restore_existing(task) and task.issue_number:
            self._persist(task)
            return task

        decision = self.policy.evaluate(
            actor="copilot",
            environment="development",
            action="pull_request",
            task_text=task.title + "\n" + task.body,
        )
        if not decision.allowed:
            task.state = "blocked"
            task.evidence.append({"type": "policy", "reason": decision.reason})
            self._persist(task)
            return task

        issue = self.github.create_issue_and_delegate(
            repository=task.repository,
            title=task.title,
            body=task.body,
            base_branch=task.base_branch,
        )
        task.issue_number = issue.get("number")
        task.state = "delegated"
        task.evidence.append({"type": "github_issue", "number": task.issue_number})
        self._persist(task)
        return task

    def discover_pull_request(self, task: TaskState) -> TaskState:
        """Advance a delegated task when Copilot has opened its PR.

        Repeated calls are safe. Once the PR/head are persisted, discovery becomes a no-op.
        """
        if self._restore_existing(task):
            if task.pr_number and task.head_sha:
                return task

        if not task.issue_number:
            task.state = "needs_attention"
            task.evidence.append({"type": "pull_request_discovery", "reason": "issue_number_missing"})
            self._persist(task)
            return task

        pr = self.github.find_pull_request_for_issue(task.repository, task.issue_number)
        if not pr:
            task.state = "coding"
            task.evidence.append({"type": "pull_request_discovery", "found": False})
            self._persist(task)
            return task

        number = pr.get("number")
        head = pr.get("head") or {}
        head_sha = head.get("sha") if isinstance(head, dict) else None
        if not number or not head_sha:
            task.state = "needs_attention"
            task.evidence.append({"type": "pull_request_discovery", "reason": "invalid_pr_payload"})
            self._persist(task)
            return task

        task.pr_number = int(number)
        task.head_sha = str(head_sha)
        task.state = "pr_open"
        task.evidence.append({"type": "pull_request", "number": task.pr_number, "head_sha": task.head_sha})
        self._persist(task)
        return task

    def attach_pull_request(self, task: TaskState, *, pr_number: int, head_sha: str) -> TaskState:
        task.pr_number = pr_number
        task.head_sha = head_sha
        task.state = "pr_open"
        task.evidence.append({"type": "pull_request", "number": pr_number, "head_sha": head_sha})
        self._persist(task)
        return task

    def evaluate_merge(self, task: TaskState, *, gates: dict[str, bool], changed_paths: list[str]) -> TaskState:
        decision = self.policy.evaluate(
            actor="copilot",
            environment="development",
            action="merge_if_green",
            changed_paths=changed_paths,
            task_text=task.title + "\n" + task.body,
            gates=gates,
        )
        task.evidence.append({"type": "merge_policy", "allowed": decision.allowed, "reason": decision.reason})
        task.state = "merge_ready" if decision.allowed else "needs_attention"
        self._persist(task)
        return task

    def merge(self, task: TaskState) -> TaskState:
        if task.state != "merge_ready" or not task.pr_number or not task.head_sha:
            task.state = "needs_attention"
            self._persist(task)
            return task
        result = self.github.merge_pull_request(task.repository, task.pr_number, task.head_sha)
        if result.get("merged") is True:
            task.state = "merged"
            merge_sha = result.get("sha")
            task.evidence.append({"type": "merge", "sha": merge_sha})
            self._persist(task, merge_sha=merge_sha)
        else:
            task.state = "needs_attention"
            task.evidence.append({"type": "merge", "message": result.get("message")})
            self._persist(task)
        return task

    def validate_hml_status(self, task: TaskState, project_id: str) -> TaskState:
        decision = self.policy.evaluate(actor="copilot", environment="hml", action="healthcheck")
        if not decision.allowed:
            task.state = "blocked"
            self._persist(task)
            return task
        status = self.v5.project_status(project_id)
        task.evidence.append({"type": "v5_project_status", "result": status})
        task.state = "hml_validating" if status.get("ok") else "needs_attention"
        self._persist(task)
        return task
