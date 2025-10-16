# Slideshow Manager Logging

Slideshow Manager uses the [LindemannRock Logging Library](https://github.com/LindemannRock/craft-logging-library) for centralized, structured logging across all LindemannRock plugins.

## Log Levels

- **Error**: Critical errors only (default)
- **Warning**: Errors and warnings
- **Info**: General information
- **Debug**: Detailed debugging (includes performance metrics, requires devMode)

## Configuration

### Control Panel

1. Navigate to **Settings → Slideshow Manager → General**
2. Scroll to **Logging Settings**
3. Select desired log level from dropdown
4. Click **Save**

### Config File

```php
// config/slideshow-manager.php
return [
    'logLevel' => 'error', // error, warning, info, or debug
];
```

**Note:** Debug level requires Craft's `devMode` to be enabled. If set to debug with devMode disabled, it automatically falls back to info level.

## Log Files

- **Location**: `storage/logs/slideshow-manager-YYYY-MM-DD.log`
- **Retention**: 30 days (automatic cleanup via Logging Library)
- **Format**: Structured JSON logs with context data
- **Web Interface**: View and filter logs in CP at Slideshow Manager → Logs

## What's Logged

The plugin logs meaningful events using context arrays for structured data. All logs include user context when available.

### Settings Operations (SettingsController)

- **[INFO]** `Settings save requested` - When user initiates settings save
  - Context: `userId`, `fields` (list of changed fields)
- **[WARNING]** `Settings validation failed` - When settings fail validation
  - Context: `errors` (validation error details)
- **[ERROR]** `Database save failed` - When database update fails
- **[INFO]** `Settings saved successfully` - When settings save completes
  - Context: `userId`

### Settings Model (Settings)

- **[WARNING]** `Log level "debug" from config file changed to "info"` - When debug level used without devMode (config override)
- **[WARNING]** `Log level automatically changed from "debug" to "info"` - When debug level auto-corrected (DB setting)
- **[ERROR]** `Failed to load settings from database` - Database query errors
  - Context: `error` (exception message)
- **[WARNING]** `No settings found in database` - Missing settings record
- **[ERROR]** `Settings validation failed` - Settings model validation errors
  - Context: `errors` (validation errors array)
- **[DEBUG]** `Saving settings to database` - Database save operation details
  - Context: `fields` (list of fields being saved)
- **[ERROR]** `Settings save failed` - Database save errors
  - Context: `error` (exception message)

### Field Operations (SlideshowConfigField)

- **[WARNING]** `Invalid config JSON` - When field contains malformed JSON
  - Context: `value` (invalid JSON string), `elementId`

**Note:** The field's `normalizeValue()` method is called multiple times per request by Craft (for display, validation, preview, etc.), so normal operations are not logged to prevent log flooding. Only actual errors are logged.

## Log Management

### Via Control Panel

1. Navigate to **Slideshow Manager → Logs**
2. Filter by date, level, or search terms
3. Download log files for external analysis
4. View file sizes and entry counts
5. Auto-cleanup after 30 days (configurable via Logging Library)

### Via Command Line

**View today's log**:

```bash
tail -f storage/logs/slideshow-manager-$(date +%Y-%m-%d).log
```

**View specific date**:

```bash
cat storage/logs/slideshow-manager-2025-01-15.log
```

**Search across all logs**:

```bash
grep "Settings save" storage/logs/slideshow-manager-*.log
```

**Filter by log level**:

```bash
grep "\[ERROR\]" storage/logs/slideshow-manager-*.log
```

## Log Format

Each log entry follows structured JSON format with context data:

```json
{
  "timestamp": "2025-01-15 14:30:45",
  "level": "INFO",
  "message": "Settings saved successfully",
  "context": {
    "userId": 1,
    "fields": ["defaultProvider", "autoplay"]
  },
  "category": "lindemannrock\\slideshowmanager\\controllers\\SettingsController"
}
```

## Using the Logging Trait

All services and controllers in Slideshow Manager use the `LoggingTrait` from the LindemannRock Logging Library:

```php
use lindemannrock\logginglibrary\traits\LoggingTrait;

class MyService extends Component
{
    use LoggingTrait;

    public function myMethod()
    {
        // Info level - general operations
        $this->logInfo('Operation started', ['param' => $value]);

        // Warning level - important but non-critical
        $this->logWarning('Missing data', ['key' => $missingKey]);

        // Error level - failures and exceptions
        $this->logError('Operation failed', ['error' => $e->getMessage()]);

        // Debug level - detailed information
        $this->logDebug('Processing item', ['item' => $itemData]);
    }
}
```

## Performance Considerations

- **Error/Warning levels**: Minimal performance impact, suitable for production
- **Info level**: Moderate logging, useful for tracking operations
- **Debug level**: Extensive logging, use only in development (requires devMode)
  - Includes performance metrics
  - Logs database operations
  - Tracks field operations

## Requirements

Slideshow Manager logging requires:

- **lindemannrock/logginglibrary** plugin (installed automatically as dependency)
- Write permissions on `storage/logs` directory
- Craft CMS 5.x or later

## Troubleshooting

If logs aren't appearing:

1. **Check permissions**: Verify `storage/logs` directory is writable
2. **Verify library**: Ensure LindemannRock Logging Library is installed and enabled
3. **Check log level**: Confirm log level allows the messages you're looking for
4. **devMode for debug**: Debug level requires `devMode` enabled in `config/general.php`
5. **Check CP interface**: Use Slideshow Manager → Logs to verify log files exist

## Common Scenarios

### Settings Save Issues

When settings fail to save, check for:

```bash
grep "Settings" storage/logs/slideshow-manager-*.log
```

Look for:
- `Settings validation failed` - Check validation errors in context
- `Database save failed` - Check database connectivity
- `Settings save failed` - Database write errors

### Field Configuration Problems

Debug field configuration issues:

```bash
grep "Invalid config JSON" storage/logs/slideshow-manager-*.log
```

Common issues:
- Malformed JSON in field configuration
- Missing required properties
- Invalid slideshow provider settings

### Log Level Changes

Monitor automatic log level adjustments:

```bash
grep "Log level" storage/logs/slideshow-manager-*.log
```

When debug level is used without devMode enabled, the system automatically falls back to info level and logs a warning.

## Development Tips

### Enable Debug Logging

For detailed troubleshooting during development:

```php
// config/slideshow-manager.php
return [
    'dev' => [
        'logLevel' => 'debug',
    ],
];
```

This provides:
- Detailed database operations
- Field save operations
- Settings changes with full context

### Monitor Specific Operations

Track specific operations using grep:

```bash
# Monitor all settings operations
grep "Settings" storage/logs/slideshow-manager-*.log

# Watch logs in real-time
tail -f storage/logs/slideshow-manager-$(date +%Y-%m-%d).log

# Check all errors
grep "\[ERROR\]" storage/logs/slideshow-manager-*.log
```

### Field Operation Logging

The Slideshow Config Field is designed to minimize log noise:

- **Normal operations**: Not logged (prevents flooding from Craft's multiple `normalizeValue()` calls)
- **Errors only**: Logged when JSON is invalid or operations fail
- **Context included**: Element ID and invalid value for debugging

This approach ensures logs remain useful without being overwhelmed by routine field operations.
