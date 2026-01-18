# Laravel + ELK-Kafka Stack - Quick Start Guide

## 🚀 Overview

This setup uses a **Laravel application** as a live log generator, sending structured JSON logs through the complete ELK-Kafka pipeline:

```
Laravel App → Filebeat → Kafka → Logstash → Elasticsearch → Kibana
```

## 📋 Prerequisites

- Podman & Podman Compose installed
- PHP 8.3+ and Composer (optional, for local development)
- 6GB+ RAM available
- Ports available: 8000, 9200, 5601, 9092, 2181, 5044, 8080

## 🛠️ Setup Instructions

### 1. Setup Laravel Application

```bash
cd Application-Logger
./setup.sh
cd ..
```

This will:
- Install Composer dependencies
- Create/update `.env` file
- Set logging channel to `elk_json`
- Generate application key
- Create storage directories
- Set proper permissions

### 2. Start All Services

```bash
podman-compose up -d
```

Services starting:
- ✅ Elasticsearch (port 9200)
- ✅ Zookeeper (port 2181)
- ✅ Kafka (port 9092)
- ✅ Logstash (port 5044)
- ✅ Kibana (port 5601)
- ✅ Elasticvue (port 8080)
- ✅ Laravel App (port 8000)
- ✅ Filebeat (monitoring logs)
- ✅ Log Generator (Python - optional)

### 3. Wait for Services to be Healthy

```bash
# Check all services are running
podman ps

# Watch logs for "healthy" status
podman-compose logs -f elasticsearch kibana logstash
```

**Wait times:**
- Elasticsearch: ~30 seconds
- Kibana: ~45 seconds
- Logstash: ~20 seconds
- Kafka: ~15 seconds

### 4. Generate Test Logs

```bash
# Generate a single log entry
curl "http://localhost:8000/api/logs/generate"

# Generate 50 logs at once
curl "http://localhost:8000/api/logs/batch?count=50"

# Simulate error scenarios
curl "http://localhost:8000/api/logs/errors"

# Generate logs with custom data
curl "http://localhost:8000/api/logs/generate?user_id=1234&action=checkout"
```

### 5. View Logs in Kibana

1. **Open Kibana:** http://localhost:5601

2. **Create Data View:**
   - Navigate to: Management → Stack Management → Data Views
   - Click "Create data view"
   - Name: `Kafka Laravel Logs`
   - Index pattern: `kafka-logstash-logs-*`
   - Time field: `@timestamp`
   - Click "Save data view to Kibana"

3. **Explore Logs (Discover):**
   - Navigate to: Analytics → Discover
   - Select your data view
   - Filter by Laravel logs:
     ```kql
     log_source: "laravel-app"
     ```

4. **Example Queries:**

   ```kql
   # All Laravel error logs
   log_source: "laravel-app" AND level_name: "ERROR"
   
   # Logs from specific user
   log_source: "laravel-app" AND context.user_id: 1234
   
   # Database errors
   log_source: "laravel-app" AND message: *database*
   
   # Slow operations (>1 second)
   log_source: "laravel-app" AND context.response_time > 1000
   
   # API failures
   log_source: "laravel-app" AND context.status_code >= 500
   ```

## 📊 Create Visualizations

### 1. Log Level Distribution (Pie Chart)

- Visualization Type: **Pie**
- Metric: **Count**
- Bucket: **Terms** on `level_name.keyword`
- Filter: `log_source: "laravel-app"`

### 2. Logs Over Time (Line Chart)

- Visualization Type: **Line**
- Y-axis: **Count**
- X-axis: **Date Histogram** on `@timestamp`
- Breakdown: **Terms** on `level_name.keyword`

### 3. Top Users (Bar Chart)

- Visualization Type: **Bar**
- Y-axis: **Count**
- X-axis: **Terms** on `context.user_id`
- Order: **Top 10**

### 4. Error Rate (Metric)

- Visualization Type: **Metric**
- Metric: **Count**
- Filter: `level_name: "ERROR" OR level_name: "CRITICAL"`

## 🔍 Monitoring the Pipeline

### Check Laravel Logs Locally

```bash
# View raw log file
tail -f Application-Logger/storage/logs/application.log

# Pretty print JSON logs
tail -f Application-Logger/storage/logs/application.log | jq .
```

### Check Filebeat is Reading Logs

```bash
podman logs -f filebeat
```

Look for: `"events": {"active": X, "added": Y}`

### Check Kafka Topics

```bash
# List topics
podman exec -it kafka kafka-topics.sh --list --bootstrap-server localhost:9092

# Consume messages
podman exec -it kafka kafka-console-consumer.sh \
  --bootstrap-server localhost:9092 \
  --topic filebeat-logs \
  --from-beginning \
  --max-messages 10
```

### Check Logstash Processing

```bash
podman logs -f logstash | grep "kafka-logstash-logs"
```

### Query Elasticsearch Directly

```bash
# Check indices
curl "http://localhost:9200/_cat/indices?v"

# Count Laravel logs
curl "http://localhost:9200/kafka-logstash-logs-*/_count?q=log_source:laravel-app&pretty"

# Get sample logs
curl "http://localhost:9200/kafka-logstash-logs-*/_search?q=log_source:laravel-app&size=3&pretty"
```

### Check Kibana Status

```bash
curl "http://localhost:5601/api/status" | jq .
```

## 🧪 Testing Scenarios

### High-Volume Load Test

