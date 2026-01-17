import time
import random
import json
import requests
from datetime import datetime

# Elasticsearch configuration
ES_HOST = "http://elasticsearch:9200"
INDEX_NAME = "app-logs"

# Sample log levels and messages
LOG_LEVELS = ["INFO", "WARNING", "ERROR", "DEBUG"]
SERVICES = ["auth-service", "payment-service", "user-service", "order-service", "notification-service"]
MESSAGES = [
    "User login successful",
    "Payment processed",
    "Database connection established",
    "API request received",
    "Cache miss occurred",
    "Failed to connect to external service",
    "Request timeout",
    "Invalid user credentials",
    "Session expired",
    "Data validation failed"
]

def generate_log():
    """Generate a random log entry"""
    log_entry = {
        "timestamp": datetime.utcnow().isoformat(),
        "level": random.choice(LOG_LEVELS),
        "service": random.choice(SERVICES),
        "message": random.choice(MESSAGES),
        "user_id": f"user_{random.randint(1000, 9999)}",
        "request_id": f"req_{random.randint(10000, 99999)}",
        "duration_ms": random.randint(10, 5000),
        "status_code": random.choice([200, 201, 400, 401, 404, 500, 503])
    }
    return log_entry

def send_log_to_elasticsearch(log_entry):
    """Send log entry to Elasticsearch"""
    try:
        url = f"{ES_HOST}/{INDEX_NAME}/_doc"
        headers = {"Content-Type": "application/json"}
        response = requests.post(url, json=log_entry, headers=headers, timeout=10)
        
        if response.status_code in [200, 201]:
            print(f"✓ Log sent: {log_entry['level']} - {log_entry['service']} - {log_entry['message']}", flush=True)
        else:
            print(f"✗ Failed to send log: {response.status_code} - {response.text}", flush=True)
    except Exception as e:
        print(f"✗ Error sending log: {str(e)}", flush=True)

def main():
    print("Starting log generator...", flush=True)
    print(f"Sending logs to: {ES_HOST}/{INDEX_NAME}", flush=True)
    print("-" * 80, flush=True)
    
    # Wait for Elasticsearch to be ready
    for i in range(30):
        try:
            response = requests.get(ES_HOST, timeout=5)
            if response.status_code == 200:
                print("✓ Elasticsearch is ready!", flush=True)
                break
        except Exception as e:
            print(f"Waiting for Elasticsearch... ({i+1}/30): {str(e)}", flush=True)
            time.sleep(2)
    
    print("-" * 80, flush=True)
    
    # Generate and send logs continuously
    while True:
        log_entry = generate_log()
        send_log_to_elasticsearch(log_entry)
        
        # Random delay between 1-5 seconds
        delay = random.uniform(1, 5)
        time.sleep(delay)

if __name__ == "__main__":
    main()
