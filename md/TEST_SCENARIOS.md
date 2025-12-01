# Multi-Country Sync Plugin - Test Scenarios

This document outlines comprehensive test scenarios for the Multi-Country Sync plugin.

## Prerequisites

Before testing, ensure:
- ✅ Plugin is activated on all instances (EG, SA, UAE)
- ✅ API keys are generated for each instance
- ✅ API URLs are configured correctly
- ✅ Instances are enabled in settings
- ✅ Queue workers are running (if using queue)

---

## Test Scenario 1: Basic Product Creation Sync

### Objective
Verify that creating a product in EG syncs to SA and UAE instances.

### Steps
1. **In EG Instance:**
   - Go to Products → Create New Product
   - Fill in basic details:
     - Name: "Test Product 1"
     - SKU: "TEST-001"
     - Price: 100
     - Description: "Test description"
     - Status: Published
   - Save the product

2. **Verify in SA Instance:**
   - Go to Products list
   - Search for "Test Product 1"
   - Verify product exists with same details
   - Check SKU matches: "TEST-001"
   - Verify price is 100

3. **Verify in UAE Instance:**
   - Repeat step 2 for UAE instance

### Expected Results
- ✅ Product appears in SA and UAE within a few seconds
- ✅ All basic fields match (name, SKU, price, description)
- ✅ Product status is "Published"
- ✅ No errors in logs

### Logs to Check
```bash
# In EG instance
tail -f storage/logs/laravel.log | grep "Multi-Country Sync"

# Should see:
# - "Dispatching job to queue"
# - "Syncing to instance: sa"
# - "Successfully synced to instance"
```

---

## Test Scenario 2: Product Update Sync

### Objective
Verify that updating a product in EG syncs changes to other instances.

### Steps
1. **In EG Instance:**
   - Edit "Test Product 1"
   - Change price from 100 to 150
   - Change description to "Updated description"
   - Save changes

2. **Verify in SA Instance:**
   - Find "Test Product 1"
   - Verify price is now 150
   - Verify description is "Updated description"

3. **Verify in UAE Instance:**
   - Repeat step 2

### Expected Results
- ✅ Changes reflect in SA and UAE
- ✅ Product ID remains the same (matched by SKU or source_product_id)
- ✅ Update happens within a few seconds

---

## Test Scenario 3: Image Sync

### Objective
Verify that product images sync correctly across instances.

### Steps
1. **In EG Instance:**
   - Edit a product
   - Upload 3 images
   - Set featured image
   - Save product

2. **Verify in SA Instance:**
   - Open the synced product
   - Verify all 3 images are present
   - Verify images load correctly (not broken links)
   - Verify featured image is set correctly

3. **Verify Image URLs:**
   - Check that images are stored locally in SA (not pointing to EG URLs)
   - Images should be in SA's storage path

### Expected Results
- ✅ All images appear in SA
- ✅ Images load correctly (not 404 errors)
- ✅ Images are stored locally in SA instance
- ✅ Featured image is synced correctly

### Logs to Check
```bash
# Should see image download/upload logs:
# - "Downloading image from URL"
# - "Image uploaded successfully"
```

---

## Test Scenario 4: Slug/Permalink Sync with Uniqueness

### Objective
Verify that slugs sync and remain unique on each instance.

### Steps
1. **In EG Instance:**
   - Create product with slug: "test-product"
   - Save product

2. **In SA Instance (Before Sync):**
   - Create a product with slug: "test-product" (manually if possible)
   - This creates a conflict

3. **In EG Instance:**
   - Update the product (triggers sync)

4. **Verify in SA Instance:**
   - Check the synced product slug
   - Should be "test-product-1" or similar (unique)

### Expected Results
- ✅ Slug syncs from EG to SA
- ✅ If conflict exists, slug is automatically made unique
- ✅ Counter is appended (e.g., `-1`, `-2`)
- ✅ No duplicate slugs on same instance

### Edge Cases to Test
- Product with no slug → slug generated from name
- Product with special characters in name → slug sanitized
- Multiple conflicts → counter increments correctly

---

## Test Scenario 5: Queue Functionality

### Objective
Verify that sync jobs run asynchronously without blocking.

### Steps
1. **Check Queue Configuration:**
   - Go to Multi-Country Sync Settings
   - Verify "Use Queue" is enabled

2. **Create Product:**
   - Create a product in EG
   - Observe save completes immediately (no hanging spinner)

3. **Verify Queue Processing:**
   - Check queue logs or database
   - Verify job was queued
   - Verify job completed successfully

