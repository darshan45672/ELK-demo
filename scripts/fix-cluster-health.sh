#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   Fixing Elasticsearch Cluster Health${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

echo -e "${YELLOW}Setting replicas to 0 for single-node cluster...${NC}"

# Set all existing indices to 0 replicas
curl -X PUT "http://localhost:9200/_settings" -H 'Content-Type: application/json' -d'
{
  "index": {
    "number_of_replicas": 0
  }
}'

echo -e "\n\n${YELLOW}Setting default template for future indices...${NC}"

# Set default template for new indices
curl -X PUT "http://localhost:9200/_template/default_template" -H 'Content-Type: application/json' -d'
{
  "index_patterns": ["*"],
  "settings": {
    "number_of_replicas": 0
  }
}'

echo -e "\n\n${YELLOW}Waiting for cluster to stabilize...${NC}"
sleep 5

echo -e "\n${GREEN}Cluster Health:${NC}"
curl -s "http://localhost:9200/_cluster/health?pretty"

echo -e "\n${GREEN}✓ Cluster health fixed!${NC}\n"
