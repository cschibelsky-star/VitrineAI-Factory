from pathlib import Path
import sys

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "src"))

from policy import AutonomyPolicy


policy = AutonomyPolicy(ROOT / "policy" / "autonomy.json")


def test_development_write_allowed():
    decision = policy.evaluate(actor="copilot", environment="development", action="write")
    assert decision.allowed is True


def test_production_deploy_denied():
    decision = policy.evaluate(actor="copilot", environment="production", action="deploy")
    assert decision.allowed is False


def test_forbidden_env_path_denied():
    decision = policy.evaluate(
        actor="copilot",
        environment="development",
        action="write",
        changed_paths=[".env"],
    )
    assert decision.allowed is False
    assert decision.reason == "forbidden_path"


def test_merge_requires_all_gates():
    decision = policy.evaluate(
        actor="copilot",
        environment="development",
        action="merge_if_green",
        gates={"ci_green": True},
    )
    assert decision.allowed is False


def test_merge_allowed_when_all_gates_green():
    gates = {
        "ci_green": True,
        "review_green": True,
        "no_secrets": True,
        "no_forbidden_paths": True,
        "risk_not_high": True,
    }
    decision = policy.evaluate(
        actor="copilot",
        environment="development",
        action="merge_if_green",
        gates=gates,
    )
    assert decision.allowed is True


def test_nested_secret_paths_are_denied():
    for path in ["service/.env", "service/.env.production", "service/secrets/token.txt"]:
        decision = policy.evaluate(
            actor="copilot",
            environment="development",
            action="write",
            changed_paths=[path],
        )
        assert decision.allowed is False
        assert decision.reason == "forbidden_path"


def test_merge_rejects_truthy_non_boolean_gate_values():
    gates = {
        "ci_green": "false",
        "review_green": "true",
        "no_secrets": "true",
        "no_forbidden_paths": "true",
        "risk_not_high": "true",
    }
    decision = policy.evaluate(
        actor="copilot",
        environment="development",
        action="merge_if_green",
        gates=gates,
    )
    assert decision.allowed is False
    assert decision.reason.startswith("missing_gates:")


def test_hml_deploy_requires_hml_gates():
    decision = policy.evaluate(
        actor="copilot",
        environment="hml",
        action="deploy",
        gates={},
    )
    assert decision.allowed is False
    assert decision.reason.startswith("missing_gates:")


def test_hml_deploy_allowed_when_all_hml_gates_green():
    decision = policy.evaluate(
        actor="copilot",
        environment="hml",
        action="deploy",
        gates={
            "merge_completed": True,
            "build_green": True,
            "health_green": True,
        },
    )
    assert decision.allowed is True
