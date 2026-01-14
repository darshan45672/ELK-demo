# Demo Script for 2-Hour ELK Stack Presentation

This script provides a detailed walkthrough for conducting the ELK Stack demo.

## Pre-Demo Checklist (Do this 15 min before)

- [ ] Start Docker Desktop
- [ ] Run `./scripts/setup-demo.sh`
- [ ] Verify all services are running: `./scripts/check-status.sh`
- [ ] Open Kibana in browser: http://localhost:5601
- [ ] Have terminal windows ready
- [ ] Have this script visible on second screen

---

## Section 1: Architecture & Use Case (0-10 min)

### What to Say

> "Today we're building a real-time log monitoring system for an e-commerce platform. Imagine you're responsible for a checkout service that processes thousands of transactions. How do you quickly identify when payments start failing? How do you know if response times are degrading?"

### Whiteboard/Slide

Draw this architecture:

```
[App Servers] → [Logstash] → [Elasticsearch] → [Kibana]
                  (Parse)      (Store)         (Visualize)
```

### Key Points to Emphasize

1. **Problem**: Logs scattered across servers, hard to search
2. **Solution**: Centralized, searchable, real-time analytics
3. **Value**: Faster troubleshooting, proactive alerts

### Demo Commands

```bash
# Show project structure
tree -L 2

# Show sample log file
cat logs/app.log
```

**Say**: "These are raw logs - timestamps, levels, key-value pairs. We'll make them searchable in seconds."

---

## Section 2: ELK Setup (10-30 min)

### Show Docker Compose

```bash
cat docker-compose.yml
```

**Explain**:
- 3 services: Elasticsearch, Logstash, Kibana
- Volume mounts for logs
- Health checks
- Network connectivity

### Start Stack (If not already running)

```bash
docker compose up -d
docker compose ps
```

### Check Elasticsearch

```bash
# Cluster health
curl http://localhost:9200

# Pretty formatted
curl http://localhost:9200 | jq '.'
```

**Point out**:
- Cluster name
- Version
- Tagline: "You Know, for Search"

### Show Kibana

Open: http://localhost:5601

**Walk through**:
- Home page
- Navigation menu
- Stack Management

---

## Section 3: Log Ingestion & Parsing (30-60 min)

### Show Raw Logs

```bash
cat logs/app.log | head -3
```

**Ask audience**: "What's wrong with this format?"

Expected answers:
- Unstructured
- Hard to filter by orderId
- Can't easily calculate average latency

### Explain Logstash Pipeline

```bash
cat logstash/pipeline/logstash.conf
```

**Walkthrough each section**:

1. **Input**: File plugin reading app.log
   ```
   "We're watching this file for changes"
   ```

2. **Filter - Grok**: Pattern matching
   ```
   "Grok extracts timestamp, level, service from the log line"
   ```

3. **Filter - KV**: Key-value extraction
   ```
   "KV plugin parses orderId=1001 into a field"
   ```

4. **Filter - Date**: Time parsing
   ```
   "Date filter ensures proper time-based searching"
   ```

5. **Filter - Mutate**: Data transformation
   ```
   "Convert latencyMs from string to integer for calculations"
   ```

6. **Output**: Send to Elasticsearch
   ```
   "Sends to ES index named checkout-logs-YYYY.MM.DD"
   ```

### Live Ingestion

```bash
# Add a new error log
echo "2026-01-14T12:30:15Z ERROR checkout orderId=2001 userId=42 latencyMs=1400 errorCode=TIMEOUT" >> logs/app.log

# Or use the generator
./scripts/generate-logs.sh
# Select option 1 for single log
```

### Show in Kibana

1. Go to **Discover**
2. If no index pattern exists:
   - Go to Stack Management → Index Patterns
   - Create: `checkout-logs-*`
   - Time field: `@timestamp`
3. Return to Discover
4. **Point out**:
   - Log appears in real-time (refresh if needed)
   - Structured fields on left
   - Parsed data (orderId, userId, latencyMs as numbers)

**Say**: "Notice how our unstructured log is now fully searchable with typed fields"

---

## Section 4: Kibana Search & Dashboards (60-90 min)

### Discover - Filtering

