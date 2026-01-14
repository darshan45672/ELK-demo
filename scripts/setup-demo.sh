#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║     ELK Stack Demo - 2 Hour Hands-on PoC                     ║
║                                                               ║
║     Elasticsearch + Logstash + Kibana                         ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}\n"

# Function to check if a service is running
check_service() {
    local service=$1
    local port=$2
    local url=$3
    
    echo -e "${YELLOW}Checking $service...${NC}"
    
    if curl -s "$url" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ $service is running on port $port${NC}"
        return 0
    else
        echo -e "${RED}✗ $service is not responding${NC}"
        return 1
    fi
}

# Function to wait for a service
wait_for_service() {
    local service=$1
    local url=$2
    local max_attempts=30
    local attempt=1
    
    echo -e "${YELLOW}Waiting for $service to be ready...${NC}"
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s "$url" > /dev/null 2>&1; then
            echo -e "${GREEN}✓ $service is ready!${NC}"
            return 0
        fi
        echo -e "${CYAN}  Attempt $attempt/$max_attempts - waiting...${NC}"
        sleep 10
        ((attempt++))
    done
    
    echo -e "${RED}✗ $service failed to start within expected time${NC}"
    return 1
}

# Main setup flow
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Step 1: Starting ELK Stack with Docker Compose${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Detect container engine
if command -v docker &> /dev/null; then
    CONTAINER_CMD="docker"
    COMPOSE_CMD="docker compose"
elif command -v podman &> /dev/null; then
    CONTAINER_CMD="podman"
    COMPOSE_CMD="podman-compose"
else
    echo -e "${RED}✗ Neither Docker nor Podman found. Please install one.${NC}"
    exit 1
fi

echo -e "${CYAN}Using container engine: $CONTAINER_CMD${NC}"
echo -e "${YELLOW}Starting containers...${NC}"
$COMPOSE_CMD up -d

if [ $? -ne 0 ]; then
    echo -e "${RED}✗ Failed to start containers${NC}"
    exit 1
fi

echo -e "\n${GREEN}✓ Containers started${NC}\n"

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Step 2: Waiting for Services to Initialize${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Wait for Elasticsearch
wait_for_service "Elasticsearch" "http://localhost:9200"
es_status=$?

# Wait for Kibana
wait_for_service "Kibana" "http://localhost:5601/api/status"
kibana_status=$?

echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Step 3: Verifying Services${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Check Elasticsearch
check_service "Elasticsearch" "9200" "http://localhost:9200"

# Check Kibana
check_service "Kibana" "5601" "http://localhost:5601/api/status"

# Check Logstash
check_service "Logstash" "9600" "http://localhost:9600"

echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Step 4: Elasticsearch Cluster Info${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

curl -s http://localhost:9200 | jq '.'

echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Demo Environment Ready!${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

echo -e "${GREEN}✓ All services are running!${NC}\n"

echo -e "${CYAN}Access URLs:${NC}"
echo -e "  • Elasticsearch: ${YELLOW}http://localhost:9200${NC}"
echo -e "  • Kibana:        ${YELLOW}http://localhost:5601${NC}"
echo -e "  • Logstash:      ${YELLOW}http://localhost:9600${NC}"

echo -e "\n${CYAN}Next Steps:${NC}"
echo -e "  1. Open Kibana in your browser: ${YELLOW}http://localhost:5601${NC}"
echo -e "  2. Generate logs: ${YELLOW}./scripts/generate-logs.sh${NC}"
echo -e "  3. View logs in Kibana Discover"
echo -e "  4. Create visualizations and dashboards"

echo -e "\n${CYAN}Quick Commands:${NC}"
echo -e "  • View logs:          ${YELLOW}docker compose logs -f${NC}"
echo -e "  • Stop services:      ${YELLOW}docker compose down${NC}"
echo -e "  • Restart services:   ${YELLOW}docker compose restart${NC}"
echo -e "  • Check ES indices:   ${YELLOW}curl http://localhost:9200/_cat/indices?v${NC}"

echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}\n"
