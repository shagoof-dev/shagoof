# Multi-Country Sync Plugin - Setup Guide

## Overview
This guide will help you set up product synchronization between EG, UAE, and SA instances.

## Step-by-Step Setup

### Step 1: Activate Plugin in All Instances ✅

**Already Done:**
- ✅ EG - Plugin activated
- ✅ SA - Plugin activated

**Still Need:**
- ⏳ UAE - Activate plugin

---

### Step 2: Generate API Keys in Each Instance

You need to generate an API key in **EACH** instance (EG, UAE, SA). Each instance needs its own unique API key.

#### In EG Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Scroll to **"Egypt (EG) Instance"** section
3. Click **🔑 Generate API Key** button
4. **Copy the generated key** (it will be shown temporarily)
5. Save this key somewhere safe (you'll need it for UAE and SA)

#### In UAE Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Scroll to **"United Arab Emirates (UAE) Instance"** section
3. Click **🔑 Generate API Key** button
4. **Copy the generated key**
5. Save this key (you'll need it for EG and SA)

#### In SA Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Scroll to **"Saudi Arabia (SA) Instance"** section
3. Click **🔑 Generate API Key** button
4. **Copy the generated key**
5. Save this key (you'll need it for EG and UAE)

---

### Step 3: Configure Each Instance

#### Configure EG Instance:

1. Go to: **Admin Panel → Settings → Multi Country Sync**

2. **Enable Multi Country Sync:** ✅ ON

3. **Current Country:** Select **"Egypt (EG)"**

4. **UAE Instance Configuration:**
   - **API URL:** `https://uae.shagoof.com`
   - **API Key:** Paste the API key you generated in **UAE** instance
   - **Enable Sync:** ✅ ON

5. **SA Instance Configuration:**
   - **API URL:** `https://sa.shagoof.com`
   - **API Key:** Paste the API key you generated in **SA** instance
   - **Enable Sync:** ✅ ON

6. **EG Instance Configuration:**
   - **API URL:** `https://eg.shagoof.com` (your own instance)
   - **API Key:** The key you generated in EG (already saved)
   - **Enable Sync:** ✅ ON (optional, but recommended)

7. Click **Save**

#### Configure UAE Instance:

1. Go to: **Admin Panel → Settings → Multi Country Sync**

2. **Enable Multi Country Sync:** ✅ ON

3. **Current Country:** Select **"United Arab Emirates (UAE)"**

4. **EG Instance Configuration:**
   - **API URL:** `https://eg.shagoof.com`
   - **API Key:** Paste the API key you generated in **EG** instance
   - **Enable Sync:** ✅ ON

5. **SA Instance Configuration:**
   - **API URL:** `https://sa.shagoof.com`
   - **API Key:** Paste the API key you generated in **SA** instance
   - **Enable Sync:** ✅ ON

6. **UAE Instance Configuration:**
   - **API URL:** `https://uae.shagoof.com` (your own instance)
   - **API Key:** The key you generated in UAE (already saved)
   - **Enable Sync:** ✅ ON (optional, but recommended)

7. Click **Save**

#### Configure SA Instance:

1. Go to: **Admin Panel → Settings → Multi Country Sync**

2. **Enable Multi Country Sync:** ✅ ON

3. **Current Country:** Select **"Saudi Arabia (SA)"**

4. **EG Instance Configuration:**
   - **API URL:** `https://eg.shagoof.com`
   - **API Key:** Paste the API key you generated in **EG** instance
   - **Enable Sync:** ✅ ON

5. **UAE Instance Configuration:**
   - **API URL:** `https://uae.shagoof.com`
   - **API Key:** Paste the API key you generated in **UAE** instance
   - **Enable Sync:** ✅ ON

6. **SA Instance Configuration:**
   - **API URL:** `https://sa.shagoof.com` (your own instance)
   - **API Key:** The key you generated in SA (already saved)
   - **Enable Sync:** ✅ ON (optional, but recommended)

7. Click **Save**

---

### Step 4: Test Connection

After configuring all instances, test the connection from each instance:

#### From EG Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Click **"Test Connection"** button
3. You should see:
   - ✅ **UAE: Connection successful**
   - ✅ **SA: Connection successful**

#### From UAE Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Click **"Test Connection"** button
3. You should see:
   - ✅ **EG: Connection successful**
   - ✅ **SA: Connection successful**

#### From SA Instance:
1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Click **"Test Connection"** button
3. You should see:
   - ✅ **EG: Connection successful**
   - ✅ **UAE: Connection successful**

---

## Quick Reference: Which API Key Goes Where?

### In EG Instance Settings:
- **UAE API Key** → Use the key generated in **UAE** instance
- **SA API Key** → Use the key generated in **SA** instance
- **EG API Key** → Use the key generated in **EG** instance (your own)

### In UAE Instance Settings:
- **EG API Key** → Use the key generated in **EG** instance
- **SA API Key** → Use the key generated in **SA** instance
- **UAE API Key** → Use the key generated in **UAE** instance (your own)

### In SA Instance Settings:
- **EG API Key** → Use the key generated in **EG** instance
- **UAE API Key** → Use the key generated in **UAE** instance
- **SA API Key** → Use the key generated in **SA** instance (your own)

**Rule:** Each instance uses **OTHER instances' API keys**, not its own (except for its own section).

---

## Which Instances to Enable?

### In EG Instance:
- ✅ **UAE** - Enable Sync: **ON** (sync products TO UAE)
- ✅ **SA** - Enable Sync: **ON** (sync products TO SA)
- ✅ **EG** - Enable Sync: **ON** (optional, for consistency)

### In UAE Instance:
- ✅ **EG** - Enable Sync: **ON** (sync products TO EG)
- ✅ **SA** - Enable Sync: **ON** (sync products TO SA)
- ✅ **UAE** - Enable Sync: **ON** (optional, for consistency)

### In SA Instance:
- ✅ **EG** - Enable Sync: **ON** (sync products TO EG)
- ✅ **UAE** - Enable Sync: **ON** (sync products TO UAE)
- ✅ **SA** - Enable Sync: **ON** (optional, for consistency)

**Note:** When you create/update a product in EG, it will sync to UAE and SA automatically (if enabled).

---

## Testing Product Sync

After setup is complete, test the sync:

### Test 1: Create Product in EG
1. Go to EG instance
2. Create a new product:
   - Name: "Test Product - Sync Test"
   - SKU: "TEST-SYNC-001"
   - Status: **Published**
   - Save
3. Wait a few seconds/minutes
4. Check UAE instance → Product should appear
5. Check SA instance → Product should appear

### Test 2: Update Product in EG
1. Go to EG instance
2. Edit the test product
3. Change name to "Test Product - Updated"
4. Save
5. Check UAE and SA → Product should be updated

---

## Troubleshooting

### If Test Connection Fails:

1. **Check API Keys:**
   - Make sure you're using the correct API key for each instance
   - Each instance should use OTHER instances' keys

2. **Check URLs:**
   - EG: `https://eg.shagoof.com`
   - UAE: `https://uae.shagoof.com`
   - SA: `https://sa.shagoof.com`

3. **Check Plugin Activation:**
   - Make sure plugin is activated in all instances
   - Clear cache: `php artisan cache:clear`

4. **Check Queue Workers:**
   - If using queues, make sure workers are running:
   ```bash
   php artisan queue:work --queue=product-sync
   ```

---

## Summary Checklist

- [ ] Plugin activated in EG ✅
- [ ] Plugin activated in UAE ⏳
- [ ] Plugin activated in SA ✅
- [ ] API key generated in EG
- [ ] API key generated in UAE
- [ ] API key generated in SA
- [ ] EG instance configured (UAE & SA API keys added)
- [ ] UAE instance configured (EG & SA API keys added)
- [ ] SA instance configured (EG & UAE API keys added)
- [ ] Test connection successful from EG
- [ ] Test connection successful from UAE
- [ ] Test connection successful from SA
- [ ] Test product creation sync
- [ ] Test product update sync

---

**Last Updated:** December 2024

