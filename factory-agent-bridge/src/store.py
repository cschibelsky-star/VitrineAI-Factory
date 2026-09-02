from __future__ import annotations

import json
import sqlite3
import threading
from dataclasses import asdict
from pathlib import Path
from typing import Any


class TaskStore:
    """Small persistent store used to make autonomous task execution idempotent."""

    def __init__(self, path: str | Path) -> None:
        self.path = str(path)
        Path(self.path).parent.mkdir(parents=True, exist_ok=True)
        self._lock = threading.RLock()
        with self._connect() as conn:
            conn.execute(
                """
                CREATE TABLE IF NOT EXISTS tasks (
                    task_key TEXT PRIMARY KEY,
                    payload TEXT NOT NULL,
                    state TEXT NOT NULL,
                    issue_number INTEGER,
                    pr_number INTEGER,
                    head_sha TEXT,
                    merge_sha TEXT,
                    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
                """
            )

    def _connect(self) -> sqlite3.Connection:
        conn = sqlite3.connect(self.path, timeout=30)
        conn.row_factory = sqlite3.Row
        return conn

    def get(self, task_key: str) -> dict[str, Any] | None:
        with self._lock, self._connect() as conn:
            row = conn.execute("SELECT * FROM tasks WHERE task_key = ?", (task_key,)).fetchone()
            return dict(row) if row else None

    def save(self, task_key: str, task: Any, merge_sha: str | None = None) -> None:
        payload = json.dumps(asdict(task), ensure_ascii=False)
        with self._lock, self._connect() as conn:
            conn.execute(
                """
                INSERT INTO tasks(task_key, payload, state, issue_number, pr_number, head_sha, merge_sha)
                VALUES(?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(task_key) DO UPDATE SET
                    payload=excluded.payload,
                    state=excluded.state,
                    issue_number=excluded.issue_number,
                    pr_number=excluded.pr_number,
                    head_sha=excluded.head_sha,
                    merge_sha=COALESCE(excluded.merge_sha, tasks.merge_sha),
                    updated_at=CURRENT_TIMESTAMP
                """,
                (
                    task_key,
                    payload,
                    task.state,
                    task.issue_number,
                    task.pr_number,
                    task.head_sha,
                    merge_sha,
                ),
            )

    def task_payload(self, task_key: str) -> dict[str, Any] | None:
        row = self.get(task_key)
        if not row:
            return None
        return json.loads(row["payload"])
