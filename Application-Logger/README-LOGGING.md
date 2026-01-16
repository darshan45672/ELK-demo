# Laravel Log Generator for ELK Stack

This Laravel application generates structured JSON logs for testing the ELK-Kafka pipeline.

## Features

- **JSON Formatted Logs**: All logs are output in JSON format for easy parsing by Logstash
- **Multiple Log Levels**: DEBUG, INFO, WARNING, ERROR, CRITICAL
- **Test Endpoints**: Simple API endpoints to generate logs on demand
- **Batch Generation**: Create multiple logs at once for testing high-volume scenarios
- **Error Scenarios**: Simulate various error conditions

## Configuration

The application uses a custom logging channel `elk_json` configured in `config/logging.php`:

```php
'elk_json' => [
    'driver' => 'daily',
    'path' => storage_path('logs/application.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,
    'tap' => [App\Logging\JsonLogFormatter::class],
]
```

## API Endpoints

### 1. Generate Single Log
```bash
# Basic log generation
curl "http://localhost:8000/api/logs/generate"

# With parameters
curl "http://localhost:8000/api/logs/generate?user_id=1234&action=login"
```

**Parameters:**
- `user_id` (optional): User ID for the log entry
- `action` (optional): Action being performed

**Response:**
```json
{
  "success": true,
  "message": "Logs generated successfully",
  "user_id": 1234,
  "action": "login",
  "logs_written": 3,
  "timestamp": "2026-01-16T10:30:00+00:00"
}
```

### 2. Batch Log Generation
```bash
# Generate 10 logs (default)
curl "http://localhost:8000/api/logs/batch"

# Generate 50 logs
curl "http://localhost:8000/api/logs/batch?count=50"
```

**Parameters:**
- `count` (optional): Number of logs to generate (max: 100, default: 10)

### 3. Error Scenarios
```bash
# Generate all error scenarios
curl "http://localhost:8000/api/logs/errors"

# Specific scenario
curl "http://localhost:8000/api/logs/errors?scenario=database"
curl "http://localhost:8000/api/logs/errors?scenario=api"
curl "http://localhost:8000/api/logs/errors?scenario=validation"
```

## Log Structure

Each log entry is formatted as JSON with the following structure:

```json
{
  "message": "User action completed",
  "context": {
    "user_id": 1234,
    "action": "login",
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "timestamp": "2026-01-16T10:30:00+00:00",
    "status": "success"
  },
  "level": 200,
  "level_name": "INFO",
  "channel": "elk_json",
  "datetime": "2026-01-16T10:30:00.123456+00:00",
  "extra": []
}
```

## Flow Diagram

```
Laravel App → storage/logs/application.log (JSON)
    ↓
Filebeat (reads JSON logs)
    ↓
Kafka (filebeat-logs topic)
    ↓
Logstash (parses and enriches)
    ↓
Elasticsearch (stores in kafka-logstash-logs-* index)
    ↓
Kibana (visualizes and analyzes)
```

## Testing the Pipeline

1. **Start all services:**
   ```bash
   podman-compose up -d
   ```

2. **Generate test logs:**
   ```bash
   # Single log
   curl "http://localhost:8000/api/logs/generate"
   
   # Batch of 50 logs
   curl "http://localhost:8000/api/logs/batch?count=50"
   
   # Error scenarios
   curl "http://localhost:8000/api/logs/errors"
   ```

3. **View in Kibana:**
   - Open http://localhost:5601
   - Create data view: `kafka-logstash-logs-*`
   - Filter by: `log_source: "laravel-app"`

4. **Query examples in Kibana:**
   ```kql
   # All Laravel logs
   log_source: "laravel-app"
   
   # Error logs only
   log_source: "laravel-app" and level_name: "ERROR"
   
   # Logs for specific user
   log_source: "laravel-app" and context.user_id: 1234
   
   # Slow queries
   log_source: "laravel-app" and message: *slow*
   ```

## Log Levels Distribution

The batch generator creates realistic log distributions:
- DEBUG: 30%
- INFO: 40%
- WARNING: 15%
- ERROR: 10%
- CRITICAL: 5%

## Development

**View logs locally:**
```bash
tail -f Application-Logger/storage/logs/application.log | jq .
```

**Check Filebeat is reading logs:**
```bash
podman logs -f filebeat
```

**Verify logs in Kafka:**
```bash
podman exec -it kafka kafka-console-consumer.sh \
  --bootstrap-server localhost:9092 \
  --topic filebeat-logs \
  --from-beginning
```

**Query Elasticsearch directly:**
```bash
curl "http://localhost:9200/kafka-logstash-logs-*/_search?q=log_source:laravel-app&size=5&pretty"
```

## Customization

### Add Custom Log Fields

Edit `LogTestController.php` and add fields to the log context:

```php
Log::info('Custom event', [
    'custom_field' => 'custom_value',
    'user_id' => 1234,
    // ... more fields
]);
```

### Change Log Format

Edit `app/Logging/JsonLogFormatter.php` to customize the JSON formatter:

```php
$handler->setFormatter(new JsonFormatter(
    JsonFormatter::BATCH_MODE_NEWLINES,
    true,  // appendNewline
    false, // ignoreEmptyContextAndExtra
    true   // includeStacktraces
));
```

## Troubleshooting

**Logs not appearing in Kibana:**
1. Check Laravel is writing logs: `ls -lh Application-Logger/storage/logs/`
2. Verify Filebeat is reading: `podman logs filebeat`
3. Check Kafka has messages: `podman exec -it kafka kafka-console-consumer.sh --bootstrap-server localhost:9092 --topic filebeat-logs --from-beginning`
4. Verify Logstash is processing: `podman logs logstash`
5. Query Elasticsearch: `curl "http://localhost:9200/_cat/indices?v"`

**Permission errors:**
```bash
# Fix Laravel storage permissions
chmod -R 775 Application-Logger/storage
```

**Logs not in JSON format:**
- Verify `LOG_CHANNEL=elk_json` in `.env`
- Check `config/logging.php` has the `elk_json` channel configured
- Clear config cache: `php artisan config:clear`
