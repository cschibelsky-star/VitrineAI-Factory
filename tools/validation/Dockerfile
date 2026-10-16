FROM python:3.12-slim
RUN apt-get update && apt-get install -y --no-install-recommends git && rm -rf /var/lib/apt/lists/*
RUN python -m pip install --no-cache-dir 'fastmcp==2.10.6' 'mcp==1.12.4' 'pydantic==2.11.7' 'pydantic-settings==2.10.1' 'starlette==0.47.2' 'sse-starlette==2.4.1'
WORKDIR /validation
COPY tools /validation/tools
ENV PYTHONDONTWRITEBYTECODE=1
USER 65534:65534
CMD ["sh", "-c", "python -m unittest discover -s tools/tests -p 'test_*.py' -v && python -m unittest discover -s tools/sdk_tests -p 'test_*.py' -v"]
