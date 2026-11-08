FROM php:8.3-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /validation
COPY factory-core/app/Services/Governance/ /validation/factory-core/app/Services/Governance/
COPY tests/ /validation/tests/
COPY tools/verify-agent-handoff.php /validation/tools/verify-agent-handoff.php

USER 65534:65534
ENTRYPOINT ["/bin/sh", "-ec"]
CMD ["FACTORY_GOVERNANCE_TEST_DRIVER=sqlite php tests/test-mission-handoff-repository.php; php tests/test-agent-handoff.php; php tests/test-mission-handoff-repository.php; php tests/test-mission-handoff-mysql-concurrency.php; echo GOVERNANCE_VALIDATION_COMPLETE"]