### Expected Results
- ✅ Product save completes immediately
- ✅ No loading spinner hangs
- ✅ Sync happens in background
- ✅ Job appears in queue and processes

### Queue Commands
```bash
# Check queue status
php artisan queue:work --queue=product-sync

# Check failed jobs
php artisan queue:failed
```

---

## Test Scenario 6: Disabled Instance

### Objective
Verify that disabled instances are skipped during sync.

### Steps
1. **In EG Settings:**
   - Go to Multi-Country Sync Settings
   - Disable SA instance (toggle off)
   - Save settings

2. **Create/Update Product:**
   - Create or update a product in EG

3. **Verify:**
   - Product syncs to UAE (if enabled)
   - Product does NOT sync to SA
   - Check logs confirm SA was skipped

### Expected Results
- ✅ SA instance is skipped
- ✅ Log shows "Instance disabled"
- ✅ Other enabled instances still sync

---

## Test Scenario 7: API Key Authentication

### Objective
Verify that API authentication works correctly.

### Steps
1. **Test with Valid API Key:**
   - Use "Test Connection" button in settings
   - Should show "Connection successful"

2. **Test with Invalid API Key:**
   - Temporarily change API key in settings
   - Try to sync a product
   - Should fail with authentication error

3. **Test with Missing API Key:**
   - Remove API key from settings
   - Try to sync
   - Should skip instance or show error

### Expected Results
- ✅ Valid API key allows sync
- ✅ Invalid API key blocks sync
- ✅ Missing API key shows appropriate error

---

## Test Scenario 8: Connection Test

### Objective
Verify the "Test Connection" functionality.

### Steps
1. **In EG Settings:**
   - Go to Multi-Country Sync Settings
   - Click "Test Connection" button

2. **Verify Results:**
   - Should test all enabled instances (SA, UAE)
   - Should show status for each instance
   - Should indicate if plugin is activated on target

### Expected Results
- ✅ Shows connection status for each instance
- ✅ Indicates if plugin is activated
- ✅ Shows authentication status
- ✅ Provides clear error messages if connection fails

---

## Test Scenario 9: Sync on Create vs Update

### Objective
Verify sync_on_create and sync_on_update settings.

### Steps
1. **Disable Sync on Create:**
   - In settings, disable "Sync on Create"
   - Enable "Sync on Update"
   - Save settings

2. **Create Product:**
   - Create new product in EG
   - Verify it does NOT sync to other instances

3. **Update Product:**
   - Update the product
   - Verify it DOES sync to other instances

4. **Enable Sync on Create:**
   - Re-enable "Sync on Create"
   - Create another product
   - Verify it syncs

### Expected Results
- ✅ Respects sync_on_create setting
- ✅ Respects sync_on_update setting
- ✅ Settings take effect immediately

---

## Test Scenario 10: Product with Variations

### Objective
Verify that product variations are handled correctly.

### Steps
1. **In EG Instance:**
   - Create a variable product (e.g., T-shirt with sizes)
   - Add variations (Small, Medium, Large)
   - Set different prices for each variation
   - Save product

2. **Verify in SA Instance:**
   - Check if parent product syncs
   - Verify variations are NOT synced separately (they sync with parent)

### Expected Results
- ✅ Parent product syncs
- ✅ Variations are NOT synced as separate products
- ✅ Log shows "Skipping - product is a variation"

---

## Test Scenario 11: Product Status Filtering

### Objective
Verify that only published products sync.

### Steps
1. **In EG Instance:**
   - Create product with status "Draft"
   - Save product

2. **Verify in SA Instance:**
   - Product should NOT appear in SA

3. **In EG Instance:**
   - Change product status to "Published"
   - Save product

4. **Verify in SA Instance:**
   - Product should now appear

### Expected Results
- ✅ Draft products do NOT sync
- ✅ Published products DO sync
- ✅ Status change triggers sync

---

## Test Scenario 12: Error Handling and Retries

### Objective
Verify error handling and retry mechanism.

### Steps
1. **Simulate Error:**
   - Temporarily disable SA instance API endpoint
   - Or use invalid API URL
   - Try to sync a product

2. **Verify Retry:**
   - Check logs for retry attempts
   - Should retry up to 3 times (configurable)
   - Should log failure after max retries

3. **Verify Logging:**
   - Check sync_logs table
   - Should have entry with status "failed"
   - Should have error message

### Expected Results
- ✅ Retries up to configured max_retries
- ✅ Waits retry_delay between retries
- ✅ Logs failure in sync_logs table
- ✅ Other instances still sync successfully

---

## Test Scenario 13: Large Product Data

