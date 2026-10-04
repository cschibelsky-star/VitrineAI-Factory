FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    BRIDGE_HOST=0.0.0.0 \
    BRIDGE_PORT=8094

WORKDIR /app

COPY factory-agent-bridge/requirements.txt ./requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

COPY factory-agent-bridge/src ./src
COPY factory-agent-bridge/policy ./policy

EXPOSE 8094

CMD ["python", "src/app.py"]
