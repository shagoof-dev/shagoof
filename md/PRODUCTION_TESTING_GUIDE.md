# Multi-Country Sync Plugin - Production Testing Guide

## Important: Database Logs Location

**When you edit/create a product in EG:**
- ✅ Check **EG Database** → `multi_country_sync_logs` table
- ✅ Logs show syncs **TO** UAE and SA (destination instances)
- ✅ Each sync creates a separate log entry per destination

**When you edit/create a product in UAE:**
- ✅ Check **UAE Database** → `multi_country_sync_logs` table
- ✅ Logs show syncs **TO** EG and SA

**When you edit/create a product in SA:**
- ✅ Check **SA Database** → `multi_country_sync_logs` table
- ✅ Logs show syncs **TO** EG and UAE

**Rule:** Sync logs are stored in the **SOURCE** instance database (where the product was created/updated).

## Pre-Deployment Checklist

### 1. Server Setup (All Instances: EG, UAE, SA)
- [ ] Pull latest code from `eg` branch
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `php artisan migrate --force`
- [ ] Run `php artisan cms:publish:assets`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear config cache: `php artisan config:clear`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Clear view cache: `php artisan view:clear`

### 2. Queue Workers Setup
- [ ] Set up queue workers on all servers:
  ```bash
  php artisan queue:work --queue=product-sync --tries=3 --timeout=300
  ```
- [ ] Or configure Supervisor for production:
  ```ini
  [program:shagoof-sync-worker]
  command=php /path/to/artisan queue:work --queue=product-sync --tries=3 --timeout=300
  autostart=true
  autorestart=true
  user=www-data
  numprocs=2
  ```

---

## Testing Scenarios

### Scenario 1: Plugin Activation & Configuration

#### Test Steps:
1. **Activate Plugin**
   - Go to Admin Panel → Plugins
   - Find "Multi Country Sync"
   - Click "Activate"
   - Verify no errors

2. **Access Settings Page**
   - Go to Admin Panel → Settings → Multi Country Sync
   - Verify page loads without errors
   - Verify all form fields are visible

3. **Configure Basic Settings**
   - Enable Multi Country Sync: ✅ ON
   - Select Current Country: Choose correct country (EG/UAE/SA)
   - Click Save
   - Verify success message

#### Expected Results:
- ✅ Plugin activates successfully
- ✅ Settings page loads correctly
- ✅ Settings save without errors

---

### Scenario 2: API Key Generation & Management

#### Test Steps:
1. **Generate API Key for UAE**
   - In UAE Instance settings section
   - Click 🔑 Generate API Key button
   - Verify key is generated and displayed
   - Verify key is automatically copied to clipboard (check browser console)
   - Copy the generated key

2. **Generate API Key for SA**
   - Repeat for SA instance
   - Copy the generated key

3. **Generate API Key for EG**
   - Repeat for EG instance
   - Copy the generated key

4. **Share API Keys Between Instances**
   - In **EG** instance: Paste UAE and SA API keys in their respective fields
   - In **UAE** instance: Paste EG and SA API keys
   - In **SA** instance: Paste EG and UAE API keys
   - Save settings on each instance

5. **Test Show/Hide Toggle**
   - Click 👁️ eye icon on any API key field
   - Verify key becomes visible
   - Click again to hide
   - Verify key is hidden

#### Expected Results:
- ✅ API keys generate successfully (64 characters)
- ✅ Keys are displayed temporarily after generation
- ✅ Keys can be copied manually
- ✅ Show/hide toggle works
- ✅ Keys are saved to database

---

### Scenario 3: Test Connection Feature

#### Test Steps:
1. **Configure All Instances**
   - Ensure all API URLs are correct:
     - UAE: `https://uae.shagoof.com`
     - SA: `https://sa.shagoof.com`
     - EG: `https://eg.shagoof.com`
   - Ensure all API keys are set

2. **Test Connection from EG**
   - Go to EG instance settings page
   - Click "Test Connection" button
   - Wait for results
   - Verify connection status for UAE and SA

3. **Test Connection from UAE**
   - Go to UAE instance settings page
   - Click "Test Connection" button
   - Verify connection status for EG and SA

4. **Test Connection from SA**
   - Go to SA instance settings page
   - Click "Test Connection" button
   - Verify connection status for EG and UAE

#### Expected Results:
- ✅ Test connection button works
- ✅ Shows "Connection successful" for each instance
- ✅ Shows errors if API key or URL is wrong
- ✅ Shows "skipped" if URL or API key not configured

