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
