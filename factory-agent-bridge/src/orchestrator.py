from __future__ import annotations

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


class AutonomousOrchestrator:
    """Policy-driven coordinator. External side effects are delegated to adapters."""

    def __init__(self, *, policy: AutonomyPolicy, github, v5) -> None:
        self.policy = policy
        self.github = github
        self.v5 = v5

    def delegate(self, task: TaskState) -> TaskState:
        decision = self.policy.evaluate(
            actor="copilot",
            environment="development",
            action="pull_request",
            task_text=task.title + "\n" + task.body,
        )
        if not decision.allowed:
            task.state = "blocked"
            task.evidence.append({"type": "policy", "reason": decision.reason})
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
        return task

    def merge(self, task: TaskState) -> TaskState:
        if task.state != "merge_ready" or not task.pr_number or not task.head_sha:
            task.state = "needs_attention"
            return task
        result = self.github.merge_pull_request(task.repository, task.pr_number, task.head_sha)
        if result.get("merged") is True:
            task.state = "merged"
            task.evidence.append({"type": "merge", "sha": result.get("sha")})
        else:
            task.state = "needs_attention"
            task.evidence.append({"type": "merge", "message": result.get("message")})
        return task

    def validate_hml_status(self, task: TaskState, project_id: str) -> TaskState:
        decision = self.policy.evaluate(actor="copilot", environment="hml", action="healthcheck")
        if not decision.allowed:
            task.state = "blocked"
            return task
        status = self.v5.project_status(project_id)
        task.evidence.append({"type": "v5_project_status", "result": status})
        task.state = "hml_validating" if status.get("ok") else "needs_attention"
        return task
