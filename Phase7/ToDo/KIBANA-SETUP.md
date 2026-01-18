# Kibana Dashboard Setup Guide

Complete guide to set up Kibana dashboards for Laravel Todo application logs in your ELK stack.

---

## Prerequisites

✅ All containers running: `podman-compose up -d`
✅ Elasticsearch accessible at http://localhost:9200
✅ Kibana accessible at http://localhost:5601
✅ Logs flowing from Laravel → Filebeat → Kafka → Logstash → Elasticsearch

---

## Step 1: Access Kibana

1. Open your browser and navigate to: **http://localhost:5601**
2. Wait for Kibana to fully load (first time may take 1-2 minutes)

---

## Step 2: Create Data View (Index Pattern)

A Data View tells Kibana which Elasticsearch indices to query.

### Option A: Using Kibana UI

1. Click the **☰ Menu** (hamburger icon) in the top-left
2. Navigate to **Management** → **Stack Management**
3. Under **Kibana**, click **Data Views**
4. Click **Create data view** button
5. Fill in the form:
   - **Name**: `Todo Application Logs`
   - **Index pattern**: `kafka-logstash-logs-*`
   - **Timestamp field**: `@timestamp`
6. Click **Save data view to Kibana**

### Option B: Using Kibana Dev Tools (API)

1. Go to **☰ Menu** → **Management** → **Dev Tools**
2. Run this command in the Console:

```json
POST kbn:/api/data_views/data_view
{
  "data_view": {
    "title": "kafka-logstash-logs-*",
    "name": "Todo Application Logs",
    "timeFieldName": "@timestamp"
  }
}
```

---

## Step 3: Explore Your Logs

1. Go to **☰ Menu** → **Analytics** → **Discover**
2. Select **Todo Application Logs** from the data view dropdown (top-left)
3. Set the time range (top-right):
   - Click the calendar icon
   - Select **Last 15 minutes** or **Last 1 hour**
4. You should now see your Laravel logs!

### Understanding Log Fields

Your logs contain these important fields:

| Field | Description | Example |
|-------|-------------|---------|
| `@timestamp` | When the log was created | `2026-01-16T11:19:18.992Z` |
| `level` | Log severity | `INFO`, `WARNING`, `ERROR` |
| `message` | Log message | `Fetching todos list` |
| `context.user_id` | User who triggered the action | `123` |
| `context.action` | What action occurred | `create_todo`, `update_todo` |
| `log_source` | Application source | `todo-app` |
| `channel` | Laravel log channel | `local` |

---

## Step 4: Create Visualizations

### Visualization 1: Log Levels Over Time

Shows distribution of INFO, WARNING, ERROR logs over time.

1. Go to **☰ Menu** → **Analytics** → **Visualize Library**
2. Click **Create visualization**
3. Select **Area** chart
4. Choose data view: **Todo Application Logs**
5. Configure:
   - **Vertical axis**: Count (auto-set)
   - **Horizontal axis**: Click **+ Add** → Select `@timestamp`
   - **Break down by**: Click **+ Add** → Select `level.keyword`
6. Click **Save** → Name it: `Log Levels Over Time`

### Visualization 2: Top Users by Activity

Shows which users are most active in the application.

1. Create new visualization
2. Select **Bar horizontal** chart
3. Configure:
   - **Vertical axis**: Count
   - **Horizontal axis**: Click **+ Add** → Select `context.user_id`
   - Sort by: Count (descending)
   - Top values: 10
4. Click **Save** → Name it: `Top Active Users`

### Visualization 3: Error Rate Metric

Shows total number of errors.

1. Create new visualization
2. Select **Metric**
3. Configure:
   - Click **Add or drag-and-drop a field**
   - Select **Count**
   - Add Filter: `level.keyword : "ERROR"`
4. Style:
   - Change color to red
   - Increase font size
5. Click **Save** → Name it: `Total Errors`

### Visualization 4: Log Messages Table

Shows recent log entries with key fields.

1. Create new visualization
2. Select **Table**
3. Configure columns:
   - Add `@timestamp`
   - Add `level.keyword`
   - Add `message`
   - Add `context.user_id`
   - Add `context.action`
4. Sort by `@timestamp` descending
5. Click **Save** → Name it: `Recent Log Entries`

---

## Step 5: Create Dashboard

Combine all visualizations into a single dashboard.

1. Go to **☰ Menu** → **Analytics** → **Dashboard**
2. Click **Create dashboard**
3. Click **Add from library**
4. Select all your saved visualizations:
   - ✅ Log Levels Over Time
   - ✅ Top Active Users
   - ✅ Total Errors
   - ✅ Recent Log Entries
5. Click **Add** (X items selected)
6. Arrange the panels:
   - Drag to reposition
   - Resize by dragging corners
   - Suggested layout:
     ```
     ┌─────────────────────┬───────────┐
     │ Log Levels Over Time│ Total     │
     │                     │ Errors    │
     ├─────────────────────┴───────────┤
     │ Top Active Users                │
     ├─────────────────────────────────┤
     │ Recent Log Entries              │
     └─────────────────────────────────┘
     ```
