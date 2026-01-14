#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

ES_URL="http://localhost:9200"

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   ELK Stack - Health Check${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Check Elasticsearch health
echo -e "${YELLOW}1. Elasticsearch Health:${NC}"
curl -s "$ES_URL/_cluster/health?pretty" | jq '.'

echo -e "\n${YELLOW}2. Elasticsearch Nodes:${NC}"
curl -s "$ES_URL/_cat/nodes?v"

echo -e "\n${YELLOW}3. Indices List:${NC}"
curl -s "$ES_URL/_cat/indices?v&s=index"

echo -e "\n${YELLOW}4. Document Count:${NC}"
index_name=$(curl -s "$ES_URL/_cat/indices/checkout-logs-*?h=index" | tail -1)
if [ -n "$index_name" ]; then
    doc_count=$(curl -s "$ES_URL/$index_name/_count" | jq '.count')
    echo -e "${GREEN}Index: $index_name${NC}"
    echo -e "${GREEN}Documents: $doc_count${NC}"
else
    echo -e "${RED}No checkout-logs indices found${NC}"
fi

echo -e "\n${YELLOW}5. Sample Documents:${NC}"
if [ -n "$index_name" ]; then
    curl -s "$ES_URL/$index_name/_search?size=3&pretty" | jq '.hits.hits[]._source'
else
    echo -e "${RED}No documents to display${NC}"
fi

echo -e "\n${YELLOW}6. Container Status:${NC}"
if command -v docker &> /dev/null; then
    docker compose ps
elif command -v podman &> /dev/null; then
    podman-compose ps
fi

echo -e "\n${YELLOW}7. Error Statistics:${NC}"
if [ -n "$index_name" ]; then
    curl -s -X POST "$ES_URL/$index_name/_search?pretty" -H 'Content-Type: application/json' -d'
    {
      "size": 0,
      "aggs": {
        "error_codes": {
          "terms": {
            "field": "errorCode.keyword",
            "size": 10
          }
        },
        "log_levels": {
          "terms": {
            "field": "level.keyword"
          }
        },
        "avg_latency": {
          "avg": {
            "field": "latencyMs"
          }
        }
      }
    }' | jq '.aggregations'
else
    echo -e "${RED}No data available for statistics${NC}"
fi

echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}\n"
