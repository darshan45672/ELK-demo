# ELK Stack Demo - Quick Reference

## Access URLs

- **Elasticsearch:** http://localhost:9200
- **Kibana:** http://localhost:5601
- **Logstash API:** http://localhost:9600

## Quick Commands

### Docker Management
```bash
# Start ELK stack
docker compose up -d

# Stop ELK stack
docker compose down

# View logs
docker compose logs -f

# Restart service
docker compose restart <service-name>

# Check status
docker compose ps

# Fresh start (removes data)
docker compose down -v && docker compose up -d
```

### Scripts
```bash
# Complete setup
./scripts/setup-demo.sh

# Generate logs
./scripts/generate-logs.sh

# Check system health
./scripts/check-status.sh
```

### Elasticsearch Commands
```bash
# Cluster health
curl http://localhost:9200/_cluster/health?pretty

# List indices
curl http://localhost:9200/_cat/indices?v

# Document count
curl http://localhost:9200/checkout-logs-*/_count

# Sample query
curl -X GET "http://localhost:9200/checkout-logs-*/_search?pretty" -H 'Content-Type: application/json' -d'
{
  "query": {
    "match": {
      "level": "ERROR"
    }
  },
  "size": 5
}'
```

### Log Generation
```bash
# Single error log
echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ERROR checkout orderId=1001 userId=42 latencyMs=1200 errorCode=PAYMENT_FAILED" >> logs/app.log

# Multiple logs
for i in {1..10}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) INFO checkout orderId=$((1000+i)) userId=42 latencyMs=250" >> logs/app.log
  sleep 1
done

# Error spike
for i in {1..5}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ERROR checkout orderId=$((2000+i)) userId=88 latencyMs=1500 errorCode=TIMEOUT" >> logs/app.log
  sleep 0.5
done
```

## Kibana Query Language (KQL)

### Basic Queries
```
# All errors
level: ERROR

# High latency
latencyMs > 500

# Specific error code
errorCode: PAYMENT_FAILED

# User-specific
userId: 42

# Time-based
@timestamp >= now-5m
```

### Combined Queries
```
# High latency errors
level: ERROR AND latencyMs > 1000

# Recent payment failures
errorCode: PAYMENT_FAILED AND @timestamp >= now-15m

# User errors excluding timeouts
userId: 42 AND level: ERROR AND NOT errorCode: TIMEOUT

# Multiple conditions
(level: ERROR OR level: WARN) AND latencyMs > 800
```

### Wildcards and Ranges
```
# Wildcard
errorCode: PAYMENT*

# Range
latencyMs >= 500 AND latencyMs <= 1000

# Exists
errorCode: *
```

## Demo Sections Timeline

| Time | Section | Key Actions |
|------|---------|-------------|
| 0-10 min | Architecture | Present use case, show architecture |
| 10-30 min | Setup | Start stack, verify services |
| 30-60 min | Log Parsing | Show raw logs, explain pipeline, ingest data |
| 60-90 min | Visualizations | Create charts, build dashboard |
| 90-110 min | Alerting | Create rules, trigger alerts, troubleshoot |
| 110-120 min | Q&A | Recap, discuss enhancements |

## Key Demo Points

### Show Value
- ✅ Centralized logging (all logs in one place)
- ✅ Real-time visibility (appears in seconds)
- ✅ Structured data (searchable fields)
- ✅ Visual insights (patterns at a glance)
- ✅ Proactive alerts (detect issues early)
- ✅ Faster MTTR (30 min → 3 min)

### Highlight Features
1. **Grok parsing** - Extract fields from unstructured logs
2. **KV extraction** - Parse key=value pairs automatically
3. **Real-time ingestion** - See logs appear instantly
4. **Interactive filters** - Click to drill down
5. **Alert rules** - Automated issue detection
6. **Dashboard** - Monitor everything at once

## Visualizations to Create

1. **Error Trend** (Line chart)
   - X: @timestamp (Date Histogram)
   - Y: Count
   - Split: level.keyword

2. **Top Error Codes** (Bar chart)
   - X: errorCode.keyword (Terms)
   - Y: Count
   - Filter: level:ERROR

3. **Average Latency** (Metric)
   - Metric: Avg(latencyMs)

4. **Latency Distribution** (Histogram)
   - X: latencyMs (Histogram, interval: 200)
   - Y: Count

## Troubleshooting Scenario

**Problem:** High error rate alert triggered

**Investigation Steps:**
1. Go to Discover → Filter: `level: ERROR AND @timestamp >= now-5m`
2. Check errorCode distribution
3. Filter specific error: `errorCode: PAYMENT_FAILED`
4. Check latency: Add latencyMs column, sort descending
5. Identify pattern: All failures have latency > 1200ms
6. Conclusion: Payment gateway timeout

**Resolution:**
- Contact payment service team
- Implement retry logic
- Adjust timeout threshold

## Alert Rules

### High Error Rate
```
Name: High Error Rate
Query: level: ERROR
Condition: Count > 2
Time Window: 5 minutes
Action: Log / Email / Slack
```

### High Latency
```
Name: High Latency Alert
Query: latencyMs >= 1000
Condition: Count > 3
Time Window: 5 minutes
```

### Payment Failures
```
Name: Payment Failure Spike
Query: errorCode: PAYMENT_FAILED
Condition: Count > 2
Time Window: 5 minutes
```

## Common Questions & Answers

**Q: How quickly do logs appear?**
A: Typically 1-5 seconds

**Q: How much data can it handle?**
A: Petabytes - scales horizontally

**Q: What about different log formats?**
A: Logstash has 200+ input plugins

**Q: Production readiness?**
A: Add authentication, TLS, clustering

**Q: Cost?**
A: Open source (free) or managed cloud

## File Locations

```
docker-compose.yml          # Stack configuration
logstash/pipeline/logstash.conf   # Parsing rules
logs/app.log                # Application logs
scripts/                    # Automation scripts
kibana/DEMO_SCRIPT.md      # Detailed walkthrough
kibana/DASHBOARD_GUIDE.md  # Visualization guide
```

## Pro Tips

1. **Prepare ahead** - Start stack 15 min before demo
2. **Clear data** - Fresh start for clean demo
3. **Pre-generate logs** - Have some data ready
4. **Practice filtering** - Know your KQL queries
5. **Time management** - Watch the clock
6. **Tell a story** - Business value over features
7. **Live demo** - Show real-time ingestion
8. **Audience engagement** - Ask questions
9. **Have backups** - Screenshots if live demo fails
10. **End strong** - Recap value delivered

## Next Steps After Demo

1. **Filebeat** - Production log shipping
2. **ILM** - Automatic data retention
3. **APM** - Application performance monitoring
4. **ML** - Anomaly detection
5. **Security** - Authentication & RBAC
6. **Clustering** - High availability setup
7. **Monitoring** - Stack monitoring
8. **Alerting** - Email/Slack integrations

## Resources

- [Full Demo Script](kibana/DEMO_SCRIPT.md)
- [Dashboard Guide](kibana/DASHBOARD_GUIDE.md)
- [Troubleshooting](TROUBLESHOOTING.md)
- [Elastic Docs](https://www.elastic.co/guide)

---

**Quick Start:** `./scripts/setup-demo.sh` → Open http://localhost:5601 → Create index pattern → Start demo!
