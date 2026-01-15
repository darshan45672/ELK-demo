# ELK Demo - Elasticsearch with Filebeat Log Shipping

A production-ready logging pipeline using Elasticsearch and Filebeat for log shipping, with synthetic log generation using Podman.

## Architecture

```
Log Generator (Python) → File System (/var/log/app/*.log) → Filebeat → Elasticsearch ← Elasticvue (Web UI)
```

This setup demonstrates proper log shipping patterns used in production environments, where applications write logs to files and a log shipper (Filebeat) tails those files and sends them to Elasticsearch.

## Components

1. **Elasticsearch**: Stores and indexes logs (v8.19.0)
2. **Log Generator**: Python app that generates random synthetic JSON logs and writes them to files
3. **Filebeat**: Log shipper that tails log files and sends them to Elasticsearch (v8.19.0)
4. **Elasticvue**: Browser-based UI for querying and viewing Elasticsearch data

## Features

- 🚀 Single-node Elasticsearch cluster with disk threshold disabled
- 📊 Synthetic JSON log generation with realistic data patterns
- 🔄 Continuous log streaming (1-5 second intervals)
- 📁 File-based log shipping via Filebeat (production pattern)
- 💾 Persistent volume storage for Elasticsearch data and Filebeat registry
- 🌐 Network isolation with dedicated bridge network
- 🖥️ Web UI (Elasticvue) for easy data exploration

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
- Log Generator (writes JSON logs to /var/log/app/application.log)
- Filebeat (tails log files and ships to Elasticsearch)
- Elasticvue on port 8080 (Web UI)

### Access Elasticvue Web UI

Open your browser and go to:
```
http://localhost:8080
```

**First-time Setup:**
1. Click "Add elasticsearch cluster"
2. Enter cluster details:
   - **Name**: Local Elasticsearch (or any name you prefer)
   - **Uri**: `http://localhost:9200` (use localhost, not elasticsearch)
3. Click "Test connection" then "Connect"

**Note**: Use `http://localhost:9200` because you're connecting from your browser (host machine), not from inside the container network.

**Features Available:**
- 📊 Browse indices and documents
- 🔍 Search with visual query builder
- 📝 View and edit documents
- 📈 Cluster health monitoring
- 🎯 Index management

### View Real-Time Logs

```bash
# View log generator output (see logs being written)
podman logs -f log-generator

# View Filebeat logs (see log shipping status)
podman logs -f filebeat

# View Elasticsearch logs
podman logs -f elasticsearch

# View actual log file being tailed by Filebeat
podman exec log-generator tail -f /var/log/app/application.log
```

### Check Elasticsearch Status

```bash
# Basic status
curl http://localhost:9200

# Cluster health (should be "yellow" for single-node)
curl 'http://localhost:9200/_cluster/health?pretty'

# List all indices
curl 'http://localhost:9200/_cat/indices?v'
```

### Query Stored Logs

**Note**: Logs are indexed in `filebeat-app-logs-*` indices with JSON content in the `message` field.

#### Basic Queries

```bash
# Get count of stored logs from Filebeat index
curl 'http://localhost:9200/filebeat-app-logs-*/_count'

# View sample logs (last 5)
curl 'http://localhost:9200/filebeat-app-logs-*/_search?pretty&size=5&sort=@timestamp:desc'

# View specific fields only
curl 'http://localhost:9200/filebeat-app-logs-*/_search?pretty&size=5&sort=@timestamp:desc' | jq '.hits.hits[]._source | {timestamp: ."@timestamp", message: .message, host: .host.name}'

# Get all logs (match_all)
curl -X GET 'http://localhost:9200/filebeat-app-logs-*/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "match_all": {}
  },
  "size": 10,
  "sort": [{"@timestamp": "desc"}]
}
'
```

#### Search by Field (Match Query)

```bash
# Search logs containing "ERROR" in message
curl -X GET 'http://localhost:9200/filebeat-app-logs-*/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "match": {
      "message": "ERROR"
    }
  },
  "size": 10,
  "sort": [{"@timestamp": "desc"}]
}
'

# Search by log source
curl -X GET 'http://localhost:9200/filebeat-app-logs-*/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "match": {
      "log_source": "filebeat"
    }
  },
  "size": 10
}
'

# Search by message text
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "match": {
      "message": "timeout"
    }
  }
}
'
```

#### Exact Match (Term Query)

