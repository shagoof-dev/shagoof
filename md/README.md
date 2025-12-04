# Multi-Country Sync Plugin

Synchronize products across multiple Shagoof instances (EG, UAE, SA).

## Installation

### Step 1: Plugin is already created in EG branch

The plugin files are located in: `platform/plugins/multi-country-sync/`

### Step 2: Run Composer Autoload

```bash
composer dump-autoload
```

### Step 3: Activate Plugin

1. Go to Admin Panel → Plugins
2. Find "Multi Country Sync"
3. Click "Activate"

### Step 4: Configure Settings via Admin Panel

**All configuration is now done through the Admin Panel!**

1. Go to **Admin Panel → Settings → Multi Country Sync**
2. Configure the following:
   - **Enable Multi Country Sync**: Turn on/off synchronization
   - **Current Country**: Select your country (EG, UAE, or SA)
   - **For each instance (UAE, SA, EG)**:
     - **API URL**: The production URL (e.g., `https://uae.shagoof.com`)
     - **API Key**: The API key for authentication
     - **Enable Sync**: Enable/disable sync for this instance
   - **Advanced Settings**:
     - Sync on Product Create/Update
     - Use Queue for background processing
     - Max Retries and Retry Delay

3. Click **Save** to apply settings

**Note**: Settings are stored in the database and can be changed anytime without editing `.env` files.

### Step 5: Generate API Keys

**API keys can now be generated directly from the Admin Panel!**

1. Go to **Admin Panel → Settings → Multi Country Sync**
2. For each instance (UAE, SA, EG):
   - Click the **key icon** (🔑) button next to the API Key field
   - Confirm the generation
   - The new API key will be generated and displayed
   - **Copy the key immediately** - it will be hidden after 5 seconds
   - Use the **eye icon** (👁️) to show/hide the key
3. Copy the generated key and paste it in the corresponding instance's settings on other servers

**Note**: Each time you generate a new key, the old one will be replaced. Make sure to update the key on all other instances.

### Step 6: Set Up Queue Worker

Since sync uses queues, set up queue workers on each server:

```bash
php artisan queue:work --queue=product-sync
```

Or use supervisor for production:

```ini
[program:shagoof-sync-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=product-sync --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/sync-worker.log
```

## How It Works

1. **Product Created/Updated** in one country (e.g., EG)
2. **Event Fired** (`CreatedContentEvent` or `UpdatedContentEvent`)
3. **SyncProductListener** catches the event
4. **ProductSyncService** prepares product data
5. **API Request** sent to other countries (UAE, SA)
6. **SyncController** receives request and creates/updates product
7. **Sync Log** recorded for monitoring

## Testing

1. Create a product in EG (eg.shagoof.com)
2. Check sync logs: Admin Panel → Multi-Country Sync → Logs
3. Verify product appears in UAE and SA
4. Update product in EG
5. Verify updates sync to other countries

## Monitoring

View sync logs in the database table: `multi_country_sync_logs`

Or create an admin panel to view logs (optional).

## Admin Panel Features

### Settings Page
- **Location**: Admin Panel → Settings → Multi Country Sync
- **Features**:
  - Enable/disable sync
  - Configure API URLs and keys for each instance
  - Test connection to verify API keys work
  - Advanced sync options

### Test Connection
- Use the "Test Connection" button in settings to verify API keys
- Tests connectivity to other instances
- Shows success/error messages

## Troubleshooting

### Sync Not Working

1. Check Admin Panel settings (Settings → Multi Country Sync)
2. Verify API keys are correct (use Test Connection button)
3. Check queue workers are running
4. Check sync logs for errors
5. Verify plugin is activated

### API Errors

1. Use "Test Connection" in settings to verify connectivity
2. Check API endpoints are accessible
3. Verify API keys are valid
4. Check network connectivity between servers
5. Review error messages in sync logs

## Configuration

Edit `config/sync.php` to customize:
- Fields to sync
- Relationships to sync
- Retry settings
- Queue settings

## Support

For issues or questions, check the main documentation:
- `MULTI_COUNTRY_SYNC_SOLUTION.md`
- `ECOMMERCE_PLUGIN_DOCUMENTATION.md`

