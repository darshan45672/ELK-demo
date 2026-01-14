#!/bin/bash

# Script to bulk-load existing logs into Elasticsearch through Logstash

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Bulk Load Logs into Elasticsearch${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

LOG_FILE="logs/app.log"
TOTAL_LINES=$(wc -l < "$LOG_FILE")

echo -e "${YELLOW}Found $TOTAL_LINES logs in $LOG_FILE${NC}"
echo -e "${YELLOW}Loading logs directly into Elasticsearch...${NC}\n"

# Delete old sincedb to force reprocessing
podman exec logstash rm -f /tmp/sincedb 2>/dev/null || true

# Touch the file to trigger a new read
touch "$LOG_FILE"

# Restart logstash to pick up all logs
echo -e "${YELLOW}Restarting Logstash...${NC}"
podman-compose restart logstash > /dev/null 2>&1

echo -e "${YELLOW}Waiting for Logstash to process logs (30 seconds)...${NC}"
sleep 30

# Check count
COUNT=$(curl -s "http://localhost:9200/checkout-logs-*/_count" | jq '.count')

echo -e "\n${GREEN}✓ Complete!${NC}"
echo -e "${GREEN}Documents in Elasticsearch: $COUNT${NC}"
echo -e "\n${BLUE}Refresh your Kibana dashboard to see the data!${NC}\n"
