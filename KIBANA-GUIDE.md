# Kibana Visualization Guide

Complete guide to using Kibana for log visualization and analytics in the ELK-Kafka stack.

## Table of Contents

- [Getting Started](#getting-started)
- [Create Data View](#create-data-view)
- [Discover: Explore Logs](#discover-explore-logs)
- [Visualizations](#visualizations)
- [Dashboards](#dashboards)
- [Advanced Features](#advanced-features)
- [Tips & Best Practices](#tips--best-practices)

---

## Getting Started

### Access Kibana

Once all services are running, open your browser:

**URL**: http://localhost:5601

You'll see the Kibana welcome screen. No authentication is needed in this development setup.

### Navigation

Kibana's main menu (hamburger icon ☰) contains:
- **Analytics**: Discover, Visualize, Dashboard, Canvas
- **Management**: Stack Management, Dev Tools, Alerts
- **Observability**: Logs, Metrics, APM, Uptime

---

## Create Data View

Data Views (formerly called Index Patterns) tell Kibana which Elasticsearch indices to query.

### Method 1: Via UI

1. **Open Stack Management**:
   - Click hamburger menu (☰)
   - Go to **Management** → **Stack Management**
   - Click **Kibana** → **Data Views**

2. **Create New Data View**:
   - Click **"Create data view"** button
   - Fill in the form:
     ```
     Name: Kafka Logs
     Index pattern: kafka-logstash-logs-*
     Timestamp field: @timestamp
     ```
   - Click **"Save data view to Kibana"**

### Method 2: Via API

```bash
curl -X POST "http://localhost:5601/api/data_views/data_view" \
  -H 'Content-Type: application/json' \
  -H 'kbn-xsrf: true' \
  -d '{
  "data_view": {
    "title": "kafka-logstash-logs-*",
    "name": "Kafka Logs",
    "timeFieldName": "@timestamp"
  }
}'
```

### Method 3: Via Dev Tools

1. Open **Management** → **Dev Tools** → **Console**
2. Paste and run:
   ```
   POST /api/data_views/data_view
   {
     "data_view": {
       "title": "kafka-logstash-logs-*",
       "name": "Kafka Logs",
       "timeFieldName": "@timestamp"
     }
   }
   ```

---

## Discover: Explore Logs

Discover is Kibana's log exploration interface - like a supercharged log viewer.

### Open Discover

1. Click hamburger menu (☰)
2. Select **Analytics** → **Discover**
3. Choose **"Kafka Logs"** data view from dropdown (top left)

### Interface Overview

```
┌─────────────────────────────────────────────────────────┐
│ [Kafka Logs ▼]  [Search Bar (KQL)]     [🕐 Last 15 min] │
├─────────────────────────────────────────────────────────┤
│ Fields Sidebar  │  Timeline Chart                       │
│                 │  ┌──────────────────────────────┐    │
│ 📊 Selected     │  │  █ █  █ █ ██ █               │    │
│ 🔍 Available    │  └──────────────────────────────┘    │
│                 │                                       │
│ app.level       │  Log Entry 1  [@timestamp]           │
│ app.service     │  { expanded JSON fields }            │
│ app.message     │                                       │
│ app.duration_ms │  Log Entry 2  [@timestamp]           │
│ ...             │  { collapsed }                       │
└─────────────────────────────────────────────────────────┘
```

### Time Range Selector

Top right corner - click to change time range:
- **Quick ranges**: Last 15 minutes, 1 hour, 24 hours, 7 days
- **Relative**: Last N minutes/hours/days
- **Absolute**: Specific start and end times
- **Refresh**: Auto-refresh every N seconds

**Common selections:**
```
Last 15 minutes  ← Default
Last 1 hour      ← Recent troubleshooting
Last 24 hours    ← Daily overview
Last 7 days      ← Weekly trends
Today            ← Current day's logs
```

### Search with KQL (Kibana Query Language)

The search bar accepts KQL syntax:

#### Basic Searches

```kql
# Exact match
app.level: "ERROR"

# Wildcard
app.message: *timeout*

# Number comparison
app.duration_ms > 2000

# Range
app.status_code >= 500 AND app.status_code < 600

# Exists (field has a value)
app.user_id: *
```

#### Logical Operators

```kql
# AND
app.level: "ERROR" AND app.service: "payment-service"

# OR
app.level: ("ERROR" OR "WARNING")

# NOT
NOT app.level: "DEBUG"

# Combine
app.level: "ERROR" AND NOT app.service: "health-check"
```

#### Advanced Examples

```kql
# Errors in payment or order service
app.level: "ERROR" AND app.service: ("payment-service" OR "order-service")

# Slow requests that resulted in errors
app.duration_ms > 2000 AND app.status_code >= 500

# Find specific user's actions
app.user_id: "user-12345"

# Logs without a user_id
NOT app.user_id: *

# Messages containing specific text
app.message: (*failed* OR *error* OR *exception*)
```

### Filter by Field Values

**Quick filters:**
1. Hover over a field value in the log details
2. Click **+** to filter FOR that value
3. Click **-** to filter OUT that value

**Example workflow:**
1. Find an error log
2. Click **+** next to `app.service: "payment-service"`
3. Now you see only errors from payment service
4. Remove filter by clicking **x** on the filter pill

### Add/Remove Columns

**Default view**: Shows `@timestamp` and full document

**Add specific fields:**
1. Find field in left sidebar (e.g., `app.level`)
2. Hover and click **"+"** icon
3. Field appears as a column

**Recommended columns for our logs:**
- `@timestamp`
- `app.level`
- `app.service`
- `app.message`
- `app.duration_ms`
- `app.status_code`

### Save Searches

1. Click **"Save"** button (top right)
2. Name: e.g., "Payment Service Errors"
3. Save
4. Access later from **"Open"** → **"Saved searches"**

---

## Visualizations

Create charts, graphs, and metrics from your log data.

### Create New Visualization

1. **Open Visualize Library**:
   - Hamburger menu (☰) → **Analytics** → **Visualize Library**
   - Click **"Create visualization"**

2. **Choose Type**:
   - Bar/Line/Area charts
   - Pie/Donut charts
   - Data tables
   - Metrics
   - Tag clouds
   - Heat maps

### Example 1: Logs by Level (Pie Chart)

**Purpose**: See distribution of log levels (INFO, ERROR, WARNING, DEBUG)

1. Select **Pie** chart
2. Choose data view: **Kafka Logs**
3. Click **"Add field"** under Slice by
4. Select **"app.level"** (use keyword field)
5. Top values: 10
6. Preview updates automatically
7. **Customize**:
   - Click "Settings" to change colors
   - Add labels with percentages
8. Click **"Save and return"** or **"Save to library"**
9. Name: "Logs by Level Distribution"

### Example 2: Error Rate Over Time (Line Chart)

**Purpose**: Track errors over time to spot incidents

1. Select **Line** chart
2. Choose data view: **Kafka Logs**
3. **Vertical axis** (already set to Count)
4. **Horizontal axis**: @timestamp (automatic)
5. **Add filter** to show only errors:
   - Click "Add filter"
   - Field: `app.level`
   - Operator: `is`
   - Value: `ERROR`
6. **Breakdown**:
   - Click "Break down by" → **app.service.keyword**
   - Shows errors per service
7. Save as "Error Rate Timeline"

### Example 3: Top Services by Log Volume (Bar Chart)

**Purpose**: Which services are logging the most

1. Select **Bar vertical** chart
2. **Vertical axis**: Count
3. **Horizontal axis**:
   - Click "Add field"
   - Select **app.service.keyword**
   - Order by: Metric (count) descending
   - Size: 10
4. Save as "Top Services by Volume"

### Example 4: Average Response Time (Metric)

**Purpose**: Big number showing average duration

1. Select **Metric** visualization
2. **Metric**:
   - Aggregation: Average
   - Field: **app.duration_ms**
3. **Customize**:
   - Add subtitle: "milliseconds"
   - Change format to number with 2 decimals
4. Save as "Average Response Time"

### Example 5: Service Performance Table (Data Table)

**Purpose**: Detailed breakdown of metrics per service

1. Select **Table**
2. **Metrics** (columns):
   - Count
   - Average of `app.duration_ms`
   - Max of `app.duration_ms`
3. **Rows**:
   - Terms aggregation
   - Field: **app.service.keyword**
   - Order by: Count descending
   - Size: 20
4. **Add filters** (optional):
   - Only show services with >100 logs
5. Save as "Service Performance Table"

### Example 6: Status Code Distribution (Donut Chart)

**Purpose**: HTTP status codes breakdown

1. Select **Donut** chart
2. **Slice by**: **app.status_code**
3. Top values: 10
4. **Color by**: Status code ranges
   - 2xx: Green
   - 3xx: Blue
   - 4xx: Yellow
   - 5xx: Red
5. Save as "Status Code Distribution"

### Example 7: Slow Requests Over Time (Area Chart)

**Purpose**: Visualize slow requests (>2 seconds)

1. Select **Area** chart
2. **Add filter**:
   - Field: `app.duration_ms`
   - Operator: `is greater than`
   - Value: `2000`
3. **Horizontal axis**: @timestamp
4. **Breakdown by**: app.service.keyword
5. **Stacked**: Yes (shows cumulative)
6. Save as "Slow Requests Timeline"

---

## Dashboards

Combine multiple visualizations into a single view.

### Create Dashboard

1. **Open Dashboards**:
   - Hamburger menu (☰) → **Analytics** → **Dashboard**
   - Click **"Create dashboard"**

2. **Add Visualizations**:
   - Click **"Add from library"**
   - Select saved visualizations
   - Click on each to add to dashboard

3. **Arrange Layout**:
   - Drag visualizations to reorder
   - Resize by dragging corners
   - Panels snap to grid

### Example Dashboard: "Kafka Logs Overview"

**Layout:**
```
┌─────────────────────────────────────────────────────┐
│  Total Logs        │  Error Count   │  Avg Response  │
│  (Metric)          │  (Metric)      │  (Metric)      │
├─────────────────────────────────────────────────────┤
│  Error Rate Timeline (Line Chart)                   │
│  (filtered to show only errors over time)           │
├─────────────────────────────────────────────────────┤
│  Logs by Level     │  Top Services by Volume        │
│  (Pie Chart)       │  (Bar Chart)                   │
├─────────────────────────────────────────────────────┤
│  Service Performance Table                          │
│  (shows count, avg duration, max duration)          │
└─────────────────────────────────────────────────────┘
```

**To create:**
1. Add visualizations in this order
2. Arrange top row: 3 metrics side by side
3. Full-width line chart below
4. Two medium charts side by side
5. Full-width table at bottom

### Dashboard Controls

**Time range**: Applies to entire dashboard (top right)

**Filters**: 
- Click "Add filter" to filter all panels
- Example: Filter to specific service

**Refresh**:
- Click refresh icon
- Set auto-refresh interval (e.g., every 30 seconds)

**Share**:
- Click "Share" → "Copy link"
- Share with team members

**Edit mode**:
- Click "Edit" to modify layout
- Click "Save" when done

### Example Dashboard: "Service Health Monitor"

**Purpose**: Monitor specific service health

1. **Top row metrics**:
   - Total requests (last 15 min)
   - Error rate percentage
   - Average response time
   - 95th percentile response time

2. **Middle section**:
   - Line chart: Requests over time (colored by status code)
   - Bar chart: Errors by endpoint

3. **Bottom section**:
   - Table: Slowest requests (show message, duration, user_id)
   - Tag cloud: Most common error messages

---

## Advanced Features

### Alerts and Actions

Set up alerts when conditions are met (e.g., error rate spike).

1. **Create Alert**:
   - Hamburger menu → **Management** → **Stack Management**
   - **Alerts and Insights** → **Rules**
   - Click **"Create rule"**

2. **Example: High Error Rate Alert**:
   - Rule type: **Elasticsearch query**
   - Index: `kafka-logstash-logs-*`
   - Query: `app.level: "ERROR"`
   - Threshold: More than 100 in 5 minutes
   - Action: Log to console / Send email / Webhook

### Lens (Drag-and-Drop Visualizations)

Lens is a simplified way to create visualizations.

1. Open **Visualize Library** → **Create visualization**
2. Select **Lens**
3. Drag fields from left sidebar to canvas
4. Kibana suggests appropriate chart types
5. Faster than traditional visualization editor

### Canvas (Infographic-Style Reports)

Create pixel-perfect reports and presentations.

1. Hamburger menu → **Analytics** → **Canvas**
2. Drag elements: Charts, images, text, shapes
3. More design-focused than dashboards
4. Good for executive reports

### Dev Tools Console

Direct access to Elasticsearch API.

1. **Open Console**:
   - Hamburger menu → **Management** → **Dev Tools**

2. **Example queries**:
   ```
   # Search logs
   GET kafka-logstash-logs-*/_search
   {
     "query": {
       "match": {
         "app.level": "ERROR"
       }
     }
   }
   
   # Get index stats
   GET kafka-logstash-logs-*/_stats
   
   # View mapping
   GET kafka-logstash-logs-*/_mapping
   ```

---

## Tips & Best Practices

### Performance Optimization

1. **Use filters instead of queries when possible**:
   - Filters are cached and faster
   - Queries are scored (slower)

2. **Limit time ranges**:
   - Don't query "All time" unless needed
   - Use relative time ranges (Last 24 hours)

3. **Use keyword fields for aggregations**:
   - `app.service.keyword` (fast)
   - NOT `app.service` (slow, analyzed)

4. **Limit aggregation size**:
   - Top 10-20 instead of 100s
   - Use pagination for large tables

### Query Best Practices

1. **Be specific**:
   ```kql
   # Good
   app.level: "ERROR" AND app.service: "payment-service"
   
   # Avoid (too broad)
   *error*
   ```

2. **Use filters for exact matches**:
   - Filter pills are faster than KQL queries
   - Combine multiple filters with AND logic

3. **Save common searches**:
   - Don't rebuild complex queries
   - Save and reuse

### Dashboard Design

1. **Most important metrics on top**:
   - Big numbers first (counts, rates)
   - Details below

2. **Use consistent time ranges**:
   - All panels should use dashboard time
   - Avoid panel-specific time overrides

3. **Group related visualizations**:
   - Use sections or colors
   - Add markdown panels for context

4. **Name clearly**:
   - "Payment Service Errors" not "Chart 1"
   - Add descriptions

### Field Naming Conventions

Our logs use nested structure under `app.*`:

```json
{
  "@timestamp": "2026-01-16T10:30:00.000Z",
  "app": {
    "level": "ERROR",
    "service": "payment-service",
    "message": "Payment failed",
    "user_id": "user-123",
    "duration_ms": 1500,
    "status_code": 500
  }
}
```

**Always use**:
- `app.service.keyword` for aggregations (not `app.service`)
- `@timestamp` for time-based operations
- Numeric fields (`app.duration_ms`) for math (avg, sum, max)

### Common Pitfalls

1. **"No results" in Discover**:
   - Check time range (expand to Last 24 hours)
   - Verify data exists: `curl localhost:9200/kafka-logstash-logs-*/_count`
   - Check data view matches index name

2. **Visualization shows no data**:
   - Verify filters aren't too restrictive
   - Check data view selected
   - Expand time range

3. **Slow dashboard loading**:
   - Reduce number of panels (max 10-12)
   - Limit time range to needed period
   - Use sampling for huge datasets

4. **Field not available in dropdown**:
   - Refresh field list: Data View → Refresh icon
   - Check if field is mapped correctly
   - Use Dev Tools to verify field exists

---

## Quick Reference

### Keyboard Shortcuts

- **`/`**: Focus search bar
- **`Ctrl/Cmd + K`**: Open command palette
- **`Ctrl/Cmd + S`**: Save
- **`Esc`**: Close modals

### Common KQL Patterns

```kql
# Text search
app.message: "payment failed"

# Wildcard
app.message: *timeout*

# Multiple values
app.level: ("ERROR" OR "WARNING")

# Range
app.duration_ms >= 1000 AND app.duration_ms < 5000

# Exists
app.user_id: *

# Not exists
NOT app.user_id: *

# Combine
app.service: "payment-service" AND app.level: "ERROR"
```

### Time Range Shortcuts

- **Quick**: Last 15m, 1h, 24h, 7d, 30d
- **Relative**: `now-1h` to `now`
- **Absolute**: Specific dates
- **Refresh**: Auto-refresh (10s, 30s, 1m)

### Field Types in Kibana

- **text**: Full-text search (analyzed)
- **keyword**: Exact match, aggregations
- **date**: Timestamps (@timestamp)
- **long/double**: Numbers (for math)
- **boolean**: true/false
- **ip**: IP addresses
- **geo_point**: Lat/lon coordinates

---

## Next Steps

1. **Explore Your Data**:
   - Open Discover
   - Browse recent logs
   - Try KQL queries

2. **Create Your First Visualization**:
   - Start with a simple pie chart
   - Show log level distribution

3. **Build a Dashboard**:
   - Combine 3-5 visualizations
   - Monitor your logs in real-time

4. **Set Up Alerts**:
   - Get notified of error spikes
   - Proactive monitoring

5. **Learn More**:
   - [Kibana Official Guide](https://www.elastic.co/guide/en/kibana/current/index.html)
   - [KQL Syntax Reference](https://www.elastic.co/guide/en/kibana/current/kuery-query.html)
   - [Visualization Types](https://www.elastic.co/guide/en/kibana/current/dashboard.html)

---

## Troubleshooting

### Kibana won't start

```bash
# Check Kibana logs
podman logs kibana

# Verify Elasticsearch is running
curl http://localhost:9200

# Restart Kibana
podman-compose restart kibana
```

### Can't create data view

```bash
# Verify indices exist
curl 'http://localhost:9200/_cat/indices/kafka-*?v'

# Check if data exists
curl 'http://localhost:9200/kafka-logstash-logs-*/_count'

# Refresh Kibana
# In browser: Settings → Advanced Settings → Refresh
```

### Visualization shows "No results found"

1. Expand time range to "Last 7 days"
2. Remove all filters
3. Check data exists in Discover first
4. Verify field names match exactly

### Dashboard is slow

1. Reduce time range to "Last 15 minutes"
2. Limit number of panels (< 10)
3. Use filters instead of complex queries
4. Reduce aggregation sizes (top 10 vs top 100)

---

**Happy Visualizing! 📊**