### Objective
Verify sync works with large product data.

### Steps
1. **Create Product with Large Content:**
   - Create product with very long description (10,000+ characters)
   - Add many images (10+ images)
   - Add many categories/tags
   - Save product

2. **Verify Sync:**
   - Check sync completes successfully
   - Verify all data syncs correctly
   - Check timeout doesn't occur

### Expected Results
- ✅ Large content syncs correctly
- ✅ Multiple images sync correctly
- ✅ Timeout is sufficient (5+ minutes)
- ✅ No data truncation

---

## Test Scenario 14: Concurrent Updates

### Objective
Verify behavior when same product is updated multiple times quickly.

### Steps
1. **Rapid Updates:**
   - Update product in EG
   - Immediately update again (within 1 second)
   - Update third time quickly

2. **Verify:**
   - All updates should sync
   - No conflicts or data loss
   - Latest data should be in SA

### Expected Results
- ✅ All updates sync successfully
- ✅ Latest data is preserved
- ✅ No race conditions

---

## Test Scenario 15: SKU Matching

### Objective
Verify products are matched correctly by SKU.

### Steps
1. **Create Product in EG:**
   - SKU: "UNIQUE-SKU-123"
   - Save product

2. **Verify in SA:**
   - Product syncs and gets ID (e.g., ID 100)

3. **Update Product in EG:**
   - Change price
   - Save

4. **Verify in SA:**
   - Same product (ID 100) is updated
   - New product is NOT created
   - Price change reflects

### Expected Results
- ✅ Products matched by SKU correctly
- ✅ Updates go to existing product
- ✅ No duplicate products created

---

## Test Scenario 16: Source Product ID Matching

### Objective
Verify products are matched by source_product_id from metadata.

### Steps
1. **Create Product in EG:**
   - Product gets ID 50 in EG
   - Syncs to SA

2. **Check Metadata:**
   - In SA, check product metadata
   - Should have sync_metadata with source_product_id: 50

3. **Update in EG:**
   - Update product ID 50
   - Should match SA product by source_product_id

### Expected Results
- ✅ Metadata stored correctly
- ✅ Matching by source_product_id works
- ✅ Falls back to SKU if metadata missing

---

## Test Scenario 17: API Key Generation

### Objective
Verify API key generation functionality.

### Steps
1. **Generate API Key:**
   - Go to Multi-Country Sync Settings
   - Click "Generate API Key" for SA instance
   - Verify new key is generated
   - Verify key is saved

2. **Copy Key:**
   - Verify key can be copied
   - Verify key is shown/hidden correctly

3. **Use Generated Key:**
   - Copy key to SA instance settings
   - Test connection
   - Should work

### Expected Results
- ✅ API key generates successfully
- ✅ Key is 64 characters long
- ✅ Key is saved to settings
- ✅ Key works for authentication

---

## Test Scenario 18: Current Country Setting

### Objective
Verify current country setting prevents self-sync.

### Steps
1. **Set Current Country:**
   - In EG, set Current Country to "EG"
   - Save settings

2. **Create Product:**
   - Create product in EG
   - Should NOT sync to EG (itself)
   - Should sync to SA and UAE only

### Expected Results
- ✅ Current country setting works
- ✅ Self-sync is prevented
- ✅ Only other instances sync

---

## Test Scenario 19: Network Failure Recovery

### Objective
Verify sync recovers after network failure.

### Steps
1. **Simulate Network Failure:**
   - Temporarily block network to SA instance
   - Try to sync product
   - Should fail with timeout

2. **Restore Network:**
   - Restore network connection
   - Update product again
   - Should sync successfully

### Expected Results
- ✅ Handles network failures gracefully
- ✅ Retries after network restored
- ✅ No permanent failures

---

## Test Scenario 20: Settings Persistence

### Objective
Verify settings are saved and persist correctly.

### Steps
1. **Update Settings:**
   - Change API URLs
   - Change API keys
   - Change enabled/disabled toggles
   - Save settings

2. **Reload Page:**
   - Refresh settings page
   - Verify all changes persist

3. **Check Config:**
   - Verify config cache is cleared
   - Verify settings load from database

### Expected Results
- ✅ All settings save correctly
- ✅ Settings persist after reload
- ✅ Config cache cleared
- ✅ Settings load from database

---

## Test Scenario 21: Product Deletion (Future)

### Objective
Verify product deletion sync (if implemented).

### Steps
1. **Delete Product:**
   - Delete product in EG
   - Verify deletion syncs to other instances

### Expected Results
- ✅ Deletion syncs to other instances
- ✅ Product removed from SA/UAE

