# Kibana Dashboard Setup Guide for Laravel Logger App

This comprehensive guide will walk you through setting up a complete Kibana dashboard for monitoring your Laravel application logs.

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Access Kibana](#access-kibana)
3. [Create Data View](#create-data-view)
4. [Discover & Filter Logs](#discover--filter-logs)
5. [Create Visualizations](#create-visualizations)
6. [Build Dashboard](#build-dashboard)
7. [KQL Query Examples](#kql-query-examples)
8. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Ensure all services are running:
```bash
cd /Users/darshandineshbhandary/GitHub/ELK-demo
podman-compose ps

# Expected services:
# - elasticsearch (healthy)
# - kibana (healthy)
# - laravel-app (running)
# - kafka (healthy)
# - logstash (healthy)
# - filebeat (running)
```

Verify logs are flowing:
```bash
curl -s "http://localhost:9200/kafka-logstash-logs-*/_count?q=log_source:laravel-app" | jq .
```

---

## Access Kibana

1. **Open Kibana in your browser:**
   ```
   http://localhost:5601
   ```

2. **Wait for Kibana to initialize** (first time may take 30-60 seconds)

3. **You should see the Kibana home page** with options like:
   - Discover
   - Dashboard
   - Visualize Library
   - Management

---

## Create Data View

A Data View tells Kibana which Elasticsearch indices to query.

### Step 1: Navigate to Data Views
1. Click the **☰ menu** (top-left)
2. Navigate to **Management** → **Stack Management**
3. Under **Kibana**, click **Data Views**

### Step 2: Create New Data View
1. Click **"Create data view"** button
2. Fill in the details:

   **Name:**
   ```
   Laravel Application Logs
   ```

   **Index pattern:**
   ```
   kafka-logstash-logs-*
   ```
   *(This will match all indices starting with `kafka-logstash-logs-`)*

   **Timestamp field:**
   ```
   @timestamp
   ```
   *(Select from dropdown)*

3. Click **"Save data view to Kibana"**

### Step 3: Verify Fields
After creation, you should see all available fields:
- `@timestamp` (date)
- `log_source` (keyword)
- `level_name` (keyword)
- `level` (number)
- `message` (text)
- `channel` (keyword)
- `datetime` (text)
- `context.user_id` (number)
- `context.action` (keyword)
- `context.operation_type` (keyword)
- `context.status_code` (number)
- `context.response_time` (number)
- `context.ip_address` (keyword)
- `context.session_id` (keyword)

---

## Discover & Filter Logs

### Access Discover
1. Click **☰ menu** → **Analytics** → **Discover**
2. Select your **"Laravel Application Logs"** data view (top-left dropdown)

### Filter for Laravel Logs Only

**Method 1: Using Filter Bar**
1. Click **"+ Add filter"** button
2. **Field:** `log_source`
3. **Operator:** `is`
4. **Value:** `laravel-app`
5. Click **"Save"**

**Method 2: Using KQL (Kibana Query Language)**
Enter in the search bar:
```kql
log_source: "laravel-app"
```

### Customize Columns
1. Click **"Add column"** button (or click the `+` next to field names)
2. Add these recommended columns:
   - `@timestamp`
   - `level_name`
   - `message`
   - `context.user_id`
   - `context.action`
   - `context.status_code`

### Time Range Selection
- Click the **time picker** (top-right, shows "Last 15 minutes")
- Select: **"Last 1 hour"**, **"Last 24 hours"**, or custom range
- For real-time monitoring: Enable **"Refresh every 10 seconds"**

---

## Create Visualizations

### Visualization 1: Log Levels Distribution (Pie Chart)

**Purpose:** Show the distribution of log levels (DEBUG, INFO, WARNING, ERROR, CRITICAL)

1. **Navigate to Visualize Library:**
   - **☰ menu** → **Analytics** → **Visualize Library**
   - Click **"Create visualization"**

2. **Select Visualization Type:**
   - Choose **"Pie"**

3. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app"`

4. **Configure Slices:**
   - **Slice by:** `level_name.keyword`
   - **Metric:** Count
   - **Order by:** Metric descending
   - **Size:** 10

5. **Customize Appearance:**
   - **Labels:** Show labels and values
   - **Legend:** Position right
   - **Color palette:** Choose your preference

6. **Save:**
   - Click **"Save"** (top-right)
   - **Title:** `Log Levels Distribution`
   - **Add to dashboard:** Create new dashboard → `Laravel Application Dashboard`
   - Click **"Save"**

---

### Visualization 2: Logs Over Time (Line Chart)

**Purpose:** Monitor log volume trends over time

1. **Create New Visualization:**
   - Click **"Create visualization"**
   - Choose **"Line"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app"`

3. **Configure X-axis (Time):**
   - **Horizontal axis:** `@timestamp`
   - **Interval:** Auto (or set to 1 minute, 5 minutes, etc.)

4. **Configure Y-axis (Count):**
   - **Vertical axis:** Count
   - **Label:** "Log Count"

5. **Add Breakdown (Optional):**
   - **Break down by:** `level_name.keyword`
   - This will show separate lines for each log level

6. **Save:**
   - **Title:** `Logs Over Time`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 3: Top Users by Activity (Bar Chart)

**Purpose:** Identify most active users in your application

1. **Create New Visualization:**
   - Choose **"Bar horizontal"** or **"Bar vertical"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND context.user_id: *`

3. **Configure Axes:**
   - **Horizontal axis:** `context.user_id`
   - **Vertical axis:** Count
   - **Order by:** Metric descending
   - **Size:** 10 (top 10 users)

4. **Customize:**
   - **Label:** "User ID"
   - **Show values on chart:** Yes

5. **Save:**
   - **Title:** `Top 10 Active Users`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 4: Operations by Type (Donut Chart)

**Purpose:** Visualize database operations (INSERT, UPDATE, SELECT, DELETE)

1. **Create New Visualization:**
   - Choose **"Donut"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND context.operation_type: *`

3. **Configure Slices:**
   - **Slice by:** `context.operation_type.keyword`
   - **Metric:** Count

4. **Save:**
   - **Title:** `Operations Distribution`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 5: Error Rate Metric

**Purpose:** Show total error count with threshold alerting

1. **Create New Visualization:**
   - Choose **"Metric"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND (level_name: "ERROR" OR level_name: "CRITICAL")`

3. **Configure Metric:**
   - **Metric:** Count
   - **Label:** "Total Errors"

4. **Add Threshold (Optional):**
   - **Color by value:** Yes
   - **Green:** 0-10
   - **Yellow:** 10-50
   - **Red:** 50+

5. **Save:**
   - **Title:** `Error Count`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 6: Response Time Analysis (Data Table)

**Purpose:** Show average response times by operation type

1. **Create New Visualization:**
   - Choose **"Table"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND context.response_time: *`

3. **Configure Rows:**
   - **Split rows by:** `context.operation_type.keyword`

4. **Configure Metrics:**
   - **Metric 1:** Count
   - **Metric 2:** Average of `context.response_time`
   - **Metric 3:** Max of `context.response_time`
   - **Metric 4:** Min of `context.response_time`

5. **Save:**
   - **Title:** `Response Time Analysis`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 7: Status Code Distribution (Heat Map)

**Purpose:** Visualize HTTP status codes over time

1. **Create New Visualization:**
   - Choose **"Heat map"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND context.status_code: *`

3. **Configure Axes:**
   - **Horizontal axis:** `@timestamp` (interval: 5 minutes)
   - **Vertical axis:** `context.status_code`
   - **Metric:** Count

4. **Save:**
   - **Title:** `Status Codes Heat Map`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

### Visualization 8: Recent Error Messages (Data Table)

**Purpose:** Display latest error messages with context

1. **Create New Visualization:**
   - Choose **"Table"**

2. **Configure Data:**
   - **Data view:** `Laravel Application Logs`
   - **Filter:** `log_source: "laravel-app" AND (level_name: "ERROR" OR level_name: "CRITICAL")`

3. **Configure Columns:**
   - **Column 1:** `@timestamp` (formatted as date)
   - **Column 2:** `level_name.keyword`
   - **Column 3:** `message` (top hit, size 1)
   - **Column 4:** `context.user_id`
   - **Column 5:** `context.error_type.keyword`

4. **Configure Settings:**
   - **Rows per page:** 10
   - **Sort by:** `@timestamp` descending

5. **Save:**
   - **Title:** `Recent Errors`
   - **Add to dashboard:** `Laravel Application Dashboard`

---

## Build Dashboard

### Create the Dashboard

1. **Navigate to Dashboard:**
   - **☰ menu** → **Analytics** → **Dashboard**

2. **Open Your Dashboard:**
   - If you created it while saving visualizations, it should be listed
   - Otherwise, click **"Create dashboard"**
   - **Title:** `Laravel Application Dashboard`

3. **Add All Visualizations:**
   - Click **"Add from library"**
   - Select all visualizations you created:
     - ✅ Log Levels Distribution
     - ✅ Logs Over Time
     - ✅ Top 10 Active Users
     - ✅ Operations Distribution
     - ✅ Error Count
     - ✅ Response Time Analysis
     - ✅ Status Codes Heat Map
     - ✅ Recent Errors

4. **Arrange Layout:**
   - Drag and resize panels to organize your dashboard
   - **Suggested layout:**
     ```
     +---------------------------+---------------------------+
     |   Log Levels Distribution |   Error Count (Metric)    |
     |   (Pie Chart - 50%)       |   (25%)                   |
     +---------------------------+---------------------------+
     |   Logs Over Time (Line Chart - Full Width)           |
     +-------------------------------------------------------+
     |   Operations Distribution |   Top 10 Active Users     |
     |   (Donut - 50%)           |   (Bar - 50%)             |
     +---------------------------+---------------------------+
     |   Response Time Analysis (Table - Full Width)        |
     +-------------------------------------------------------+
     |   Status Codes Heat Map (Full Width)                 |
     +-------------------------------------------------------+
     |   Recent Errors (Table - Full Width)                 |
     +-------------------------------------------------------+
     ```

5. **Configure Dashboard Settings:**
   - Click **"Edit"** (top-right)
   - **Options:**
     - Use margins between panels: Yes
     - Show panel titles: Yes
     - Time range: Last 24 hours
     - Refresh interval: 30 seconds (for real-time monitoring)

6. **Save Dashboard:**
   - Click **"Save"** (top-right)
   - **Title:** `Laravel Application Dashboard`
   - **Description:** `Monitoring dashboard for Laravel application logs with error tracking, user activity, and performance metrics`
   - Click **"Save"**

---

## KQL Query Examples

Use these Kibana Query Language (KQL) queries in the search bar for quick filtering:

### Basic Filters

**All Laravel logs:**
```kql
log_source: "laravel-app"
```

**Specific log level:**
```kql
log_source: "laravel-app" AND level_name: "ERROR"
```

**Multiple log levels:**
```kql
log_source: "laravel-app" AND (level_name: "ERROR" OR level_name: "CRITICAL")
```

**Exclude DEBUG logs:**
```kql
log_source: "laravel-app" AND NOT level_name: "DEBUG"
```

### User-Based Queries

**Logs for specific user:**
```kql
log_source: "laravel-app" AND context.user_id: 12345
```

**All user login actions:**
```kql
log_source: "laravel-app" AND context.action: "login"
```

**Failed login attempts:**
```kql
log_source: "laravel-app" AND context.action: "login" AND level_name: "ERROR"
```

### Operation-Based Queries

**All database INSERT operations:**
```kql
log_source: "laravel-app" AND context.operation_type: "INSERT"
```

**DELETE operations with errors:**
```kql
log_source: "laravel-app" AND context.operation_type: "DELETE" AND level_name: "ERROR"
```

### Performance Queries

**Slow requests (>1000ms):**
```kql
log_source: "laravel-app" AND context.response_time > 1000
```

**Requests with 500 status codes:**
```kql
log_source: "laravel-app" AND context.status_code >= 500
```

**Successful requests (2xx status):**
```kql
log_source: "laravel-app" AND context.status_code >= 200 AND context.status_code < 300
```

### Time-Based Queries

**Logs in the last hour:**
```kql
log_source: "laravel-app" AND @timestamp >= now-1h
```

**Errors in the last 15 minutes:**
```kql
log_source: "laravel-app" AND level_name: "ERROR" AND @timestamp >= now-15m
```

**Specific date range:**
```kql
log_source: "laravel-app" AND @timestamp >= "2026-01-16T00:00:00" AND @timestamp <= "2026-01-16T23:59:59"
```

### IP Address Queries

**Logs from specific IP:**
```kql
log_source: "laravel-app" AND context.ip_address: "192.168.1.100"
```

**Logs from IP range:**
```kql
log_source: "laravel-app" AND context.ip_address: "192.168.*"
```

### Session-Based Queries

**All logs for a session:**
```kql
log_source: "laravel-app" AND context.session_id: "abc123xyz"
```

### Complex Queries

**High-value operations with errors:**
```kql
log_source: "laravel-app" AND 
context.operation_type: ("INSERT" OR "DELETE") AND 
level_name: "ERROR" AND 
@timestamp >= now-1h
```

**User activity excluding health checks:**
```kql
log_source: "laravel-app" AND 
context.user_id: * AND 
NOT context.action: "health_check" AND 
level_name: ("INFO" OR "WARNING" OR "ERROR")
```

**Performance issues:**
```kql
log_source: "laravel-app" AND 
(context.response_time > 1000 OR context.status_code >= 500) AND 
@timestamp >= now-1h
```

---

## Advanced Features

### Create Alerts

1. **Navigate to Alerting:**
   - **☰ menu** → **Management** → **Stack Management**
   - Click **"Rules and Connectors"**

2. **Create Rule:**
   - Click **"Create rule"**
   - **Name:** `High Error Rate Alert`
   - **Rule type:** Elasticsearch query
   - **Index:** `kafka-logstash-logs-*`
   - **Query:**
     ```json
     {
       "query": {
         "bool": {
           "must": [
             {"match": {"log_source": "laravel-app"}},
             {"match": {"level_name": "ERROR"}}
           ]
         }
       }
     }
     ```
   - **Threshold:** Count > 10 in 5 minutes
   - **Action:** Send email, Slack notification, etc.

### Export/Import Dashboard

**Export:**
```bash
# Export dashboard configuration
curl -X GET "http://localhost:5601/api/saved_objects/_export" \
  -H "kbn-xsrf: true" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "dashboard",
    "includeReferencesDeep": true
  }' > laravel_dashboard.ndjson
```

**Import:**
1. **☰ menu** → **Management** → **Stack Management**
2. Click **"Saved Objects"**
3. Click **"Import"**
4. Select your `.ndjson` file
5. Click **"Import"**

---

## Troubleshooting

### No Data Showing in Kibana

**Check 1: Verify logs in Elasticsearch**
```bash
curl -s "http://localhost:9200/kafka-logstash-logs-*/_count?q=log_source:laravel-app" | jq .
```

**Check 2: Verify data view pattern**
- Management → Data Views → Check your pattern matches indices
- Try pattern: `kafka-logstash-logs-*`

**Check 3: Check time range**
- Extend time range to "Last 7 days" or "Last 30 days"
- Check if `@timestamp` field exists and has data

**Check 4: Refresh data view fields**
- Management → Data Views → Select your data view
- Click **"Refresh field list"** (⟳ icon)

### Visualizations Not Loading

**Issue 1: Field not found**
- Refresh data view fields
- Check field name (case-sensitive)
- Use `.keyword` suffix for text fields: `level_name.keyword`

**Issue 2: No data matches**
- Remove filters temporarily
- Check KQL syntax
- Verify time range includes data

**Issue 3: Performance issues**
- Reduce time range
- Add more specific filters
- Decrease visualization data points

### Dashboard Performance Optimization

**Tip 1: Use appropriate time ranges**
```
- Real-time monitoring: Last 15 minutes, refresh every 30s
- Daily review: Last 24 hours, refresh every 5 minutes
- Historical analysis: Last 7 days, no auto-refresh
```

**Tip 2: Limit data points**
- Bar/pie charts: Show top 10-20 items
- Tables: Limit to 50-100 rows
- Time series: Use appropriate intervals (1m, 5m, 1h)

**Tip 3: Use filters effectively**
- Always filter by `log_source: "laravel-app"`
- Add time range filters
- Filter out unnecessary log levels

---

## Next Steps

### 1. Generate Test Data
```bash
# Generate 100 test logs
curl "http://localhost:8000/api/logs/batch?count=100"

# Generate error logs
curl "http://localhost:8000/api/logs/errors?scenario=database"
curl "http://localhost:8000/api/logs/errors?scenario=api"

# Generate logs for specific user
curl "http://localhost:8000/api/logs/generate?user_id=999&action=purchase"
```

### 2. Monitor Real-Time
- Open your dashboard
- Set refresh interval to 10-30 seconds
- Generate logs via API
- Watch visualizations update in real-time

### 3. Create Custom Visualizations
- Experiment with different chart types
- Create visualizations for your specific use cases
- Share dashboards with your team

### 4. Set Up Alerts
- Configure email/Slack notifications
- Set thresholds for error rates
- Create alerts for performance issues

### 5. Explore Advanced Features
- **Canvas:** Create pixel-perfect presentations
- **Machine Learning:** Detect anomalies automatically
- **APM:** Add application performance monitoring
- **Uptime:** Monitor service availability

---

## Useful Resources

**Kibana Documentation:**
- https://www.elastic.co/guide/en/kibana/current/index.html

**KQL (Kibana Query Language):**
- https://www.elastic.co/guide/en/kibana/current/kuery-query.html

**Visualization Types:**
- https://www.elastic.co/guide/en/kibana/current/dashboard.html

**Laravel Logging:**
- https://laravel.com/docs/logging

---

## Summary

You now have:
- ✅ Data view configured for Laravel logs
- ✅ 8 different visualizations covering all key metrics
- ✅ Complete dashboard with real-time monitoring
- ✅ KQL query examples for quick filtering
- ✅ Performance optimization tips
- ✅ Troubleshooting guide

Your Laravel application logs are now fully integrated with Kibana for comprehensive monitoring and analysis! 🎉

---

**Need help?** Check the [main README](./README-LARAVEL-ELK.md) for additional information about the log generation API and pipeline architecture.
