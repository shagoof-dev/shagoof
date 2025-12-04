# Multi-Country Sync Plugin - Installation Guide

## Quick Start

### Step 1: Plugin is Already Created
The plugin files are located in: `platform/plugins/multi-country-sync/`

### Step 2: Run Composer Autoload
```bash
composer dump-autoload
```

### Step 3: Activate Plugin
1. Go to **Admin Panel → Plugins**
2. Find **"Multi Country Sync"**
3. Click **"Activate"**

The plugin will automatically:
- Run database migrations
- Register routes
- Load configuration
- Set up event listeners

### Step 4: Configure Settings
1. Go to **Admin Panel → Settings → Multi Country Sync**
2. **Enable Multi Country Sync**: Turn on
3. **Select Current Country**: Choose your country (EG, UAE, or SA)
4. **For each instance** (UAE, SA, EG):
   - Enter **API URL** (e.g., `https://uae.shagoof.com`)
   - Click **🔑 Generate API Key** button
   - Copy the generated key
   - **Enable Sync** toggle
5. Click **💾 Save**

### Step 5: Share API Keys
1. Copy the generated API key from **Instance A**
2. Go to **Instance B** (other country)
3. Paste the key in the corresponding instance's API Key field
4. Save settings

**Example:**
- In **EG**: Generate UAE API key → Copy it
- In **UAE**: Paste the key in EG API Key field
- In **EG**: Generate SA API key → Copy it  
- In **SA**: Paste the key in EG API Key field

### Step 6: Test Connection
1. In settings page, click **"Test Connection"** button
2. Verify all instances show "Connection successful"
3. If errors occur, check:
   - API URLs are correct
   - API keys are valid
   - Servers are accessible

### Step 7: Set Up Queue Workers
For background sync processing, set up queue workers:

**Development:**
```bash
php artisan queue:work --queue=product-sync
```

**Production (Supervisor):**
Create `/etc/supervisor/conf.d/shagoof-sync-worker.conf`:
```ini
[program:shagoof-sync-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=product-sync --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/sync-worker.log
stopwaitsecs=3600
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start shagoof-sync-worker:*
```

### Step 8: Test Sync
1. Create a test product in **EG**
2. Check if it appears in **UAE** and **SA**
3. Update the product in **EG**
4. Verify updates sync to other countries
5. Check sync logs in database: `multi_country_sync_logs` table

## Multi-Branch Deployment

### EG Branch (Main)
```bash
git checkout eg
# Plugin is already here
git add platform/plugins/multi-country-sync
git commit -m "Add multi-country sync plugin"
git push origin eg
```

### Merge to Other Branches
```bash
# Merge to UAE
git checkout uae
git merge eg
git push origin uae

# Merge to SA
git checkout sa
git merge eg
git push origin sa
```

### Configure Each Branch
Each branch needs its own configuration:
- **EG**: Set `current_country=eg`, configure UAE & SA API keys
- **UAE**: Set `current_country=uae`, configure EG & SA API keys
- **SA**: Set `current_country=sa`, configure EG & UAE API keys

## Troubleshooting

### Plugin Not Appearing
- Run `composer dump-autoload`
- Clear cache: `php artisan cache:clear`
- Check `plugin.json` syntax

### Sync Not Working
1. Check sync is enabled in settings
2. Verify API keys are correct (use Test Connection)
3. Check queue workers are running
4. Review sync logs: `SELECT * FROM multi_country_sync_logs ORDER BY created_at DESC LIMIT 10;`
5. Check Laravel logs: `storage/logs/laravel.log`

### API Errors
1. Use "Test Connection" button
2. Verify API URLs are accessible
3. Check API keys match between instances
4. Review error messages in sync logs
5. Check network connectivity

### Queue Not Processing
1. Verify queue workers are running: `ps aux | grep queue:work`
2. Check queue configuration in `.env`: `QUEUE_CONNECTION=database` or `redis`
3. Run migrations: `php artisan queue:table` (if using database queue)
4. Check failed jobs: `php artisan queue:failed`

## Verification Checklist

- [ ] Plugin activated
- [ ] Settings configured
- [ ] API keys generated and shared
- [ ] Test connection successful
- [ ] Queue workers running
- [ ] Test product created and synced
- [ ] Sync logs showing success
- [ ] No errors in Laravel logs

## Support

For issues, check:
- `README.md` - Full documentation
- `PLUGIN_CHECKLIST.md` - Completeness checklist
- `MULTI_COUNTRY_SYNC_SOLUTION.md` - Architecture details

