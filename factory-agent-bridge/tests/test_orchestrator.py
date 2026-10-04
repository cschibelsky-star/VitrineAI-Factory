from pathlib import Path
import tempfile
import sys

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "src"))

from orchestrator import AutonomousOrchestrator, TaskState
from policy import AutonomyPolicy
from store import TaskStore


class FakeGitHub:
    def __init__(self):
        self.created = 0
        self.merged = False
        self.discovered_pr = None
        self.discovery_calls = 0

    def create_issue_and_delegate(self, **kwargs):
        self.created += 1
        return {"number": 42}

    def find_pull_request_for_issue(self, repository, issue_number):
        self.discovery_calls += 1
        return self.discovered_pr

    def merge_pull_request(self, repository, pr_number, expected_head_sha):
        self.merged = True
        return {"merged": True, "sha": "abc123"}


class FakeV5:
    def project_status(self, project_id):
        return {"ok": True, "project_id": project_id}


policy = AutonomyPolicy(ROOT / "policy" / "autonomy.json")


def build_orchestrator(store=None):
    github = FakeGitHub()
    return AutonomousOrchestrator(policy=policy, github=github, v5=FakeV5(), store=store), github


def test_delegate_low_risk_task():
    orchestrator, _ = build_orchestrator()
    task = TaskState(
        project="cursos-ia-mvp",
        repository="cschibelsky-star/CursosIAMVP",
        title="Adicionar indicador financeiro",
        body="Exibir pendencias de pagamento no dashboard existente.",
    )
    result = orchestrator.delegate(task)
    assert result.state == "delegated"
    assert result.issue_number == 42


def test_delegate_is_idempotent_with_store():
    with tempfile.TemporaryDirectory() as tmp:
        store = TaskStore(Path(tmp) / "bridge.db")
        orchestrator, github = build_orchestrator(store=store)
        first = TaskState(
            project="cursos-ia-mvp",
            repository="cschibelsky-star/CursosIAMVP",
            title="Tarefa unica",
            body="Fazer uma alteracao pequena.",
        )
        second = TaskState(
            project="cursos-ia-mvp",
            repository="cschibelsky-star/CursosIAMVP",
            title="Tarefa unica",
            body="Fazer uma alteracao pequena.",
        )
        orchestrator.delegate(first)
        result = orchestrator.delegate(second)
        assert github.created == 1
        assert result.issue_number == 42
        assert any(item.get("type") == "idempotency" for item in result.evidence)


def test_discover_pull_request_waits_while_coding():
    with tempfile.TemporaryDirectory() as tmp:
        store = TaskStore(Path(tmp) / "bridge.db")
        orchestrator, github = build_orchestrator(store=store)
        task = TaskState(
            project="cursos-ia-mvp",
            repository="cschibelsky-star/CursosIAMVP",
            title="Esperar PR",
            body="Alteracao pequena.",
        )
        orchestrator.delegate(task)
        result = orchestrator.discover_pull_request(task)
        assert result.state == "coding"
        assert result.pr_number is None
        assert github.discovery_calls == 1


def test_discover_pull_request_persists_and_reuses_same_pr():
    with tempfile.TemporaryDirectory() as tmp:
        store = TaskStore(Path(tmp) / "bridge.db")
        orchestrator, github = build_orchestrator(store=store)
        task = TaskState(
            project="cursos-ia-mvp",
            repository="cschibelsky-star/CursosIAMVP",
            title="Descobrir PR",
            body="Alteracao pequena.",
        )
        orchestrator.delegate(task)
        github.discovered_pr = {"number": 77, "head": {"sha": "deadbeef"}}
        first = orchestrator.discover_pull_request(task)
        assert first.state == "pr_open"
        assert first.pr_number == 77
        assert first.head_sha == "deadbeef"
        calls_after_first = github.discovery_calls

        recreated = TaskState(
            project="cursos-ia-mvp",
            repository="cschibelsky-star/CursosIAMVP",
            title="Descobrir PR",
            body="Alteracao pequena.",
        )
        second = orchestrator.discover_pull_request(recreated)
        assert second.state == "pr_open"
        assert second.pr_number == 77
        assert second.head_sha == "deadbeef"
        assert github.discovery_calls == calls_after_first


def test_block_high_risk_task():
    orchestrator, _ = build_orchestrator()
    task = TaskState(
        project="core",
        repository="owner/repo",
        title="Alterar auth em production",
        body="Modificar permission e secret.",
    )
    result = orchestrator.delegate(task)
    assert result.state == "blocked"


def test_merge_only_with_all_gates():
    orchestrator, _ = build_orchestrator()
    task = TaskState(project="x", repository="owner/repo", title="Ajuste visual", body="UI", pr_number=7, head_sha="deadbeef")
    gates = {
        "ci_green": True,
        "review_green": True,
        "no_secrets": True,
        "no_forbidden_paths": True,
        "risk_not_high": True,
    }
    orchestrator.evaluate_merge(task, gates=gates, changed_paths=["public/dashboard.php"])
    assert task.state == "merge_ready"
    orchestrator.merge(task)
    assert task.state == "merged"


def test_hml_status_via_v5():
    orchestrator, _ = build_orchestrator()
    task = TaskState(project="cursos-ia-mvp", repository="owner/repo", title="Ajuste visual", body="UI")
    result = orchestrator.validate_hml_status(task, "cursos-ia-mvp")
    assert result.state == "hml_validating"
