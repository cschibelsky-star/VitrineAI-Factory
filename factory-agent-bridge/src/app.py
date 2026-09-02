from __future__ import annotations

import json
import os
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path

from github_copilot import GitHubCopilotAdapter
from orchestrator import AutonomousOrchestrator, TaskState
from policy import AutonomyPolicy
from store import TaskStore

BASE_DIR = Path(__file__).resolve().parents[1]
POLICY_PATH = BASE_DIR / "policy" / "autonomy.json"
POLICY = AutonomyPolicy(POLICY_PATH)
STORE_PATH = Path(os.getenv("BRIDGE_DB_PATH", "/data/factory-agent-bridge.db"))
STORE = TaskStore(STORE_PATH)


def json_response(handler: BaseHTTPRequestHandler, status: int, payload: dict) -> None:
    raw = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    handler.send_response(status)
    handler.send_header("Content-Type", "application/json; charset=utf-8")
    handler.send_header("Content-Length", str(len(raw)))
    handler.end_headers()
    handler.wfile.write(raw)


class Handler(BaseHTTPRequestHandler):
    def do_GET(self) -> None:  # noqa: N802
        if self.path == "/health":
            json_response(self, 200, {"ok": True, "service": "factory-agent-bridge", "version": "0.3.0", "store": "sqlite"})
            return
        if self.path == "/policy":
            json_response(self, 200, POLICY.data)
            return
        json_response(self, 404, {"ok": False, "error": "not_found"})

    def do_POST(self) -> None:  # noqa: N802
        length = int(self.headers.get("Content-Length", "0"))
        body = self.rfile.read(length) if length else b"{}"
        try:
            payload = json.loads(body.decode("utf-8"))
        except (UnicodeDecodeError, json.JSONDecodeError):
            json_response(self, 400, {"ok": False, "error": "invalid_json"})
            return

        if self.path == "/decisions/evaluate":
            decision = POLICY.evaluate(
                actor=payload.get("actor", "copilot"),
                environment=payload.get("environment", "development"),
                action=payload.get("action", "read"),
                changed_paths=payload.get("changed_paths", []),
                task_text=payload.get("task_text", ""),
                gates=payload.get("gates", {}),
            )
            json_response(self, 200, {"ok": True, "decision": decision.__dict__})
            return

        if self.path == "/tasks/plan":
            task = {
                "project": payload.get("project"),
                "provider": payload.get("provider", "copilot"),
                "environment": payload.get("environment", "development"),
                "state": "planned",
                "autonomy": "automatic",
                "production": "blocked",
                "next": ["create_issue", "delegate_agent", "watch_pr", "watch_ci", "review", "merge_if_green", "deploy_hml", "validate_hml"],
            }
            json_response(self, 200, {"ok": True, "task": task})
            return

        if self.path == "/tasks/delegate":
            project = str(payload.get("project", ""))
            repository = str(payload.get("repository", ""))
            title = str(payload.get("title", ""))
            task_body = str(payload.get("body", ""))
            base_branch = str(payload.get("base_branch", "main"))
            if not project or not repository or not title or not task_body:
                json_response(self, 422, {"ok": False, "error": "project_repository_title_body_required"})
                return

            task = TaskState(
                project=project,
                repository=repository,
                title=title,
                body=task_body,
                base_branch=base_branch,
            )
            existing = STORE.get(task.task_key)
            try:
                github = GitHubCopilotAdapter()
                orchestrator = AutonomousOrchestrator(policy=POLICY, github=github, v5=None, store=STORE)
                result = orchestrator.delegate(task)
            except (ValueError, RuntimeError) as exc:
                json_response(self, 502, {"ok": False, "error": str(exc)})
                return

            status = 200 if existing else 201
            json_response(
                self,
                status,
                {
                    "ok": True,
                    "task_key": result.task_key,
                    "state": result.state,
                    "issue_number": result.issue_number,
                    "idempotent_reuse": existing is not None,
                    "production": "blocked",
                },
            )
            return

        json_response(self, 404, {"ok": False, "error": "not_found"})

    def log_message(self, fmt: str, *args: object) -> None:
        return


if __name__ == "__main__":
    host = os.getenv("BRIDGE_HOST", "0.0.0.0")
    port = int(os.getenv("BRIDGE_PORT", "8094"))
    HTTPServer((host, port), Handler).serve_forever()