7. Click **Save** → Name it: `Todo App Monitoring Dashboard`

---

## Step 6: Add More Advanced Visualizations

### Error Messages Word Cloud

1. Create **Tag cloud** visualization
2. Configure:
   - **Tags**: Select `message.keyword`
   - Add Filter: `level.keyword : "ERROR"`
   - Top values: 20
3. Save as: `Error Messages Cloud`

### Response Time Line Chart (if you log response times)

1. Create **Line** chart
2. Configure:
   - **Vertical axis**: Average of `context.response_time`
   - **Horizontal axis**: `@timestamp`
3. Save as: `Average Response Time`

### Actions Breakdown Pie Chart

1. Create **Donut** chart
2. Configure:
   - **Slice by**: `context.action.keyword`
   - Top values: 10
3. Save as: `Actions Distribution`

---

## Step 7: Set Up Alerts (Optional)

Get notified when errors spike.

1. Go to **☰ Menu** → **Management** → **Stack Management**
2. Under **Alerts and Insights**, click **Rules**
3. Click **Create rule**
4. Configure:
   - **Name**: High Error Rate Alert
   - **Check every**: 1 minute
   - **Notify**: When threshold is above 10 errors in 5 minutes
5. Add action (email, Slack, webhook, etc.)
6. Click **Save**

---

## Step 8: Generate Test Logs

To see your dashboards in action, generate some logs:

```bash
# Navigate to Todo app
cd /Users/darshandineshbhandary/GitHub/ELK-demo/Todo

# Generate test logs
php artisan tinker --execute="
  Log::info('User logged in', ['user_id' => 101, 'action' => 'login']);
  Log::warning('Slow query detected', ['user_id' => 102, 'query_time' => 2.5]);
  Log::error('Database connection failed', ['user_id' => 103, 'error' => 'timeout']);
"
```

Or use the actual application:
1. Visit http://localhost:8000/todos
2. Create, update, delete todos
3. Watch logs appear in Kibana in real-time!

---

## Useful Kibana Query Language (KQL) Examples

Use these in the Discover search bar or as dashboard filters:

```kql
# Show only errors
level: "ERROR"

# Show logs from specific user
context.user_id: 123

# Show specific actions
context.action: "create_todo" OR context.action: "delete_todo"

# Show logs with specific message
message: "failed" OR message: "error"

# Show logs from last hour
@timestamp >= now-1h

# Combine filters
level: "ERROR" AND context.user_id: 123

# Show logs NOT from a user
NOT context.user_id: 123

# Show logs with context field present
_exists_: context.user_id
```

---

## Troubleshooting

### No data appears in Discover
1. Check time range (expand to "Last 7 days")
2. Verify index exists:
   - Go to Dev Tools
   - Run: `GET _cat/indices/kafka-logstash-logs-*`
3. Generate test logs (see Step 8)
4. Check containers are running: `podman ps`

### "_jsonparsefailure" tag appears
- This was fixed! If you still see it:
  1. Verify `.env` has `LOG_CHANNEL=elk_json`
  2. Restart Laravel: `php artisan config:clear`
  3. Check log format: `cat storage/logs/elk.log`

### Visualizations show "No results found"
1. Check your filters
2. Verify field names match (use auto-complete)
3. Ensure data exists for selected time range

### Dashboard is slow
1. Reduce time range
2. Add more specific filters
3. Limit table rows to 100
4. Consider using Lens instead of legacy visualizations

---

## Next Steps

### Performance Optimization
- Set up index lifecycle management (ILM)
- Configure index rollover at 50GB or 30 days
- Delete old logs automatically

### Security
- Enable Elasticsearch security features
- Set up user authentication for Kibana
- Implement role-based access control (RBAC)

### Advanced Monitoring
- Set up APM (Application Performance Monitoring)
- Add custom metrics
- Integrate with Elastic Observability

### Machine Learning
- Use ML to detect anomalies in log patterns
- Predict error rates
- Identify unusual user behavior

---

## Quick Reference: Navigation

| Feature | Path |
|---------|------|
| Discover (Search Logs) | ☰ → Analytics → Discover |
| Dashboards | ☰ → Analytics → Dashboard |
| Visualizations | ☰ → Analytics → Visualize Library |
| Data Views | ☰ → Management → Stack Management → Data Views |
| Dev Tools | ☰ → Management → Dev Tools |
| Alerts | ☰ → Management → Stack Management → Rules |

---

## Resources

- [Kibana Official Docs](https://www.elastic.co/guide/en/kibana/8.19/index.html)
- [KQL Query Syntax](https://www.elastic.co/guide/en/kibana/8.19/kuery-query.html)
- [Elasticsearch Query DSL](https://www.elastic.co/guide/en/elasticsearch/reference/8.19/query-dsl.html)
- [Lens Visualizations](https://www.elastic.co/guide/en/kibana/8.19/lens.html)

---

**📊 Your ELK Stack is now fully configured for Laravel application monitoring!**

Access your dashboards at: http://localhost:5601