1. **Filter by error level**:
   - Click on `level: ERROR` in left panel
   - Click `+` to filter
   - Show how results update

2. **Filter by latency**:
   - Add filter manually: `latencyMs > 500`
   - Show results

3. **Combined filters**:
   ```
   level: ERROR AND latencyMs > 1000
   ```

4. **Show time picker**:
   - Last 15 minutes
   - Last 1 hour
   - Today
   - Custom range

### Saved Searches

Save the query as "High Latency Errors" for later use.

### Build Visualizations

#### 1. Error Trend Line Chart

1. Go to **Visualize Library** → **Create visualization**
2. Select **Line**
3. Index: `checkout-logs-*`
4. Configuration:
   - X-axis: Date Histogram (`@timestamp`)
   - Y-axis: Count
   - Break down: `level.keyword`
5. Save as "Error Trend"

**Say**: "This shows us error patterns over time - are errors increasing?"

#### 2. Top Error Codes

1. Create → **Bar (Vertical)**
2. X-axis: Terms on `errorCode.keyword`
3. Y-axis: Count
4. Filter: `level: ERROR`
5. Save as "Top Error Codes"

**Say**: "Which errors are most common? Is it payment gateway or database?"

#### 3. Average Latency

1. Create → **Metric**
2. Metric: Average of `latencyMs`
3. Save as "Avg Latency"

**Say**: "Single number shows overall performance health"

#### 4. Latency Histogram

1. Create → **Histogram**
2. X-axis: Histogram of `latencyMs` (interval: 200)
3. Y-axis: Count
4. Save as "Latency Distribution"

**Say**: "Are most requests fast? Do we have a long tail?"

### Create Dashboard

1. **Dashboard** → **Create new dashboard**
2. Click **Add from library**
3. Add all 4 visualizations
4. Arrange in grid layout
5. Add title: "Checkout Service Monitoring"
6. Save dashboard

**Demonstrate**:
- Click on bar in chart to filter entire dashboard
- Change time range
- Full screen mode
- Share/export options

---

## Section 5: Alerting & Troubleshooting (90-110 min)

### Create Alert Rule

1. Go to **Stack Management** → **Rules and Connectors**
2. **Create rule**
3. Configuration:
   - Name: "High Error Rate"
   - Check every: 1 minute
   - Rule type: Elasticsearch query
   - Index: `checkout-logs-*`
   - Size: 100
   - Time window: last 5 minutes
   - Query:
     ```json
     {
       "query": {
         "match": {
           "level": "ERROR"
         }
       }
     }
     ```
   - Condition: count ABOVE 2
4. Action: Server log
5. Message: "Alert: {{context.hits}} errors detected"
6. Save

### Trigger the Alert

```bash
# Generate error spike
./scripts/generate-logs.sh
# Select option 4 (simulate error spike)
```

This will add 5 ERROR logs quickly.

### Show Alert Firing

1. Go to **Rules and Connectors** → **Rules**
2. Show alert status changing to "Active"
3. Click on alert to see details
4. Show alert history

**Say**: "In production, this would trigger PagerDuty, Slack, or email notifications"

### Troubleshooting Scenario

**Present problem**: 
> "We just got alerted - 5 errors in 2 minutes. Let's investigate."

**Steps to demonstrate**:

1. **Go to Discover**
   ```
   level: ERROR AND @timestamp >= now-5m
   ```

2. **Check error distribution**:
   - Click on `errorCode.keyword` in left panel
   - See which error is most common

3. **Filter by specific error**:
   ```
   errorCode: PAYMENT_FAILED
   ```

4. **Check if it's user-specific**:
   - Look at `userId` field
   - Add filter: `userId: 42`

5. **Check latency correlation**:
   - Add column: `latencyMs`
   - Sort by latencyMs descending
   - **Insight**: "All payment failures have latency > 1200ms"

6. **Narrow time window**:
   - Set time to "Last 10 minutes"
   - **Question**: "When did this start?"

7. **Check dashboard view**:
   - Go back to dashboard
   - Show spike in error trend
   - Show latency increase

**Conclude**: 
> "Root cause: Payment gateway timeout. Latency spiked, then payments started failing. We should check the payment service health."

### Show MTTR Benefits

**Compare**:

