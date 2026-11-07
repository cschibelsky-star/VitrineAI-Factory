<?php

// These contracts describe required proof; unavailable validators never approve.
return [
    'architecture' => ['required_checks' => ['contracts_reviewed', 'dependencies_reviewed', 'acceptance_criteria_defined'], 'functional_validator_available' => false],
    'backend' => ['required_checks' => ['business_rules', 'api_integration', 'authorization', 'error_handling'], 'functional_validator_available' => false],
    'frontend' => ['required_checks' => ['real_backend_flow', 'loading_and_errors', 'mobile', 'accessibility'], 'functional_validator_available' => false],
    'integration' => ['required_checks' => ['authenticated_roundtrip', 'dependency_failure', 'retry_idempotency'], 'functional_validator_available' => false],
    'homologation' => ['required_checks' => ['same_commit', 'independent_qa', 'end_to_end', 'rollback', 'cockpit_evidence'], 'functional_validator_available' => false],
];
