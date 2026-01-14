#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   ELK Stack Demo - Log Generator${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Get current timestamp in ISO8601 format
get_timestamp() {
    date -u +"%Y-%m-%dT%H:%M:%SZ"
}

# Generate random values
get_random_user() {
    users=(42 55 67 77 88 99 101 115 120 135)
    echo ${users[$RANDOM % ${#users[@]}]}
}

get_random_order_id() {
    echo $((1000 + $RANDOM % 9000))
}

get_random_latency() {
    case $((RANDOM % 10)) in
        0|1|2) echo $((150 + $RANDOM % 150));;  # 30% fast (150-300ms)
        3|4|5) echo $((300 + $RANDOM % 200));;  # 30% normal (300-500ms)
        6|7)   echo $((500 + $RANDOM % 300));;  # 20% slow (500-800ms)
        8)     echo $((800 + $RANDOM % 400));;  # 10% very slow (800-1200ms)
        9)     echo $((1200 + $RANDOM % 800));; # 10% critical (1200-2000ms)
    esac
}

# Log levels and error codes
LOG_LEVELS=("INFO" "INFO" "INFO" "INFO" "WARN" "ERROR")
ERROR_CODES=("PAYMENT_FAILED" "TIMEOUT" "INVALID_CART" "DATABASE_ERROR" "SERVICE_UNAVAILABLE")

# Generate a single log entry
generate_log() {
    timestamp=$(get_timestamp)
    level=${LOG_LEVELS[$RANDOM % ${#LOG_LEVELS[@]}]}
    order_id=$(get_random_order_id)
    user_id=$(get_random_user)
    latency=$(get_random_latency)
    
    if [ "$level" == "ERROR" ]; then
        error_code=${ERROR_CODES[$RANDOM % ${#ERROR_CODES[@]}]}
        log_line="${timestamp} ${level} checkout orderId=${order_id} userId=${user_id} latencyMs=${latency} errorCode=${error_code}"
    else
        log_line="${timestamp} ${level} checkout orderId=${order_id} userId=${user_id} latencyMs=${latency}"
    fi
    
    echo "$log_line"
}

# Main script
LOG_FILE="logs/app.log"

echo -e "${YELLOW}Select log generation mode:${NC}"
echo "1) Generate single log"
echo "2) Generate multiple logs (specify count)"
echo "3) Generate continuous logs (Ctrl+C to stop)"
echo "4) Simulate error spike (3 errors quickly)"
echo "5) Simulate high latency scenario"
echo ""
read -p "Enter choice [1-5]: " choice

case $choice in
    1)
        log=$(generate_log)
        echo "$log" >> "$LOG_FILE"
        echo -e "${GREEN}✓ Generated 1 log entry${NC}"
        echo -e "${BLUE}$log${NC}"
        ;;
    2)
        read -p "How many logs to generate? " count
        echo -e "${YELLOW}Generating $count logs...${NC}"
        for ((i=1; i<=count; i++)); do
            log=$(generate_log)
            echo "$log" >> "$LOG_FILE"
            if [ $((i % 10)) -eq 0 ]; then
                echo -e "${GREEN}✓ Generated $i logs...${NC}"
            fi
            sleep 0.1
        done
        echo -e "${GREEN}✓ Completed! Generated $count logs${NC}"
        ;;
    3)
        echo -e "${YELLOW}Generating continuous logs (Press Ctrl+C to stop)...${NC}"
        count=0
        while true; do
            log=$(generate_log)
            echo "$log" >> "$LOG_FILE"
            echo -e "${BLUE}[$count] $log${NC}"
            ((count++))
            sleep 2
        done
        ;;
    4)
        echo -e "${RED}Simulating error spike...${NC}"
        for i in {1..5}; do
            timestamp=$(get_timestamp)
            order_id=$(get_random_order_id)
            user_id=$(get_random_user)
            latency=$((1200 + $RANDOM % 800))
            error_code=${ERROR_CODES[$RANDOM % ${#ERROR_CODES[@]}]}
            log_line="${timestamp} ERROR checkout orderId=${order_id} userId=${user_id} latencyMs=${latency} errorCode=${error_code}"
            echo "$log_line" >> "$LOG_FILE"
            echo -e "${RED}✓ $log_line${NC}"
            sleep 0.5
        done
        echo -e "${GREEN}✓ Error spike simulation complete!${NC}"
        ;;
    5)
        echo -e "${YELLOW}Simulating high latency scenario...${NC}"
        for i in {1..5}; do
            timestamp=$(get_timestamp)
            order_id=$(get_random_order_id)
            user_id=$(get_random_user)
            latency=$((1500 + $RANDOM % 1500))
            log_line="${timestamp} WARN checkout orderId=${order_id} userId=${user_id} latencyMs=${latency}"
            echo "$log_line" >> "$LOG_FILE"
            echo -e "${YELLOW}✓ $log_line${NC}"
            sleep 1
        done
        echo -e "${GREEN}✓ High latency simulation complete!${NC}"
        ;;
    *)
        echo -e "${RED}Invalid choice!${NC}"
        exit 1
        ;;
esac

echo -e "\n${GREEN}Log file updated: $LOG_FILE${NC}"
echo -e "${BLUE}Logs should appear in Kibana within a few seconds${NC}"
