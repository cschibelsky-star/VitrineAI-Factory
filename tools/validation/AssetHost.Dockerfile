FROM python:3.12-slim
RUN apt-get update && apt-get install -y --no-install-recommends git && rm -rf /var/lib/apt/lists/*
RUN python -m pip install --no-cache-dir 'fastmcp==2.10.6' 'mcp==1.12.4' 'pydantic==2.11.7' 'pydantic-settings==2.10.1' 'starlette==0.47.2' 'sse-starlette==2.4.1' 'uvicorn==0.35.0'
WORKDIR /app
COPY tools /app/tools
ENV PYTHONDONTWRITEBYTECODE=1 PYTHONPATH=/app/tools
# Empty Docker named volumes inherit this directory's ownership and permissions.
# PrivateRuntime rejects any state path not owned by the unprivileged process.
RUN install -d -m 0700 -o 65534 -g 65534 /var/lib/asset-intake
USER 65534:65534
CMD ["uvicorn", "asset_host:make_app", "--factory", "--host", "0.0.0.0", "--port", "8080"]