---

### Scenario 4: Product Creation Sync

#### Test Steps:
1. **Create Product in EG**
   - Go to EG instance
   - Create a new product:
     - Name: "Test Product - Sync Test"
     - SKU: "TEST-SYNC-001"
     - Price: 100
     - Status: Published
     - Add categories, images, etc.
   - Save product

2. **Verify Sync to UAE**
   - Go to UAE instance
   - Search for "Test Product - Sync Test"
   - Verify product exists
   - Verify all data is synced correctly:
     - Name, SKU, Price
     - Categories
     - Images
     - Status

3. **Verify Sync to SA**
   - Go to SA instance
   - Search for "Test Product - Sync Test"
   - Verify product exists
   - Verify all data is synced correctly

4. **Check Sync Logs**
   - **In EG Database** (where product was created):
     - Query: `SELECT * FROM multi_country_sync_logs WHERE product_id = [PRODUCT_ID] ORDER BY created_at DESC`
     - Verify logs show:
       - Product ID
       - Instance: "uae" and "sa" (two separate log entries)
       - Action: "create"
       - Status: "success"
       - response_data contains sync response
   - **Note:** Sync logs are stored in the SOURCE instance database (EG in this case)

#### Expected Results:
- ✅ Product created in EG
- ✅ Product appears in UAE within seconds/minutes
- ✅ Product appears in SA within seconds/minutes
- ✅ All product data is synced correctly
- ✅ Sync logs show success status

---

### Scenario 5: Product Update Sync

#### Test Steps:
1. **Update Product in EG**
   - Go to EG instance
   - Edit the test product:
     - Change name to "Test Product - Updated"
     - Change price to 150
     - Add/remove categories
     - Update images
   - Save changes

2. **Verify Update in UAE**
   - Go to UAE instance
   - Find the product (by SKU: "TEST-SYNC-001")
   - Verify:
     - Name updated to "Test Product - Updated"
     - Price updated to 150
     - Categories updated
     - Images updated

3. **Verify Update in SA**
   - Go to SA instance
   - Find the product
   - Verify all updates are synced

4. **Check Sync Logs**
   - **In EG Database** (where product was updated):
     - Query: `SELECT * FROM multi_country_sync_logs WHERE product_id = [PRODUCT_ID] AND action = 'update' ORDER BY created_at DESC`
     - Verify logs show:
       - Action: "update"
       - Instance: "uae" and "sa" (two separate log entries)
       - Status: "success"
       - Timestamp matches update time
   - **Note:** Each instance maintains its own sync logs for products it syncs OUT

#### Expected Results:
- ✅ Product updates in EG
- ✅ Updates sync to UAE and SA
- ✅ All changes are reflected correctly
- ✅ Sync logs show update action

---

### Scenario 6: Queue Processing

#### Test Steps:
1. **Enable Queue**
   - In settings, ensure "Use Queue for Sync" is enabled
   - Save settings

2. **Create Product**
   - Create a new product in EG
   - Verify it's added to queue immediately

3. **Check Queue Status**
   - Run: `php artisan queue:work --queue=product-sync`
   - Or check queue table: `SELECT * FROM jobs WHERE queue = 'product-sync'`
   - Verify job is processed

4. **Verify Sync**
   - Check UAE and SA instances
   - Verify product appears after queue processing

#### Expected Results:
- ✅ Products are queued for sync
- ✅ Queue workers process jobs
- ✅ Sync happens asynchronously
- ✅ No blocking of product save operation

---

### Scenario 7: Error Handling & Retry Logic

#### Test Steps:
1. **Simulate Connection Failure**
   - Temporarily change UAE API URL to wrong URL
   - Create/update a product in EG
   - Verify error is logged

2. **Check Retry Logic**
   - Verify sync retries up to 3 times (configurable)
   - Check sync logs for retry attempts
   - Fix API URL
   - Verify sync succeeds on retry

3. **Check Failed Jobs**
   - If using database queue, check `failed_jobs` table
   - Verify failed sync attempts are logged

#### Expected Results:
- ✅ Errors are caught and logged
- ✅ Retry logic works (3 attempts by default)
- ✅ Failed syncs are logged in sync_logs table
- ✅ Sync succeeds after fixing the issue

---

### Scenario 8: Sync Logs Verification

