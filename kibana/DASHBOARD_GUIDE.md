# Kibana Dashboard and Visualization Guide

This directory contains guides for creating dashboards, visualizations, and alerts in Kibana.

## Quick Setup

### 1. Create Index Pattern (First Time Setup)

1. Open Kibana: http://localhost:5601
2. Go to **Stack Management** → **Index Patterns**
3. Click **Create index pattern**
4. Enter pattern: `checkout-logs-*`
5. Select time field: `@timestamp`
6. Click **Create index pattern**

## Visualizations to Create

### 1. Error Trend Line Chart

**Purpose:** Track errors over time

1. Go to **Visualize Library** → **Create visualization**
2. Select **Line**
3. Select index pattern: `checkout-logs-*`
4. Configuration:
   - **X-axis**: Date Histogram on `@timestamp` (Auto interval)
   - **Y-axis**: Count
   - **Break down by**: Terms on `level.keyword` (Top 5)
5. Save as: "Error Trend Over Time"

### 2. Top Error Codes Bar Chart

**Purpose:** Identify most common errors

1. Create visualization → Select **Bar (Vertical)**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **X-axis**: Terms on `errorCode.keyword` (Top 10)
   - **Y-axis**: Count
   - Add filter: `level: ERROR`
4. Save as: "Top Error Codes"

### 3. Average Latency Metric

**Purpose:** Monitor performance

1. Create visualization → Select **Metric**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **Metric**: Average of `latencyMs`
4. Save as: "Average Latency"

### 4. Latency Distribution Histogram

**Purpose:** Understand latency patterns

1. Create visualization → Select **Histogram**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **X-axis**: Histogram on `latencyMs` (Interval: 200)
   - **Y-axis**: Count
4. Save as: "Latency Distribution"

### 5. Error Category Pie Chart

**Purpose:** Visualize error distribution by category

1. Create visualization → Select **Pie**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **Slice by**: Terms on `error_category.keyword`
   - Add filter: `has_error: true`
4. Save as: "Error Categories"

### 6. User Activity Table

**Purpose:** Track user-specific issues

1. Create visualization → Select **Data table**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **Rows**: Terms on `userId` (Top 10)
   - **Metrics**: 
     - Count
     - Average of `latencyMs`
   - Add split: Terms on `level.keyword`
4. Save as: "User Activity Summary"

### 7. Request Volume Over Time

**Purpose:** Monitor traffic patterns

1. Create visualization → Select **Area**
2. Select index pattern: `checkout-logs-*`
3. Configuration:
   - **X-axis**: Date Histogram on `@timestamp` (Auto)
   - **Y-axis**: Count
4. Save as: "Request Volume"

## Dashboard Creation

### Checkout Monitoring Dashboard

1. Go to **Dashboard** → **Create dashboard**
2. Click **Add from library**
3. Add all created visualizations:
   - Error Trend Over Time
   - Top Error Codes
   - Average Latency
   - Latency Distribution
   - Error Categories
   - User Activity Summary
   - Request Volume
4. Arrange them in a logical layout
5. Save as: "Checkout Service Monitoring"

**Recommended Layout:**
```
┌─────────────────────────────────────────────────┐
│  Request Volume Over Time (full width)          │
├──────────────────┬──────────────────────────────┤
│ Average Latency  │  Error Trend Over Time       │
├──────────────────┼──────────────────────────────┤
│ Top Error Codes  │  Latency Distribution        │
├──────────────────┴──────────────────────────────┤
│  Error Categories (pie)                         │
├─────────────────────────────────────────────────┤
│  User Activity Summary (table)                  │
└─────────────────────────────────────────────────┘
```

## Alerting Rules

### 1. High Error Rate Alert

1. Go to **Stack Management** → **Rules and Connectors**
2. Click **Create rule**
3. Configuration:
   - **Name**: "High Error Rate - Checkout Service"
   - **Check every**: 1 minute
   - **Rule type**: Elasticsearch query
   - **Index**: `checkout-logs-*`
   - **Query**: 
     ```json
     {
       "query": {
         "bool": {
           "must": [
             { "match": { "level": "ERROR" } }
           ]
         }
       }
     }
     ```
   - **When**: count is ABOVE 2
   - **Over**: last 5 minutes
4. **Actions**: 
   - Add action: "Log" (for demo)
   - Message: "⚠️ High error rate detected: {{context.hits}} errors in 5 minutes"

### 2. High Latency Alert

1. Create another rule
2. Configuration:
   - **Name**: "High Latency Alert"
   - **Rule type**: Elasticsearch query
   - **Index**: `checkout-logs-*`
   - **Query**:
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
   - **When**: count is ABOVE 3
   - **Over**: last 5 minutes
3. **Actions**: Log action

### 3. Payment Failure Spike

1. Create rule
2. Configuration:
   - **Name**: "Payment Failure Spike"
   - **Rule type**: Elasticsearch query
   - **Index**: `checkout-logs-*`
   - **Query**:
     ```json
     {
       "query": {
         "match": {
           "errorCode": "PAYMENT_FAILED"
         }
       }
     }
     ```
   - **When**: count is ABOVE 2
   - **Over**: last 5 minutes

## Discover Queries

### Useful KQL Queries for Demo

1. **All errors**: 
   ```
   level: ERROR
   ```

2. **High latency requests**:
   ```
   latencyMs > 500
   ```

3. **Payment failures**:
   ```
   errorCode: PAYMENT_FAILED
   ```

4. **Specific user's errors**:
   ```
   userId: 42 AND level: ERROR
   ```

5. **High latency errors**:
   ```
   level: ERROR AND latencyMs > 1000
   ```

6. **Recent timeouts**:
   ```
   errorCode: TIMEOUT AND @timestamp >= now-5m
   ```

7. **All issues (WARN + ERROR)**:
   ```
   level: (ERROR OR WARN)
   ```

## Filter Combinations for Troubleshooting

### Scenario 1: Investigating Payment Issues
```
Filters:
- errorCode: PAYMENT_FAILED
- Time range: Last 15 minutes
- Sort by: @timestamp (descending)
```

### Scenario 2: Performance Investigation
```
Filters:
- latencyMs > 800
- Time range: Last hour
- Breakdown by: userId
```

### Scenario 3: User-Specific Issues
```
Filters:
- userId: 42
- level: ERROR OR WARN
- Time range: Today
```

## Demo Tips

1. **Start with Discover** - Show raw logs first
2. **Build visualizations live** - Don't pre-create everything
3. **Use filters interactively** - Click on fields to add filters
4. **Show query performance** - Mention index patterns
5. **Demonstrate drill-down** - Click on chart elements to filter
6. **Time picker tricks** - Show relative time ranges
7. **Save searches** - Demonstrate saving queries for reuse

## Advanced Features (Optional)

- **Lens**: Drag-and-drop visualization builder
- **Canvas**: Pixel-perfect dashboard creation
- **Maps**: Geographic visualization (if you add location data)
- **Machine Learning**: Anomaly detection on latency
- **TSVB**: Time Series Visual Builder for advanced time series

## Keyboard Shortcuts in Kibana

- `/` - Focus search bar
- `Ctrl/Cmd + /` - Open shortcuts help
- `Ctrl/Cmd + P` - Quick navigation
