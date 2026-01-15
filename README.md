# ELK Demo - Elasticsearch Log Storage

A simple setup with Elasticsearch and a synthetic log generator using Podman.

## Components

1. **Elasticsearch**: Stores and indexes logs (v8.19.0)
2. **Log Generator**: Python app that generates random synthetic logs and sends them to Elasticsearch via REST API

## Features

- 🚀 Single-node Elasticsearch cluster with disk threshold disabled
- 📊 Synthetic log generation with realistic data patterns
- 🔄 Continuous log streaming (1-5 second intervals)
- 💾 Persistent volume storage for Elasticsearch data
- 🌐 Network isolation with dedicated bridge network

## Setup

### Prerequisites
- Podman (or Docker)
- Podman Compose (or Docker Compose)
- curl and jq (for testing)

### Start the Services

```bash
podman-compose up -d
```

This will start:
- Elasticsearch on ports 9200 (HTTP) and 9300 (Transport)
- Log Generator (automatically starts sending logs when Elasticsearch is ready)

### View Real-Time Logs

```bash
# View log generator output (see logs being sent)
podman logs -f log-generator

# View Elasticsearch logs
podman logs -f elasticsearch
```

### Check Elasticsearch Status

```bash
# Basic status
curl http://localhost:9200

# Cluster health (should be "yellow" for single-node)
curl 'http://localhost:9200/_cluster/health?pretty'
```

### Query Stored Logs

```bash
# Get count of stored logs
curl http://localhost:9200/app-logs/_count

# View sample logs (last 5)
curl 'http://localhost:9200/app-logs/_search?pretty&size=5'

# View specific fields only
curl 'http://localhost:9200/app-logs/_search?pretty&size=5' | jq '.hits.hits[]._source'

# Search logs by level (ERROR only)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "match": {
      "level": "ERROR"
    }
  },
  "size": 10
}
'

# Search by service
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
## Troubleshooting

### Elasticsearch shows "red" cluster status
This is usually due to disk space issues. The configuration disables disk threshold checks, but if you still face issues:
```bash
# Check cluster health
curl 'http://localhost:9200/_cluster/health?pretty'

# Check disk space
df -h
```

### Log generator not sending logs
```bash
# Check if containers are running
podman ps

# Restart log generator
podman-compose restart log-generator

# Check logs for errors
podman logs log-generator
```

### Cannot connect to Elasticsearch
Ensure Elasticsearch is healthy before querying:
```bash
# Wait for health check to pass
podman ps
# Look for "healthy" status on elasticsearch container
```

## Architecture

```
┌─────────────────────────────────────┐
│  Log Generator Container             │
│  (Python 3.11)                       │
│  - Generates synthetic logs          │
│  - Random intervals (1-5s)           │
│  - Multiple log levels & services    │
└──────────────┬──────────────────────┘
               │
               │ HTTP POST /app-logs/_doc
               │ Content-Type: application/json
               │
               ▼
┌─────────────────────────────────────┐
│  Elasticsearch Container             │
│  (v8.19.0)                           │
│  - Port: 9200 (HTTP API)             │
│  - Port: 9300 (Transport)            │
│  - Single-node cluster               │
│  - Security disabled (dev mode)      │
│  - Volume: elasticsearch-data        │
└─────────────────────────────────────┘
```

## Next Steps

This is a basic setup. To expand further, consider:
- Adding Kibana for log visualization
- Implementing Logstash for log processing pipelines
- Adding Filebeat for log collection from files
- Implementing index lifecycle management (ILM)
- Setting up security (authentication & encryption)
- Creating custom dashboards and visualizations
}
'
```

### Stop the Services

```bash
podman-compose down
```

### Clean Up (Remove Volumes and Data)

```bash
podman-compose down -v
```

## Log Structure

Each log entry contains:
- `timestamp`: ISO format timestamp
- `level`: INFO, WARNING, ERROR, or DEBUG
- `service`: Name of the service generating the log
- `message`: Log message
- `user_id`: User identifier
- `request_id`: Request identifier
- `duration_ms`: Duration in milliseconds
- `status_code`: HTTP status code

## Architecture

```
┌─────────────────┐
│  Log Generator  │
│   (Python App)  │
└────────┬────────┘
         │
         │ HTTP POST
         │ /app-logs/_doc
         │
         ▼
┌─────────────────┐
│  Elasticsearch  │
│   Port: 9200    │
└─────────────────┘
```