```bash
# Generate 1000 logs (in batches of 100)
for i in {1..10}; do
  curl "http://localhost:8000/api/logs/batch?count=100"
  echo "Batch $i completed"
  sleep 2
done
```

### Error Simulation

```bash
# Generate database errors
curl "http://localhost:8000/api/logs/errors?scenario=database"

# Generate API errors
curl "http://localhost:8000/api/logs/errors?scenario=api"

# Generate validation errors
curl "http://localhost:8000/api/logs/errors?scenario=validation"
```

### Realistic Traffic Pattern

```bash
# Simulate realistic user activity
while true; do
  curl -s "http://localhost:8000/api/logs/generate?user_id=$((RANDOM % 100 + 1000))&action=page_view" > /dev/null
  sleep $((RANDOM % 5 + 1))
done
```

Press `Ctrl+C` to stop.

## 🐛 Troubleshooting

### Logs Not Appearing in Kibana

1. **Check Laravel is writing logs:**
   ```bash
   ls -lh Application-Logger/storage/logs/
   cat Application-Logger/storage/logs/application.log | tail -5
   ```

2. **Verify JSON format:**
   ```bash
   tail -1 Application-Logger/storage/logs/application.log | jq .
   ```

3. **Check Filebeat is reading:**
   ```bash
   podman logs filebeat 2>&1 | grep "laravel"
   ```

4. **Check Kafka has messages:**
   ```bash
   podman exec -it kafka kafka-console-consumer.sh \
     --bootstrap-server localhost:9092 \
     --topic filebeat-logs \
     --from-beginning --max-messages 1
   ```

5. **Check Logstash is processing:**
   ```bash
   podman logs logstash | grep -i error
   ```

6. **Verify Elasticsearch has data:**
   ```bash
   curl "http://localhost:9200/_cat/indices?v"
   curl "http://localhost:9200/kafka-logstash-logs-*/_count?pretty"
   ```

### Laravel Application Not Starting

```bash
# Check logs
podman logs laravel-app

# Rebuild container
podman-compose down
podman-compose up -d --build laravel-app
```

### Permission Errors

```bash
# Fix Laravel storage permissions
chmod -R 775 Application-Logger/storage
chmod -R 775 Application-Logger/bootstrap/cache
```

### Filebeat Not Reading Laravel Logs

```bash
# Check volume mount
podman exec -it filebeat ls -lh /var/log/laravel/

# Verify Filebeat config
podman exec -it filebeat cat /usr/share/filebeat/filebeat.yml
```

## 📁 Project Structure

```
ELK-demo/
├── Application-Logger/          # Laravel application
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── LogTestController.php   # Log generation endpoints
│   │   └── Logging/
│   │       └── JsonLogFormatter.php    # JSON formatter
│   ├── config/
│   │   └── logging.php          # Logging configuration
│   ├── routes/
│   │   └── web.php              # API routes
│   ├── storage/logs/            # Log files (mounted to containers)
│   ├── Dockerfile               # Laravel container
│   ├── setup.sh                 # Setup script
│   └── README-LOGGING.md        # Detailed logging docs
├── filebeat/
│   └── filebeat.yml             # Filebeat config (monitors Laravel logs)
├── logstash/
│   └── pipeline/
│       └── logstash.conf        # Logstash parsing pipeline
├── podman-compose.yml           # All services definition
└── README-LARAVEL-ELK.md        # This file
```

## 🎯 Key Features

### Laravel Application
- ✅ JSON structured logging
- ✅ Multiple log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- ✅ Contextual data (user_id, IP, timestamps, etc.)
- ✅ API endpoints for easy testing
- ✅ Batch log generation
- ✅ Error scenario simulation

### Pipeline Components
- ✅ **Filebeat**: Monitors Laravel log files, ships to Kafka
- ✅ **Kafka**: Buffers logs, ensures no data loss
- ✅ **Logstash**: Parses JSON, enriches data
- ✅ **Elasticsearch**: Stores and indexes logs
- ✅ **Kibana**: Visualizes and analyzes logs

## 📚 Additional Resources

- **Kibana Guide**: See `KIBANA-GUIDE.md` for comprehensive visualization tutorials
- **Kafka Integration**: See `README-KAFKA.md` for Kafka setup details
- **Architecture**: See `ARCHITECTURE.md` for system evolution and design decisions
- **Laravel Logging**: See `Application-Logger/README-LOGGING.md` for detailed logging documentation

## 🔄 Stopping and Cleaning Up

```bash
# Stop all services
podman-compose down

# Remove volumes (deletes all data)
podman-compose down -v

# Remove Laravel logs
rm -rf Application-Logger/storage/logs/*.log
```

## 🎉 Success Checklist

- [ ] All 9 services running (`podman ps`)
- [ ] Laravel app responding (`curl http://localhost:8000`)
- [ ] Logs being generated (`curl http://localhost:8000/api/logs/generate`)
- [ ] Logs in file (`tail Application-Logger/storage/logs/application.log`)
- [ ] Filebeat shipping (`podman logs filebeat | grep laravel`)
- [ ] Kafka receiving (`podman exec -it kafka kafka-console-consumer.sh ...`)
- [ ] Elasticsearch storing (`curl http://localhost:9200/_cat/indices`)
- [ ] Kibana visualizing (`http://localhost:5601`)

**When all checks pass, you have a fully operational Laravel + ELK-Kafka logging pipeline!** 🚀
