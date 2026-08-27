from __future__ import annotations

import fnmatch
import json
from dataclasses import dataclass
from pathlib import Path
from typing import Iterable


@dataclass(frozen=True)
class Decision:
    allowed: bool
    reason: str
    risk: str


class AutonomyPolicy:
    def __init__(self, policy_path: str | Path) -> None:
        self.policy_path = Path(policy_path)
        self.data = json.loads(self.policy_path.read_text(encoding="utf-8"))

    def _path_forbidden(self, paths: Iterable[str]) -> bool:
        patterns = self.data.get("forbidden_paths", [])
        return any(fnmatch.fnmatch(path, pattern) for path in paths for pattern in patterns)

    def _high_risk(self, text: str) -> bool:
        lowered = text.lower()
        return any(pattern.lower() in lowered for pattern in self.data.get("high_risk_patterns", []))

    def evaluate(
        self,
        *,
        actor: str,
        environment: str,
        action: str,
        changed_paths: Iterable[str] = (),
        task_text: str = "",
        gates: dict[str, bool] | None = None,
    ) -> Decision:
        if self._path_forbidden(changed_paths):
            return Decision(False, "forbidden_path", "high")

        if self._high_risk(task_text):
            return Decision(False, "high_risk_pattern", "high")

        actor_rules = self.data.get("actors", {}).get(actor, {})
        env_rules = actor_rules.get(environment, {})
        configured = env_rules.get(action, self.data.get("default", "deny"))

        if configured != "allow":
            return Decision(False, "policy_denied", "high" if environment == "production" else "medium")

        if action == "merge_if_green":
            required = self.data.get("gates", {}).get("merge", [])
            actual = gates or {}
            missing = [gate for gate in required if not actual.get(gate, False)]
            if missing:
                return Decision(False, "missing_gates:" + ",".join(missing), "medium")

        return Decision(True, "policy_allowed", "low")