```bash
# Exact match on service (use .keyword for text fields)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "term": {
      "service.keyword": "auth-service"
    }
  }
}
'

# Match specific status code
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "term": {
      "status_code": 500
    }
  }
}
'
```

#### Range Queries

```bash
# Get logs from last 5 minutes
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "range": {
      "timestamp": {
        "gte": "now-5m",
        "lte": "now"
      }
    }
  }
}
'

# Find slow requests (duration > 2000ms)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "range": {
      "duration_ms": {
        "gte": 2000
      }
    }
  }
}
'

# Find server errors (status code 500-599)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "range": {
      "status_code": {
        "gte": 500,
        "lt": 600
      }
    }
  }
}
'
```

#### Complex Queries (Bool Query)

```bash
# Combine multiple conditions (must = AND)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "bool": {
      "must": [
        { "match": { "level": "ERROR" } },
        { "match": { "service": "payment-service" } }
      ],
      "filter": [
        { "range": { "timestamp": { "gte": "now-1h" } } }
      ]
    }
  }
}
'

# Exclude certain conditions (must_not = NOT)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "bool": {
      "must": [
        { "match": { "level": "ERROR" } }
      ],
      "must_not": [
        { "term": { "status_code": 404 } }
      ]
    }
  }
}
'

# Any of conditions (should = OR)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": {
    "bool": {
      "should": [
        { "match": { "level": "ERROR" } },
        { "match": { "level": "WARNING" } }
      ],
      "minimum_should_match": 1
    }
  }
}
'
```

#### Sorting and Pagination

```bash
# Sort by timestamp (newest first)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": { "match_all": {} },
  "sort": [
    { "timestamp": { "order": "desc" } }
  ],
  "size": 10,
  "from": 0
}
'

# Top 5 slowest requests
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": { "match_all": {} },
  "sort": [
    { "duration_ms": { "order": "desc" } }
  ],
  "size": 5
}
'

# Pagination (page 2, 10 results per page)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "query": { "match_all": {} },
  "size": 10,
  "from": 10
}
'
```

#### Aggregations (Analytics)

```bash
# Count logs by level
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "logs_by_level": {
      "terms": {
        "field": "level.keyword",
        "size": 10
      }
    }
  }
}
'

# Count logs by service
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "logs_by_service": {
      "terms": {
        "field": "service.keyword"
      }
    }
  }
}
'

# Average response time
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "avg_duration": {
      "avg": {
        "field": "duration_ms"
      }
    }
  }
}
'

# Statistics on duration
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "duration_stats": {
      "stats": {
        "field": "duration_ms"
      }
    }
  }
}
'

# Count unique users
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "unique_users": {
      "cardinality": {
        "field": "user_id.keyword"
      }
    }
  }
}
'

# Logs over time (histogram)
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "size": 0,
  "aggs": {
    "logs_over_time": {
      "date_histogram": {
        "field": "timestamp",
        "calendar_interval": "minute"
      }
    }
  }
}
'
```

#### Select Specific Fields

```bash
# Return only specific fields
curl -X GET 'http://localhost:9200/app-logs/_search?pretty' -H 'Content-Type: application/json' -d'
{
  "_source": ["timestamp", "level", "service", "message"],
  "query": {
    "match": {
      "level": "ERROR"
    }
  }
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

## Troubleshooting

### Elasticsearch shows "yellow" cluster status
This is **normal and expected** for a single-node cluster!

**Why Yellow?**
- 🟡 Yellow means all primary shards are active, but replica shards cannot be assigned
- Single-node clusters can't place replicas (they must be on different nodes)
- Your data is safe and accessible - this is the correct state for development

**Cluster Status Colors:**
- 🟢 **Green**: All primary and replica shards allocated (requires multiple nodes)
- 🟡 **Yellow**: All primaries allocated, some replicas unassigned (normal for single-node)
- 🔴 **Red**: Some primary shards missing (data loss risk)

```bash
# Check cluster health
curl 'http://localhost:9200/_cluster/health?pretty'
```

### Elasticsearch shows "red" cluster status
This indicates missing primary shards. Usually due to disk space issues:
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
└──────────────┬──────────────────────┘
               │
               │ Connected via elk-network
               │
               ▼
┌─────────────────────────────────────┐
│  Elasticvue Container                │
│  (Web UI)                            │
│  - Port: 8080                        │
│  - Browse & query data               │
│  - Visual interface                  │
└─────────────────────────────────────┘
         Access via browser:
         http://localhost:8080
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
