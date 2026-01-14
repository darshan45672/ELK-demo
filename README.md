# ELK Stack Demo - 2 Hour Hands-on PoC

A comprehensive demonstration of the **ELK Stack** (Elasticsearch, Logstash, Kibana) for centralized log monitoring, real-time analytics, and alerting using an e-commerce checkout service scenario.

![ELK Stack](https://img.shields.io/badge/ELK-Stack-005571?style=for-the-badge&logo=elastic&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Status](https://img.shields.io/badge/Status-Demo%20Ready-success?style=for-the-badge)

## 📋 Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Demo Storyline](#demo-storyline)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Project Structure](#project-structure)
- [Demo Sections](#demo-sections)
- [Usage Guide](#usage-guide)
- [Troubleshooting](#troubleshooting)
- [Advanced Features](#advanced-features)
- [Resources](#resources)

## 🎯 Overview

This project demonstrates a production-like ELK Stack implementation for monitoring an e-commerce checkout service. It showcases:

- ✅ **Centralized Logging** - All application logs in one searchable location
- ✅ **Real-Time Analytics** - Instant visibility into system health and errors
- ✅ **Log Parsing & Enrichment** - Transform unstructured logs into structured data
- ✅ **Interactive Dashboards** - Visualize trends, errors, and performance metrics
- ✅ **Automated Alerting** - Detect and respond to issues proactively
- ✅ **Faster Troubleshooting** - Reduce MTTR from 30 minutes to 3 minutes

### Use Case: E-Commerce Checkout Service

Monitor a critical payment processing service that handles:
- Transaction processing
- Payment gateway integration
- Order management
- Performance SLAs

## 🏗️ Architecture

```
┌─────────────────────┐
│  Application Logs   │
│  (checkout service) │
└──────────┬──────────┘
           │ File-based logs
           ▼
┌─────────────────────┐
│     Logstash        │  ← Parsing & Enrichment
│  - Grok parsing     │     • Extract fields
│  - KV extraction    │     • Type conversion
│  - Date parsing     │     • Data enrichment
└──────────┬──────────┘
           │ Structured data
           ▼
┌─────────────────────┐
│   Elasticsearch     │  ← Storage & Search
│  - Indexing         │     • Full-text search
│  - Aggregations     │     • Analytics
│  - Time-series data │     • Distributed storage
└──────────┬──────────┘
           │ REST API
           ▼
┌─────────────────────┐
│      Kibana         │  ← Visualization & Analysis
│  - Discover         │     • Search interface
│  - Visualizations   │     • Charts & graphs
│  - Dashboards       │     • Real-time updates
│  - Alerting         │     • Rule management
└─────────────────────┘
```

## 📖 Demo Storyline

> **"From raw logs to actionable insights in 2 hours"**

**Narrative Flow:**
1. Start with scattered, unstructured logs
2. Centralize and parse them automatically
3. Extract meaningful patterns and metrics
4. Visualize system health in real-time
5. Detect and alert on critical issues
6. Demonstrate rapid troubleshooting

**Business Value:**
- Faster root cause analysis
- Proactive issue detection
- Reduced downtime
- Better operational visibility

## ✅ Prerequisites

- **Docker Desktop** (or Docker Engine + Docker Compose)
- **Minimum 4GB RAM** allocated to Docker
- **8GB disk space** for images and data
- **macOS, Linux, or Windows** with WSL2
- **Terminal** with bash/zsh
- **Web Browser** (Chrome, Firefox, Safari)
- **Optional:** `jq` for JSON formatting

### Install Docker

```bash
# macOS (using Homebrew)
brew install --cask docker

# Linux (Ubuntu/Debian)
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Start Docker
sudo systemctl start docker
```

## 🚀 Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/darshan45672/ELK-demo.git
cd ELK-demo
```

### 2. Make Scripts Executable

```bash
chmod +x scripts/*.sh
```

### 3. Start the ELK Stack

```bash
./scripts/setup-demo.sh
```

This script will:
- Start Elasticsearch, Logstash, and Kibana containers
- Wait for services to be healthy
- Verify connectivity
- Display access URLs

**Expected output:**
```
✓ Elasticsearch is running on port 9200
✓ Kibana is running on port 5601
✓ Logstash is running on port 9600

Access URLs:
  • Elasticsearch: http://localhost:9200
  • Kibana:        http://localhost:5601
  • Logstash:      http://localhost:9600
```

### 4. Access Kibana

Open your browser and navigate to:
```
http://localhost:5601
```

### 5. Create Index Pattern (First Time Only)

1. In Kibana, go to **Stack Management** → **Index Patterns**
2. Click **Create index pattern**
3. Enter: `checkout-logs-*`
4. Select time field: `@timestamp`
5. Click **Create index pattern**

### 6. Generate Sample Logs

```bash
./scripts/generate-logs.sh
```

Choose from:
1. Single log entry
2. Multiple logs (specify count)
3. Continuous log stream
4. Error spike simulation
5. High latency scenario

### 7. View Logs in Kibana

1. Go to **Discover** in Kibana
2. Select index pattern: `checkout-logs-*`
3. View parsed, structured logs in real-time

## 📁 Project Structure

```
ELK-demo/
├── docker-compose.yml           # ELK Stack services definition
├── README.md                     # This file
│
├── logstash/
│   └── pipeline/
│       └── logstash.conf        # Log parsing pipeline
│
├── logs/
│   └── app.log                  # Application logs (mounted to Logstash)
│
├── scripts/
│   ├── setup-demo.sh            # Automated ELK setup script
│   ├── generate-logs.sh         # Log generation utility
│   └── check-status.sh          # Health check script
│
└── kibana/
    ├── DASHBOARD_GUIDE.md       # Dashboard creation instructions
    └── DEMO_SCRIPT.md           # Complete demo walkthrough
```

## 🎬 Demo Sections

### Section 1: Architecture & Use Case (0-10 min)
- Explain the business scenario
- Show the architecture diagram
- Discuss pain points and solutions

### Section 2: ELK Setup (10-30 min)
- Start Docker containers
- Verify services
- Explain each component's role

### Section 3: Log Ingestion & Parsing (30-60 min)
- Show raw unstructured logs
- Explain Logstash pipeline configuration
- Demonstrate real-time log parsing
- Show structured data in Kibana

### Section 4: Kibana Search & Dashboards (60-90 min)
- Use Discover for log searching
- Create visualizations:
  - Error trend line chart
  - Top error codes bar chart
  - Average latency metric
  - Latency distribution histogram
- Build a complete dashboard

### Section 5: Alerting & Troubleshooting (90-110 min)
- Create alert rules
- Trigger alerts with error spikes
- Demonstrate root cause analysis
- Show MTTR reduction

### Section 6: Q&A & Extensions (110-120 min)
- Recap value delivered
- Discuss scaling and enhancements
- Answer audience questions

**📄 [Full Demo Script](kibana/DEMO_SCRIPT.md)**

## 📘 Usage Guide

### Managing the Stack

```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# View logs
docker compose logs -f

# Restart specific service
docker compose restart elasticsearch

# Remove all data and start fresh
docker compose down -v
docker compose up -d
```

### Checking System Status

```bash
# Run health check
./scripts/check-status.sh

# Check Elasticsearch
curl http://localhost:9200

# List indices
curl http://localhost:9200/_cat/indices?v

# Check cluster health
curl http://localhost:9200/_cluster/health?pretty
```

### Generating Logs

```bash
# Interactive menu
./scripts/generate-logs.sh

# Add single log programmatically
echo "2026-01-14T12:00:00Z ERROR checkout orderId=1001 userId=42 latencyMs=1200 errorCode=PAYMENT_FAILED" >> logs/app.log

# Simulate error spike
for i in {1..5}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ERROR checkout orderId=$((2000+i)) userId=88 latencyMs=$((1200+RANDOM%800)) errorCode=TIMEOUT" >> logs/app.log
  sleep 0.5
done
```

### Kibana Operations

**Useful URLs:**
- Discover: `http://localhost:5601/app/discover`
- Dashboards: `http://localhost:5601/app/dashboards`
- Visualizations: `http://localhost:5601/app/visualize`
- Stack Management: `http://localhost:5601/app/management`

**Common KQL Queries:**
```
# All errors
level: ERROR

# High latency requests
latencyMs > 500

# Payment failures
errorCode: PAYMENT_FAILED

# User-specific errors
userId: 42 AND level: ERROR

# Recent timeouts
errorCode: TIMEOUT AND @timestamp >= now-5m
```

**📄 [Dashboard Creation Guide](kibana/DASHBOARD_GUIDE.md)**

## 🔧 Troubleshooting

### Services Not Starting

```bash
# Check Docker is running
docker ps

# Check logs for errors
docker compose logs elasticsearch
docker compose logs logstash
docker compose logs kibana

# Restart services
docker compose restart
```

### Elasticsearch Not Responding

```bash
# Check if port is available
lsof -i :9200

# Increase memory limit in docker-compose.yml
# ES_JAVA_OPTS=-Xms1g -Xmx1g

# Clear data and restart
docker compose down -v
docker compose up -d
```

### Logs Not Appearing in Kibana

```bash
# Verify Logstash is reading the file
docker compose logs logstash | grep "app.log"

# Check file permissions
ls -la logs/app.log

# Verify index exists
curl http://localhost:9200/_cat/indices?v

# Force index refresh
curl -X POST "http://localhost:9200/checkout-logs-*/_refresh"
```

### Kibana Loading Slowly

```bash
# Increase memory
# In docker-compose.yml, add to kibana:
# environment:
#   - NODE_OPTIONS="--max-old-space-size=2048"

# Clear browser cache
# Restart Kibana
docker compose restart kibana
```

### Port Already in Use

```bash
# Find process using port 9200
lsof -i :9200

# Kill process (if safe)
kill -9 <PID>

# Or change port in docker-compose.yml
```

## 🚀 Advanced Features

### 1. Filebeat Integration

Replace Logstash file input with Filebeat for better performance:

```yaml
# filebeat.yml
filebeat.inputs:
- type: log
  paths:
    - /logs/app.log
output.logstash:
  hosts: ["logstash:5044"]
```

### 2. Elasticsearch Index Lifecycle Management

Automatically manage index retention:

```json
PUT _ilm/policy/checkout-logs-policy
{
  "policy": {
    "phases": {
      "hot": {
        "actions": {
          "rollover": {
            "max_age": "7d",
            "max_size": "50gb"
          }
        }
      },
      "delete": {
        "min_age": "30d",
        "actions": {
          "delete": {}
        }
      }
    }
  }
}
```

### 3. Machine Learning Anomaly Detection

Enable ML to automatically detect unusual patterns:
- Latency spikes
- Error rate anomalies
- Traffic pattern changes

### 4. APM (Application Performance Monitoring)

Add Elastic APM for distributed tracing:
- Request flow across services
- Code-level performance insights
- Database query analysis

### 5. Security & SIEM

Enable security features:
- Authentication and RBAC
- Audit logging
- Threat detection
- Compliance dashboards

## 📚 Resources

### Official Documentation
- [Elasticsearch Guide](https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html)
- [Logstash Documentation](https://www.elastic.co/guide/en/logstash/current/index.html)
- [Kibana Guide](https://www.elastic.co/guide/en/kibana/current/index.html)
- [Elastic Stack Overview](https://www.elastic.co/elastic-stack)

### Learning Resources
- [Elastic Training](https://www.elastic.co/training/)
- [ELK Stack Tutorial](https://www.elastic.co/what-is/elk-stack)
- [Kibana Query Language (KQL)](https://www.elastic.co/guide/en/kibana/current/kuery-query.html)
- [Grok Patterns](https://github.com/elastic/logstash/blob/main/patterns/grok-patterns)

### Community
- [Elastic Discuss Forums](https://discuss.elastic.co/)
- [Stack Overflow - Elasticsearch](https://stackoverflow.com/questions/tagged/elasticsearch)
- [Elastic Community Slack](https://communityinviter.com/apps/elasticstack/elastic-community)

### Sample Data & Demos
- [Elastic Examples](https://github.com/elastic/examples)
- [Kibana Sample Data](https://www.elastic.co/guide/en/kibana/current/sample-data.html)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👤 Author

**Darshan Bhandary**
- GitHub: [@darshan45672](https://github.com/darshan45672)

## ⭐ Acknowledgments

- Elastic team for the amazing ELK Stack
- Docker for containerization
- Community contributors

---

**Happy Logging! 📊🔍**

For detailed demo instructions, see [DEMO_SCRIPT.md](kibana/DEMO_SCRIPT.md)