**Note:** Currently deletion sync is disabled in config (`sync_on_delete => false`)

---

## Test Scenario 22: Categories and Tags Sync

### Objective
Verify product relationships sync correctly.

### Steps
1. **Create Product with Relationships:**
   - Assign categories
   - Assign tags
   - Assign collections
   - Assign labels
   - Save product

2. **Verify in SA:**
   - Check product has same categories
   - Check product has same tags
   - Verify relationships match

### Expected Results
- ✅ Categories sync correctly
- ✅ Tags sync correctly
- ✅ Collections sync correctly
- ✅ Labels sync correctly

**Note:** Requires categories/tags/collections to exist in target instance first

---

## Test Scenario 23: Product Attributes Sync

### Objective
Verify product attributes sync correctly.

### Steps
1. **Create Product with Attributes:**
   - Add product attributes (e.g., Color, Size)
   - Set attribute values
   - Save product

2. **Verify in SA:**
   - Check attributes are synced
   - Verify attribute values match

### Expected Results
- ✅ Attributes sync correctly
- ✅ Attribute values match

**Note:** Requires attributes to exist in target instance first

---

## Test Scenario 24: Performance Testing

### Objective
Verify sync performance with multiple products.

### Steps
1. **Bulk Create:**
   - Create 10 products in EG
   - Monitor sync time
   - Check queue processing

2. **Verify:**
   - All products sync successfully
   - No performance degradation
   - Queue processes efficiently

### Expected Results
- ✅ All products sync
- ✅ Acceptable performance
- ✅ Queue processes efficiently

---

## Test Scenario 25: Logging and Monitoring

### Objective
Verify comprehensive logging for debugging.

### Steps
1. **Perform Various Actions:**
   - Create product
   - Update product
   - Test connection
   - Generate API key

2. **Check Logs:**
   - Review Laravel logs
   - Check sync_logs table
   - Verify log entries are detailed

### Expected Results
- ✅ Detailed logs for all actions
- ✅ Logs include product IDs, instances, actions
- ✅ Error logs include stack traces
- ✅ Success logs confirm completion

---

## Test Checklist Summary

### Basic Functionality
- [ ] Product creation syncs
- [ ] Product update syncs
- [ ] Images sync correctly
- [ ] Slug syncs with uniqueness
- [ ] Queue works without blocking

### Settings & Configuration
- [ ] Settings save correctly
- [ ] API key generation works
- [ ] Connection test works
- [ ] Enable/disable instances works
- [ ] Current country setting works

### Error Handling
- [ ] Retry mechanism works
- [ ] Error logging works
- [ ] Network failures handled
- [ ] Invalid API keys rejected

### Edge Cases
- [ ] Draft products don't sync
- [ ] Variations handled correctly
- [ ] Large data syncs
- [ ] Concurrent updates work
- [ ] SKU matching works

### Performance
- [ ] Acceptable sync speed
- [ ] Queue processes efficiently
- [ ] No blocking on save
- [ ] Timeout sufficient for images

---

## Troubleshooting

### Common Issues

1. **Products not syncing:**
   - Check plugin is activated
   - Check API keys are set
   - Check instances are enabled
   - Check queue is running (if using queue)
   - Check logs for errors

2. **Images not loading:**
   - Check image URLs are accessible
   - Check storage permissions
   - Check logs for download errors
   - Verify RvMedia is configured

3. **Slug conflicts:**
   - Check slug uniqueness logic
   - Verify counter increments
   - Check logs for conflict resolution

4. **Timeout errors:**
   - Increase api_timeout setting
   - Check network speed
   - Reduce image count/size
   - Check PHP max_execution_time

5. **Queue not processing:**
   - Check queue worker is running
   - Check queue connection setting
   - Check failed jobs table
   - Restart queue worker

---

## Test Environment Setup

### Required Instances
- **EG Instance:** `https://eg.shagoof.com` (or local)
- **SA Instance:** `https://sa.shagoof.com` (or local)
- **UAE Instance:** `https://uae.shagoof.com` (or local)

### Required Tools
- Browser (for admin panel)
- Terminal (for logs and commands)
- Database access (for verification)
- Queue worker (for async testing)

### Test Data
- Create test products with various configurations
- Use unique SKUs for easy identification
- Use descriptive names for easy searching

---

## Notes

- All test scenarios should be performed in a test/staging environment first
- Backup databases before testing
- Monitor logs during testing
- Document any issues found
- Test both create and update scenarios
- Test with different product types (simple, variable, digital)

---

**Last Updated:** 2025-12-01
**Version:** 1.0