**Without ELK**:
- SSH to 5 app servers
- grep through logs
- Correlate timestamps
- Manual data aggregation
- Time: 20-30 minutes

**With ELK**:
- Open Kibana
- Filter and search
- Visualize patterns
- Identify root cause
- Time: 2-3 minutes

---

## Section 6: Wrap-Up & Extensions (110-120 min)

### Recap Value Delivered

1. ✅ **Centralized Logging**: All logs in one place
2. ✅ **Real-Time Insights**: Instant visibility
3. ✅ **Structured Data**: Searchable fields
4. ✅ **Visualizations**: Understand patterns quickly
5. ✅ **Alerting**: Proactive problem detection
6. ✅ **Faster MTTR**: From 30 min to 3 min

### Optional Enhancements (Talk Only)

#### 1. Filebeat Instead of Logstash
- Lighter weight
- Built-in modules
- Better for large scale

#### 2. APM Integration
- Trace requests across services
- Code-level performance insights
- Distributed tracing

#### 3. Metrics + Logs + Traces
- Elastic Observability
- Unified view
- Correlate metrics with logs

#### 4. ML Anomaly Detection
- Automatic pattern detection
- Alert on unusual behavior
- No threshold configuration needed

#### 5. Security (SIEM)
- Threat detection
- Security analytics
- Compliance reporting

### Demo Environment Commands

```bash
# Generate more logs for testing
./scripts/generate-logs.sh

# Check system status
./scripts/check-status.sh

# View live logs
docker compose logs -f logstash

# Stop everything
docker compose down

# Start fresh (removes data)
docker compose down -v
docker compose up -d
```

### Resources to Share

- Elastic documentation: https://www.elastic.co/guide
- This demo repository: [Your GitHub URL]
- ELK getting started: https://www.elastic.co/elk-stack
- Kibana query language: https://www.elastic.co/guide/en/kibana/current/kuery-query.html

---

## Q&A Tips

### Common Questions & Answers

**Q: How much data can Elasticsearch handle?**
A: Petabytes. It's horizontally scalable - add more nodes as needed.

**Q: What's the latency for logs to appear?**
A: Typically 1-5 seconds depending on configuration.

**Q: Can we parse different log formats?**
A: Yes, Logstash has 200+ plugins. Grok patterns handle most formats.

**Q: How do we handle high volume?**
A: Use Filebeat for shipping, add Logstash nodes, scale ES cluster.

**Q: What about log retention?**
A: Configure Index Lifecycle Management (ILM) - hot/warm/cold architecture.

**Q: Can we integrate with existing tools?**
A: Yes - APIs, webhooks, plugins for Datadog, Splunk, etc.

**Q: What's the cost?**
A: Open source is free. Elastic Cloud is managed (paid). Self-hosted = infrastructure cost.

---

## Backup Demos (If Time Permits)

### 1. Show Log Pattern Analysis

```bash
# Generate diverse logs
./scripts/generate-logs.sh
# Select option 2, generate 50 logs
```

Then show aggregations in Kibana.

### 2. Live Continuous Logging

```bash
./scripts/generate-logs.sh
# Select option 3 (continuous)
```

Watch logs stream into Kibana in real-time.

### 3. Export Dashboard

Show how to:
- Export dashboard as JSON
- Share with team
- Import in another environment

---

## Troubleshooting During Demo

### If Elasticsearch isn't responding:
```bash
docker compose restart elasticsearch
# Wait 30 seconds
curl http://localhost:9200
```

### If logs aren't appearing:
```bash
# Check Logstash logs
docker compose logs logstash

# Verify file mount
docker compose exec logstash ls -la /logs/
```

### If Kibana is slow:
```bash
# Restart Kibana
docker compose restart kibana
```

### Reset everything:
```bash
docker compose down -v
docker compose up -d
./scripts/setup-demo.sh
```

---

## Timing Checkpoints

- **10 min**: Finished architecture explanation
- **30 min**: Stack is running, services verified
- **60 min**: Logs are parsed and appearing in Kibana
- **90 min**: Dashboard created with multiple visualizations
- **110 min**: Alert created and triggered
- **120 min**: Q&A wrapping up

**Stay on track!** If running behind, skip the advanced visualizations and focus on core value.

---

Good luck with your demo! 🚀
