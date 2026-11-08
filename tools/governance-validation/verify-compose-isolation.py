"""Check rendered test Compose against the HML isolation acceptance contract."""
import json
import sys

with open(sys.argv[1], encoding="utf-8") as stream:
    config = json.load(stream)

services = config["services"]
assert set(services) == {"governance_db", "governance_tests"}, "Unexpected service"
assert not config.get("volumes"), "Named/shared volumes are forbidden"
assert not config.get("secrets"), "Real secret references are forbidden"
assert set(config["networks"]) == {"governance_internal"}, "Unexpected network"
network = config["networks"]["governance_internal"]
assert network.get("internal") is True and not network.get("external"), "Network must be isolated"

for name, service in services.items():
    assert not service.get("ports"), f"Published port: {name}"
    assert not service.get("container_name"), f"Fixed container name: {name}"
    assert not service.get("volumes"), f"Host/shared mount: {name}"
    assert not service.get("privileged"), f"Privileged container: {name}"
    assert not service.get("pid") and not service.get("ipc"), f"Shared host namespace: {name}"
    assert service.get("restart", "no") == "no", f"Persistent worker: {name}"

db, runner = services["governance_db"], services["governance_tests"]
assert set(db["networks"]) == {"governance_internal"}, "Database network mismatch"
assert any(str(mount).startswith("/var/lib/mysql:") for mount in db["tmpfs"]), "Disposable DB storage missing"
assert runner["network_mode"] == "service:governance_db", "Database localhost must not be host localhost"
assert runner["user"] == "65534:65534" and runner["read_only"] is True, "Runner privilege mismatch"
assert runner.get("cap_drop") == ["ALL"], "Capabilities must be dropped"
assert "no-new-privileges:true" in runner["security_opt"], "Privilege escalation prevention missing"
assert any(str(mount).startswith("/tmp:") for mount in runner["tmpfs"]), "Private test temp directory missing"
assert int(db["mem_limit"]) <= 512 * 1024**2 and int(runner["mem_limit"]) <= 256 * 1024**2, "Memory quota exceeded"
assert float(db["cpus"]) <= 0.5 and float(runner["cpus"]) <= 0.5, "CPU quota exceeded"
assert int(db["pids_limit"]) <= 128 and int(runner["pids_limit"]) <= 64, "PID quota exceeded"
assert db["environment"]["MARIADB_DATABASE"] == "factory_governance_ci", "Unexpected DB"
assert runner["environment"]["FACTORY_DISPOSABLE_DB"] == "I_CONFIRM_DISPOSABLE_CI", "CI-only guard missing"
print("ISOLATION_CONTRACT_PASS")
