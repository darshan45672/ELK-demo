# Kibana Saved Objects - Alert Examples

This document contains example alert configurations that you can create in Kibana.

## Alert Rule 1: High Error Rate

**How to Create:**

1. Go to **Stack Management** → **Rules and Connectors**
2. Click **Create rule**
3. Enter the following:

**Basic Settings:**
- Name: `High Error Rate - Checkout Service`
- Tags: `checkout`, `critical`, `errors`
- Check every: `1 minute`

**Rule Type:**
- Select: **Elasticsearch query**

**Define Rule:**
- Index: `checkout-logs-*`
- Size: `100`
- Time field: `@timestamp`
- Time window: `5 minutes`

**Query (JSON):**
```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "level": "ERROR"
          }
        }
      ]
    }
  }
}
```

**Threshold:**
- When: `count`
- IS ABOVE: `2`
- FOR THE LAST: `5 minutes`

**Actions:**
- Add action: **Server Log**
- Message:
```
⚠️ HIGH ERROR RATE ALERT ⚠️

Error Count: {{context.hits}}
Time Window: Last 5 minutes
Triggered At: {{context.date}}

Service: Checkout Service
Environment: Production

Action Required: Investigate immediately in Kibana Discover
```

---

## Alert Rule 2: High Latency Alert

**Basic Settings:**
- Name: `High Latency - Performance Degradation`
- Tags: `checkout`, `performance`, `latency`
- Check every: `1 minute`

**Rule Type:**
- Select: **Elasticsearch query**

**Define Rule:**
- Index: `checkout-logs-*`
- Size: `100`
- Time field: `@timestamp`
- Time window: `5 minutes`

**Query (JSON):**
```json
{
  "query": {
    "range": {
      "latencyMs": {
        "gte": 1000
      }
    }
  }
}
```

**Threshold:**
- When: `count`
- IS ABOVE: `3`
- FOR THE LAST: `5 minutes`

**Actions:**
- Add action: **Server Log**
- Message:
```
⚠️ HIGH LATENCY DETECTED ⚠️

High Latency Requests: {{context.hits}}
Threshold: >1000ms
Time Window: Last 5 minutes

Service: Checkout Service
Impact: Customer experience degradation

Action Required: Check service health and dependencies
```

---

## Alert Rule 3: Payment Failure Spike

**Basic Settings:**
- Name: `Payment Failure Spike - Critical`
- Tags: `checkout`, `payment`, `critical`
- Check every: `1 minute`

**Rule Type:**
- Select: **Elasticsearch query**

**Define Rule:**
- Index: `checkout-logs-*`
- Size: `100`
- Time field: `@timestamp`
- Time window: `5 minutes`

**Query (JSON):**
```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "errorCode": "PAYMENT_FAILED"
          }
        }
      ]
    }
  }
}
```

**Threshold:**
- When: `count`
- IS ABOVE: `2`
- FOR THE LAST: `5 minutes`

**Actions:**
- Add action: **Server Log**
- Message:
```
🚨 PAYMENT FAILURE SPIKE 🚨

Payment Failures: {{context.hits}}
Time Window: Last 5 minutes
Service: Checkout Service

CRITICAL: Revenue impact - immediate action required!

Next Steps:
1. Check payment gateway status
2. Review recent deployments
3. Verify API credentials
4. Check network connectivity
```

---

## Alert Rule 4: Timeout Errors

**Basic Settings:**
- Name: `Timeout Errors - Service Degradation`
- Tags: `checkout`, `timeout`, `performance`
- Check every: `2 minutes`

**Rule Type:**
- Select: **Elasticsearch query**

**Define Rule:**
- Index: `checkout-logs-*`
- Size: `50`
- Time field: `@timestamp`
- Time window: `10 minutes`

**Query (JSON):**
```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "errorCode": "TIMEOUT"
          }
        }
      ]
    }
  }
}
```

**Threshold:**
- When: `count`
- IS ABOVE: `3`
- FOR THE LAST: `10 minutes`

**Actions:**
- Add action: **Server Log**
- Message:
```
⚠️ TIMEOUT ERRORS DETECTED ⚠️

Timeout Count: {{context.hits}}
Time Window: Last 10 minutes

Possible Causes:
- Downstream service slow
- Database performance issues
- Network problems
- Resource exhaustion

Check service dependencies immediately!
```

---

## Alert Rule 5: User-Specific Issues

**Basic Settings:**
- Name: `User Error Pattern - Account 42`
- Tags: `user-specific`, `investigation`
- Check every: `5 minutes`

**Rule Type:**
- Select: **Elasticsearch query**

**Define Rule:**
- Index: `checkout-logs-*`
- Size: `100`
- Time field: `@timestamp`
- Time window: `15 minutes`

**Query (JSON):**
```json
{
  "query": {
    "bool": {
      "must": [
        {
          "match": {
            "userId": "42"
          }
        },
        {
          "match": {
            "level": "ERROR"
          }
        }
      ]
    }
  }
}
```

**Threshold:**
- When: `count`
- IS ABOVE: `3`
- FOR THE LAST: `15 minutes`