#### Test Steps:
1. **Check Sync Logs Table**
   - Run SQL: `SELECT * FROM multi_country_sync_logs ORDER BY created_at DESC LIMIT 20`
   - Verify logs contain:
     - product_id
     - instance (uae, sa, eg)
     - action (create, update)
     - status (success, failed, pending)
     - error_message (if failed)
     - response_data (if success)

2. **Verify Log Accuracy**
   - Create a product
   - Check logs match the sync operation
   - Verify timestamps are correct

#### Expected Results:
- ✅ All sync operations are logged
- ✅ Logs contain accurate information
- ✅ Failed syncs show error messages
- ✅ Successful syncs show response data

---

### Scenario 9: Multiple Products Sync

#### Test Steps:
1. **Create Multiple Products**
   - Create 5 products in EG
   - Verify all sync to UAE and SA
   - Check sync logs for all products

2. **Update Multiple Products**
   - Update all 5 products
   - Verify all updates sync correctly

#### Expected Results:
- ✅ Multiple products sync correctly
- ✅ No conflicts or errors
- ✅ All sync logs are created

---

### Scenario 10: Edge Cases

#### Test Steps:
1. **Product with Variations**
   - Create a variable product with variations
   - Verify only parent product syncs (variations don't sync separately)
   - Verify variations are included in parent sync

2. **Draft Product**
   - Create a product with status "Draft"
   - Verify it does NOT sync (only published products sync)

3. **Product Deletion**
   - Delete a synced product
   - Verify deletion syncs (if enabled in config)

4. **Disabled Instance**
   - Disable sync for UAE instance in settings
   - Create a product
   - Verify it syncs to SA but NOT to UAE

5. **Disabled Sync**
   - Disable "Enable Multi Country Sync" toggle
   - Create a product
   - Verify it does NOT sync to any instance

#### Expected Results:
- ✅ Variations handled correctly
- ✅ Draft products don't sync
- ✅ Deletions sync (if enabled)
- ✅ Disabled instances are skipped
- ✅ Global disable works

---

### Scenario 11: Performance Testing

#### Test Steps:
1. **Bulk Product Creation**
   - Create 50 products in EG
   - Monitor sync time
   - Verify all sync successfully

2. **Concurrent Updates**
   - Update multiple products simultaneously
   - Verify all updates sync correctly
   - Check for any race conditions

#### Expected Results:
- ✅ Bulk operations work correctly
- ✅ No performance degradation
- ✅ Queue handles load properly

---

### Scenario 12: Security Testing

#### Test Steps:
1. **API Key Validation**
   - Try to sync with wrong API key
   - Verify request is rejected (401 Unauthorized)

2. **CSRF Protection**
   - Verify settings form has CSRF protection
   - Verify API endpoints require authentication

3. **Input Validation**
   - Try to inject malicious data in product fields
   - Verify data is sanitized

#### Expected Results:
- ✅ Invalid API keys are rejected
- ✅ CSRF protection works
- ✅ Input validation prevents attacks

---

## Production Monitoring Checklist

### Daily Checks:
- [ ] Check sync logs for errors
- [ ] Verify queue workers are running
- [ ] Monitor failed jobs
- [ ] Check sync success rate

### Weekly Checks:
- [ ] Review sync logs for patterns
- [ ] Verify all instances are in sync
- [ ] Check API key rotation (if needed)
- [ ] Review performance metrics

### Monthly Checks:
- [ ] Audit sync logs
- [ ] Review error patterns
- [ ] Optimize sync settings if needed
- [ ] Update documentation

---

## Troubleshooting Common Issues

### Issue: Products Not Syncing
**Check:**
1. Sync enabled in settings?
2. Queue workers running?
3. API keys correct?
4. API URLs accessible?
5. Check sync logs for errors

### Issue: Sync Too Slow
**Solutions:**
1. Increase queue workers
2. Check network latency
3. Optimize product data size
4. Review retry delays

### Issue: Sync Failing
**Check:**
1. API keys valid?
2. API URLs correct?
3. Network connectivity
4. Check error messages in logs
5. Verify product data is valid

---

## Success Criteria

✅ **Plugin is production-ready when:**
- All scenarios above pass
- No errors in logs
- Sync happens within acceptable time (< 30 seconds)
- Queue workers running smoothly
- All instances stay in sync
- Error handling works correctly
- Logs are accurate and helpful

---

## Rollback Plan

If issues occur:
1. Disable sync in settings (immediate stop)
2. Disable plugin if needed
3. Check logs to identify issue
4. Fix and re-enable
5. Re-sync missing products manually if needed

---

**Last Updated:** December 2024  
**Version:** 1.0.0

