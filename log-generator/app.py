import time
import random
import json
import os
from datetime import datetime

# Log file path
LOG_FILE_PATH = "/var/log/app/application.log"

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

def write_log_to_file(log_entry):
    """Write log entry to file"""
    try:
        # Ensure directory exists
        os.makedirs(os.path.dirname(LOG_FILE_PATH), exist_ok=True)
        
        # Write log as JSON line
        with open(LOG_FILE_PATH, 'a') as f:
            f.write(json.dumps(log_entry) + '\n')
            f.flush()
        
        print(f"✓ Log written: {log_entry['level']} - {log_entry['service']} - {log_entry['message']}", flush=True)
    except Exception as e:
        print(f"✗ Error writing log: {str(e)}", flush=True)

def main():
    print("Starting log generator...", flush=True)
    print(f"Writing logs to: {LOG_FILE_PATH}", flush=True)
    print("-" * 80, flush=True)
    
    # Generate and write logs continuously
    while True:
        log_entry = generate_log()
        write_log_to_file(log_entry)
        
        # Random delay between 1-5 seconds
        delay = random.uniform(1, 5)
        time.sleep(delay)

if __name__ == "__main__":
    main()
