from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "src"))

from orchestrator import AutonomousOrchestrator, TaskState
from policy import AutonomyPolicy


class FakeGitHub:
    def __init__(self):
        self.created = False
        self.merged = False

    def create_issue_and_delegate(self, **kwargs):
        self.created = True
        return {"number": 42}

    def merge_pull_request(self, repository, pr_number, expected_head_sha):
        self.merged = True
        return {"merged": True, "sha": "abc123"}


class FakeV5:
    def project_status(self, project_id):
        return {"ok": True, "project_id": project_id}


policy = AutonomyPolicy(ROOT / "policy" / "autonomy.json")


def build_orchestrator():
    return AutonomousOrchestrator(policy=policy, github=FakeGitHub(), v5=FakeV5())


def test_delegate_low_risk_task():
    orchestrator = build_orchestrator()
    task = TaskState(
        project="cursos-ia-mvp",
        repository="cschibelsky-star/CursosIAMVP",
        title="Adicionar indicador financeiro",
        body="Exibir pendencias de pagamento no dashboard existente.",
    )
    result = orchestrator.delegate(task)
    assert result.state == "delegated"
    assert result.issue_number == 42


def test_block_high_risk_task():
    orchestrator = build_orchestrator()
    task = TaskState(
        project="core",
        repository="owner/repo",
        title="Alterar auth em production",
        body="Modificar permission e secret.",
    )
    result = orchestrator.delegate(task)
    assert result.state == "blocked"


def test_merge_only_with_all_gates():
    orchestrator = build_orchestrator()
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
    orchestrator = build_orchestrator()
    task = TaskState(project="cursos-ia-mvp", repository="owner/repo", title="Ajuste visual", body="UI")
    result = orchestrator.validate_hml_status(task, "cursos-ia-mvp")
    assert result.state == "hml_validating"
