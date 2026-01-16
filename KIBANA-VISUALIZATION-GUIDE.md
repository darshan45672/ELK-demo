# Kibana Visualization Guide for Next.js & Laravel Logs

Complete guide to visualizing application logs from Laravel and Next.js applications in Kibana.

---

## 📋 Table of Contents

1. [Access Kibana](#access-kibana)
2. [Create Data Views](#create-data-views)
3. [Explore Logs with Discover](#explore-logs-with-discover)
4. [Create Visualizations](#create-visualizations)
5. [Build Dashboards](#build-dashboards)
6. [Filters and Queries](#filters-and-queries)
7. [Alerts and Monitoring](#alerts-and-monitoring)

---

## 🌐 Access Kibana

### Step 1: Start Services

```bash
cd /path/to/ELK-demo
podman-compose up -d
```

Wait for all services to be healthy (30-60 seconds).

### Step 2: Open Kibana

Open your browser and navigate to:
```
http://localhost:5601
```

**First time setup:** Kibana will take 1-2 minutes to initialize.

---

## 📊 Create Data Views

Data Views (formerly Index Patterns) tell Kibana which Elasticsearch indices to query.

### Step 1: Navigate to Data Views

1. Click the **☰ Menu** (top left)
2. Go to **Management** → **Stack Management**
3. Click **Data Views** (under Kibana section)

### Step 2: Create Data View for All Logs

1. Click **Create data view** button
2. Fill in the details:

   **Name:** `Application Logs`
   
   **Index pattern:** `kafka-logstash-logs-*`
   
   **Timestamp field:** `@timestamp`
   
   **Custom data view ID:** `app-logs` (optional)

3. Click **Save data view to Kibana**

### Step 3: Verify Fields

After creation, you should see these key fields:

**Common Fields:**
- `@timestamp` - When the log was created
- `level` - Log level (INFO, ERROR, WARN, DEBUG)
- `message` - Log message
- `channel` - Application channel (nextjs, local, production)
- `log_source` - Source application (nextjs-app, todo-app)

**Next.js Specific:**
- `context.action` - Action being performed (fetch_todos, create_todo, etc.)
- `context.count` - Number of items
- `context.todo_id` - Todo identifier

**Laravel Specific:**
- `context.user_id` - User ID
- `context.route` - Route name
- `context.method` - HTTP method

---

## 🔍 Explore Logs with Discover

### Step 1: Open Discover

1. Click **☰ Menu**
2. Click **Discover** (under Analytics)

### Step 2: Select Data View

- In the top left, select **Application Logs** from the dropdown

### Step 3: Set Time Range

Click the **time picker** (top right):
- **Quick select:** Last 15 minutes, Last 1 hour, Today, etc.
- **Absolute:** Specific date/time range
- **Relative:** Last N minutes/hours/days

### Step 4: Add Columns

Click **+** next to fields to add them to the table:

**Recommended columns:**
- `@timestamp`
- `level`
- `log_source`
- `channel`
- `message`
- `context.action`

### Step 5: Filter Logs

#### Filter by Next.js Logs Only

Click **+ Add filter**:
- **Field:** `log_source.keyword`
- **Operator:** `is`
- **Value:** `nextjs-app`
- Click **Add filter**

#### Filter by Log Level

Click **+ Add filter**:
- **Field:** `level.keyword`
- **Operator:** `is`
- **Value:** `ERROR`
- Click **Add filter**

#### Filter by Action

Click **+ Add filter**:
- **Field:** `context.action.keyword`
- **Operator:** `is`
- **Value:** `create_todo`
- Click **Add filter**

### Step 6: Save Search

1. Click **Save** (top right)
2. Enter name: `Next.js Todo Actions`
3. Click **Save**

---

## 📈 Create Visualizations

### Visualization 1: Log Levels Distribution (Pie Chart)

**Purpose:** See distribution of INFO, ERROR, WARN logs

1. Go to **☰ Menu** → **Visualize Library**
2. Click **Create visualization**
3. Select **Pie**
4. Select data view: **Application Logs**

**Configuration:**

**Slice by:**
- **Aggregation:** Terms
- **Field:** `level.keyword`
- **Order:** Metric: Count (Descending)
- **Size:** 10

**Breakdown by (Optional):**
- **Aggregation:** Terms
- **Field:** `log_source.keyword`
- **Size:** 5

5. Click **Save**
6. Name: `Log Levels Distribution`

---

### Visualization 2: Logs Over Time (Area Chart)

**Purpose:** See log volume trends over time

1. **Create visualization** → **Area**
2. Select data view: **Application Logs**

**Configuration:**

**Horizontal axis:**
- **Aggregation:** Date Histogram
- **Field:** `@timestamp`
- **Minimum interval:** Auto

**Vertical axis:**
- **Aggregation:** Count

**Breakdown by:**
- **Aggregation:** Terms
- **Field:** `log_source.keyword`
- **Size:** 5

**Optional Filters:**
- Add filter: `level.keyword is ERROR` to show only errors

3. **Save** as `Logs Timeline`

---

### Visualization 3: Todo Actions Breakdown (Vertical Bar)

**Purpose:** See which todo actions are most frequent

1. **Create visualization** → **Vertical bar**
2. Select data view: **Application Logs**

**Filters:**
- Add filter: `log_source.keyword is nextjs-app`

**Configuration:**

**Horizontal axis:**
- **Aggregation:** Terms
- **Field:** `context.action.keyword`
- **Order:** Metric: Count (Descending)
- **Size:** 10

**Vertical axis:**
- **Aggregation:** Count

**Breakdown by (Optional):**
- **Aggregation:** Terms
- **Field:** `level.keyword`

3. **Save** as `Next.js Todo Actions`

---

### Visualization 4: Application Comparison (Horizontal Bar)

**Purpose:** Compare log volume between Laravel and Next.js

1. **Create visualization** → **Horizontal bar**
2. Select data view: **Application Logs**

**Configuration:**

**Vertical axis:**
- **Aggregation:** Terms
- **Field:** `log_source.keyword`
- **Order:** Metric: Count (Descending)

**Horizontal axis:**
- **Aggregation:** Count

**Breakdown by:**
- **Aggregation:** Terms
- **Field:** `level.keyword`
- **Size:** 5

3. **Save** as `Application Log Volume`

---

### Visualization 5: Error Rate Metric

**Purpose:** Show total error count

1. **Create visualization** → **Metric**
2. Select data view: **Application Logs**

**Filters:**
- Add filter: `level.keyword is ERROR`

**Configuration:**

**Metric:**
- **Aggregation:** Count
- **Custom label:** Error Count

3. **Save** as `Total Errors`

---

### Visualization 6: Top Error Messages (Table)

**Purpose:** See most common error messages

1. **Create visualization** → **Table**
2. Select data view: **Application Logs**

**Filters:**
- Add filter: `level.keyword is ERROR`

**Configuration:**

**Rows:**
- **Aggregation:** Terms
- **Field:** `message.keyword`
- **Order:** Metric: Count (Descending)
- **Size:** 10

**Metrics:**
- **Aggregation:** Count
- **Custom label:** Occurrences

**Add column:**
- **Aggregation:** Top Hit
- **Field:** `@timestamp`
- **Aggregate with:** Concatenate
- **Size:** 1
- **Custom label:** Last Seen

3. **Save** as `Top Errors`

---

### Visualization 7: Todo Operations Heatmap

**Purpose:** See todo operations by time of day

1. **Create visualization** → **Heat map**
2. Select data view: **Application Logs**

**Filters:**
- Add filter: `context.action.keyword exists`

**Configuration:**

**X-axis (Horizontal):**
- **Aggregation:** Date Histogram
- **Field:** `@timestamp`
- **Minimum interval:** 1 hour

**Y-axis (Vertical):**
- **Aggregation:** Terms
- **Field:** `context.action.keyword`
- **Size:** 10

**Cell intensity:**
- **Aggregation:** Count

3. **Save** as `Todo Operations Heatmap`

---

## 🎨 Build Dashboards

### Create Main Dashboard

1. Go to **☰ Menu** → **Dashboard**
2. Click **Create dashboard**
3. Click **Add from library**

### Add Visualizations

Select and add these visualizations:

1. **Log Levels Distribution** (Pie Chart) - Top Left
2. **Logs Timeline** (Area Chart) - Top Right (Wide)
3. **Application Log Volume** (Horizontal Bar) - Middle Left
4. **Next.js Todo Actions** (Vertical Bar) - Middle Right
5. **Total Errors** (Metric) - Bottom Left (Small)
6. **Top Errors** (Table) - Bottom Right (Wide)

### Arrange Layout

- **Drag** visualizations to rearrange
- **Resize** by dragging corners
- **Delete** by clicking ⋮ → Delete from dashboard

### Add Filters to Dashboard

Click **Add filter** at the top:

**Filter 1: Only show logs from last 24 hours**
- Time picker → Last 24 hours

**Filter 2: Exclude health check logs (optional)**
```
NOT message: "health check"
```

### Save Dashboard

1. Click **Save**
2. **Title:** `Application Logs Overview`
3. **Description:** `Comprehensive view of Laravel and Next.js application logs`
4. **Store time with dashboard:** ✅ Checked
5. Click **Save**

---

### Create Next.js Specific Dashboard

1. **Create dashboard**
2. **Add filter:**
   - `log_source.keyword is nextjs-app`
3. Add these visualizations:
   - Next.js Todo Actions
   - Logs Timeline (filtered for Next.js)
   - Todo Operations Heatmap
4. **Save** as `Next.js Todo App Monitoring`

---

## 🔎 Filters and Queries

### Using KQL (Kibana Query Language)

In the search bar at the top of Discover or Dashboard:

#### Basic Searches

**Search for specific text:**
```
message: "error"
```

**Search in specific field:**
```
log_source: "nextjs-app"
```

**Multiple conditions (AND):**
```
log_source: "nextjs-app" AND level: "ERROR"
```

**Multiple conditions (OR):**
```
level: "ERROR" OR level: "WARN"
```

**Exclude results (NOT):**
```
NOT level: "INFO"
```

**Field exists:**
```
context.action: *
```

**Field does not exist:**
```
NOT context.action: *
```

#### Advanced Queries

**Wildcard search:**
```
message: "*todo*"
```

**Range queries:**
```
context.count > 10
```

**Nested field search:**
```
context.action: "create_todo" AND context.count >= 1
```

**Combine multiple filters:**
```
log_source: "nextjs-app" AND (level: "ERROR" OR level: "WARN") AND @timestamp > "now-1h"
```

---

### Using Lucene Syntax

Switch to Lucene by clicking **KQL** toggle.

**Examples:**

```
message:error AND level:ERROR
```

```
log_source:nextjs-app AND context.action:create_todo
```

```
_exists_:context.todo_id
```

---

## 🚨 Alerts and Monitoring

### Create Alert for Errors

1. Go to **☰ Menu** → **Stack Management** → **Rules and Connectors**
2. Click **Create rule**

**Rule details:**
- **Name:** High Error Rate Alert
- **Check every:** 1 minute
- **Notify:** Only on status change

**Define rule:**
1. **Rule type:** Elasticsearch query
2. **Index:** `kafka-logstash-logs-*`
3. **Time field:** `@timestamp`
4. **Query:**
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
- **When:** count
- **Over:** all documents
- **IS ABOVE:** 10
- **For the last:** 5 minutes

5. **Actions:** Add connector (Email, Slack, Webhook, etc.)
6. **Save**

---

## 📊 Sample Queries for Common Use Cases

### 1. Find All Failed Todo Creations

```kql
log_source: "nextjs-app" AND context.action: "create_todo" AND level: "ERROR"
```

### 2. Track User Activity (Laravel)

```kql
log_source: "todo-app" AND context.user_id: *
```

### 3. Monitor Database Operations

```kql
message: "MongoDB" OR message: "database"
```

### 4. Find Slow Operations (if you add duration logging)

```kql
context.duration > 1000
```

### 5. Application Errors in Last Hour

```kql
level: "ERROR" AND @timestamp > "now-1h"
```

### 6. Compare Todo Operations

```kql
context.action: ("create_todo" OR "update_todo" OR "delete_todo")
```

---

## 🎯 Best Practices

### 1. **Use Time Filters**
Always set appropriate time ranges to improve query performance.

### 2. **Save Common Searches**
Save frequently used searches in Discover for quick access.

### 3. **Create Focused Dashboards**
- One dashboard per application/service
- One dashboard for errors/monitoring
- One dashboard for business metrics

### 4. **Use Field Filters**
Filter by `log_source.keyword` to separate Laravel and Next.js logs.

### 5. **Regular Maintenance**
- Archive old indices to save space
- Review and update visualizations monthly
- Adjust alert thresholds based on actual patterns

### 6. **Add Context to Logs**
Ensure your application logs include:
- Action/operation name
- User ID (if applicable)
- Request ID for tracing
- Timing/duration for performance monitoring

---

## 🔧 Troubleshooting

### No Data in Kibana

**Check:**
1. Are containers running? `podman ps`
2. Is Elasticsearch accessible? `curl http://localhost:9200/_cat/indices`
3. Are logs being generated? `podman logs js-todo-app`
4. Is Filebeat collecting logs? `podman logs filebeat`

### Visualization Not Showing Data

**Solutions:**
1. Check time range - expand to "Last 7 days"
2. Verify filters aren't excluding all data
3. Refresh field list: Data View → Refresh icon
4. Check if data exists: Go to Discover first

### Fields Not Appearing

**Solution:**
1. Go to **Data Views**
2. Select your data view
3. Click **Refresh field list** (🔄 icon)
4. Wait 30 seconds and refresh browser

---

## 📚 Quick Reference

### Log Sources

| log_source | channel | Description |
|------------|---------|-------------|
| `nextjs-app` | `nextjs` | Next.js Todo App |
| `todo-app` | `local` | Laravel Todo App |

### Log Levels

| Level | Purpose |
|-------|---------|
| `INFO` | Normal operations |
| `WARN` | Warning conditions |
| `ERROR` | Error conditions |
| `DEBUG` | Debugging information |

### Next.js Context Actions

| Action | Description |
|--------|-------------|
| `fetch_todos` | Retrieving todo list |
| `create_todo` | Creating new todo |
| `update_todo` | Updating existing todo |
| `toggle_todo` | Toggling completion status |
| `delete_todo` | Deleting todo |

---

## 🎓 Next Steps

1. **Explore Log Patterns:** Spend time in Discover to understand your data
2. **Build Custom Dashboards:** Create dashboards for specific use cases
3. **Set Up Alerts:** Configure alerts for critical errors
4. **Add More Context:** Enhance logging in your applications
5. **Archive Strategy:** Plan for log retention and archival
6. **Machine Learning:** Use Kibana ML features to detect anomalies

---

## 📖 Additional Resources

- **Kibana Guide:** https://www.elastic.co/guide/en/kibana/current/index.html
- **KQL Documentation:** https://www.elastic.co/guide/en/kibana/current/kuery-query.html
- **Visualizations:** https://www.elastic.co/guide/en/kibana/current/dashboard.html
- **Alerting:** https://www.elastic.co/guide/en/kibana/current/alerting-getting-started.html

---

**Happy Monitoring! 🎉**

Access Kibana: http://localhost:5601
