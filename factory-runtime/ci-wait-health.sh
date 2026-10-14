#!/usr/bin/env bash
set -euo pipefail
for i in {1..60}; do
  status="$(docker inspect --format='{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' vitrine_factory_hml 2>/dev/null || true)"
  if [ "$status" = "healthy" ]; then
    exit 0
  fi
  if [ "$status" = "unhealthy" ] || [ "$status" = "exited" ]; then
    docker compose -f docker-compose.factory.yml logs --no-color factory
    exit 1
  fi
  sleep 5
done
docker compose -f docker-compose.factory.yml logs --no-color factory
exit 1