**Actions:**
- Add action: **Server Log**
- Message:
```
👤 USER-SPECIFIC ERROR PATTERN

User ID: 42
Errors: {{context.hits}}
Time Window: Last 15 minutes

Action: Review user account and recent activity
Possible account-specific issue or data problem
```

---

## Connector Setup (for Email/Slack)

### Email Connector

1. Go to **Stack Management** → **Rules and Connectors** → **Connectors**
2. Click **Create connector**
3. Select **Email**
4. Configure:
   - Name: `Email Alerts`
   - Sender: `elk-alerts@yourcompany.com`
   - Host: `smtp.gmail.com`
   - Port: `587`
   - Secure: `true`
   - Username: Your email
   - Password: App password

### Slack Connector

1. Create Slack webhook: https://api.slack.com/messaging/webhooks
2. In Kibana, create connector:
   - Type: **Slack**
   - Name: `Slack Alerts`
   - Webhook URL: Your webhook URL
3. Test the connector

### Webhook Connector (Generic)

For integrating with PagerDuty, Opsgenie, etc.:

```json
{
  "method": "POST",
  "url": "https://your-webhook-url.com/alert",
  "headers": {
    "Content-Type": "application/json",
    "Authorization": "Bearer YOUR_TOKEN"
  },
  "body": {
    "alert": "{{context.title}}",
    "severity": "critical",
    "count": "{{context.hits}}",
    "timestamp": "{{context.date}}"
  }
}
```

---

## Advanced Alert: Anomaly Detection

**If you have ML license:**

1. Go to **Machine Learning** → **Anomaly Detection**
2. Create job:
   - Job ID: `checkout-latency-anomaly`
   - Index: `checkout-logs-*`
   - Detector: `mean(latencyMs)`
   - Bucket span: `5m`
3. Create alert rule:
   - Type: **Anomaly detection alert**
   - Select job: `checkout-latency-anomaly`
   - Severity: `critical` (score > 75)

---

## Testing Alerts

### Trigger High Error Rate Alert

```bash
./scripts/generate-logs.sh
# Select option 4 (simulate error spike)
```

Or manually:
```bash
for i in {1..5}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ERROR checkout orderId=$((3000+i)) userId=99 latencyMs=$((1200+RANDOM%800)) errorCode=TIMEOUT" >> logs/app.log
  sleep 1
done
```

### Trigger High Latency Alert

```bash
for i in {1..5}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) WARN checkout orderId=$((4000+i)) userId=77 latencyMs=$((1500+RANDOM%1000))" >> logs/app.log
  sleep 2
done
```

### Trigger Payment Failure Alert

```bash
for i in {1..3}; do
  echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ERROR checkout orderId=$((5000+i)) userId=88 latencyMs=980 errorCode=PAYMENT_FAILED" >> logs/app.log
  sleep 1
done
```

---

## Viewing Alert History

1. Go to **Stack Management** → **Rules and Connectors** → **Rules**
2. Click on rule name
3. View:
   - **Execution history** - When rule checked
   - **Alert history** - When alerts fired
   - **Actions** - What actions were taken

---

## Alert Best Practices

### 1. Start Conservative
- Set higher thresholds initially
- Avoid alert fatigue
- Adjust based on actual patterns

### 2. Use Appropriate Time Windows
- Short (1-5 min): Critical errors
- Medium (5-15 min): Performance degradation
- Long (15-60 min): Trends and patterns

### 3. Clear Action Items
- Include what to check
- Link to runbooks
- Specify urgency

### 4. Test Regularly
- Trigger test alerts weekly
- Verify notification delivery
- Update on-call procedures

### 5. Review and Adjust
- Weekly: Check false positives
- Monthly: Review thresholds
- Quarterly: Update alert strategy

---

## Troubleshooting Alerts

### Alert Not Firing

```bash
# Check rule status
# Kibana → Rules → Check "Last run" column

# Verify data exists
curl "http://localhost:9200/checkout-logs-*/_search?q=level:ERROR&size=0"

# Check rule execution logs
# Click on rule → View in Stack Management
```

### Too Many False Positives

- Increase threshold
- Lengthen time window
- Add more specific filters
- Use anomaly detection instead

### Actions Not Executing

- Verify connector configuration
- Check connector test
- Review action logs
- Check system logs: `docker compose logs kibana`

---

## Next Level: Watcher (Advanced)

For more complex alerting logic, use Elasticsearch Watcher:

```json
PUT _watcher/watch/checkout_health
{
  "trigger": {
    "schedule": {
      "interval": "1m"
    }
  },
  "input": {
    "search": {
      "request": {
        "indices": ["checkout-logs-*"],
        "body": {
          "query": {
            "bool": {
              "must": [
                {"match": {"level": "ERROR"}},
                {"range": {"@timestamp": {"gte": "now-5m"}}}
              ]
            }
          }
        }
      }
    }
  },
  "condition": {
    "compare": {
      "ctx.payload.hits.total": {
        "gt": 2
      }
    }
  },
  "actions": {
    "log_error": {
      "logging": {
        "text": "High error rate: {{ctx.payload.hits.total}} errors in last 5 minutes"
      }
    }
  }
}
```

---

For more information on alerting, see the [official Kibana Alerting documentation](https://www.elastic.co/guide/en/kibana/current/alerting-getting-started.html).
