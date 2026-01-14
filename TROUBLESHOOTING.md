# Common Issues and Solutions

## Quick Troubleshooting Guide

### 1. Containers Won't Start

**Symptom:** `docker compose up -d` fails

**Solutions:**
```bash
# Check if Docker is running
docker info

# Check if ports are already in use
lsof -i :9200
lsof -i :5601
lsof -i :9600

# Increase Docker memory to at least 4GB
# Docker Desktop → Settings → Resources → Memory

# Remove old containers and volumes
docker compose down -v
docker compose up -d
```

### 2. Elasticsearch Keeps Restarting

**Symptom:** `docker ps` shows elasticsearch restarting

**Solutions:**
```bash
# Check logs
docker compose logs elasticsearch

# Common issue: Not enough memory
# Increase Docker memory allocation

# Check vm.max_map_count (Linux)
sysctl vm.max_map_count
# Should be at least 262144

# Fix on Linux:
sudo sysctl -w vm.max_map_count=262144

# Make permanent (Linux):
echo "vm.max_map_count=262144" | sudo tee -a /etc/sysctl.conf
```

### 3. Kibana Shows "Elasticsearch Not Ready"

**Symptom:** Kibana UI shows waiting message

**Solutions:**
```bash
# Wait 2-3 minutes for Elasticsearch to fully start

# Check Elasticsearch health
curl http://localhost:9200/_cluster/health?pretty

# Restart Kibana
docker compose restart kibana

# Check Kibana logs
docker compose logs kibana
```

### 4. No Logs Appearing in Kibana

**Symptom:** Discover page shows no results

**Solutions:**
```bash
# 1. Verify index pattern exists
# Kibana → Stack Management → Index Patterns
# Should see: checkout-logs-*

# 2. Check if data exists in Elasticsearch
curl http://localhost:9200/_cat/indices?v

# 3. Check Logstash is processing
docker compose logs logstash | tail -20

# 4. Verify log file has content
cat logs/app.log

# 5. Check file permissions
ls -la logs/app.log

# 6. Force Logstash restart
docker compose restart logstash

# 7. Refresh index
curl -X POST "http://localhost:9200/checkout-logs-*/_refresh"
```

### 5. Time Range Shows No Data

**Symptom:** Logs exist but Discover shows "No results"

**Solutions:**
- Check time picker (top right in Kibana)
- Select "Last 15 minutes" or "Last 1 hour"
- Click "Refresh" button
- Ensure log timestamps are recent

### 6. Logstash Not Parsing Logs

**Symptom:** Logs appear but fields are not extracted

**Solutions:**
```bash
# Check Logstash configuration syntax
docker compose exec logstash logstash --config.test_and_exit -f /usr/share/logstash/pipeline/logstash.conf

# View Logstash processing
docker compose logs -f logstash

# Check for grok pattern errors
# Look for "_grokparsefailure" in logs
```

### 7. High Memory Usage

**Symptom:** System becomes slow

**Solutions:**
```bash
# Check container resource usage
docker stats

# Reduce Elasticsearch heap size
# In docker-compose.yml:
# ES_JAVA_OPTS=-Xms512m -Xmx512m

# Reduce Logstash heap size
# In docker-compose.yml:
# LS_JAVA_OPTS=-Xms256m -Xmx256m

# Restart services
docker compose restart
```

### 8. Can't Access Kibana at localhost:5601

**Symptom:** Browser can't connect

**Solutions:**
```bash
# Check if container is running
docker compose ps

# Check if port is bound
docker port kibana

# Try alternative URLs
# http://127.0.0.1:5601
# http://0.0.0.0:5601

# Check firewall settings

# View Kibana logs
docker compose logs kibana
```

### 9. Scripts Won't Execute

**Symptom:** `./scripts/setup-demo.sh` gives permission denied

**Solutions:**
```bash
# Make scripts executable
chmod +x scripts/*.sh

# Or run with bash
bash scripts/setup-demo.sh
```

### 10. JSON Parse Errors in Queries

**Symptom:** Kibana query fails

**Solutions:**
- Use KQL (Kibana Query Language) instead of JSON
- Example: `level: ERROR` instead of `{"match": {"level": "ERROR"}}`
- For Dev Tools console, ensure proper JSON formatting
- Use `jq` to validate JSON:
  ```bash
  echo '{"query": {"match": {"level": "ERROR"}}}' | jq '.'
  ```

## Performance Optimization

### Slow Queries

```bash
# Check index size
curl http://localhost:9200/_cat/indices?v&h=index,store.size,docs.count

# Force merge indices (careful in production)
curl -X POST "http://localhost:9200/checkout-logs-*/_forcemerge?max_num_segments=1"

# Clear cache
curl -X POST "http://localhost:9200/_cache/clear"
```

### Slow Kibana

```bash
# Clear browser cache
# Use incognito/private mode

# Reduce data retention
# Only keep last 7 days of data

# Increase Kibana memory
# In docker-compose.yml under kibana:
# NODE_OPTIONS="--max-old-space-size=2048"
```

## Reset and Start Fresh

```bash
# Nuclear option - removes ALL data
docker compose down -v
docker system prune -a --volumes
docker compose up -d

# Wait for services to start
./scripts/setup-demo.sh
```

## Getting Help

### Logs to Check

```bash
# All services
docker compose logs

# Specific service with follow
docker compose logs -f elasticsearch
docker compose logs -f logstash
docker compose logs -f kibana

# Last 50 lines
docker compose logs --tail=50 logstash
```

### Useful Commands

```bash
# Container status
docker compose ps

# Container resource usage
docker stats

# Enter container
docker compose exec elasticsearch bash
docker compose exec logstash bash
docker compose exec kibana bash

# Check Elasticsearch settings
curl http://localhost:9200/_cluster/settings?pretty

# Check Logstash plugins
docker compose exec logstash logstash-plugin list
```

### Health Checks

```bash
# Elasticsearch
curl http://localhost:9200/_cluster/health?pretty
curl http://localhost:9200/_cat/nodes?v

# Kibana
curl http://localhost:5601/api/status

# Logstash
curl http://localhost:9600/_node/stats?pretty
```

## Environment-Specific Issues

### macOS

```bash
# If Docker Desktop is slow
# Enable: Preferences → Resources → Enable VirtioFS

# Check Docker Desktop version
docker --version
# Should be latest version
```

### Linux

```bash
# Set vm.max_map_count
sudo sysctl -w vm.max_map_count=262144

# Check SELinux (may block container operations)
getenforce
# If enforcing, may need to disable or configure
```

### Windows WSL2

```bash
# Ensure WSL2 backend is enabled in Docker Desktop

# Increase WSL2 memory
# Create/edit ~/.wslconfig:
[wsl2]
memory=4GB
processors=2

# Restart WSL
wsl --shutdown
```

## Still Having Issues?

1. Check Docker Desktop is running and updated
2. Ensure minimum 4GB RAM allocated to Docker
3. Try the "Reset and Start Fresh" section above
4. Check logs for specific error messages
5. Search Elastic forums: https://discuss.elastic.co/
6. Check GitHub issues: https://github.com/elastic

## Emergency Commands

```bash
# Stop everything immediately
docker compose down

# Kill stuck containers
docker kill $(docker ps -q)

# Remove all containers
docker rm $(docker ps -a -q)

# Clean up everything
docker system prune -a --volumes
```
