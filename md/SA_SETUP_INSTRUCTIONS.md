# SA Instance Setup Instructions

## ✅ Step 1: Generate SA API Key

1. **In SA Instance** (sa.shagoof.com):
   - Go to: **Admin Panel → Settings → Multi Country Sync**
   - Scroll to **"Saudi Arabia (SA) Instance"** section
   - Click **🔑 Generate API Key** button
   - **COPY THE GENERATED KEY** (it shows temporarily)
   - Save it somewhere safe - you'll need to paste it in EG and UAE instances

---

## ✅ Step 2: Configure SA Instance Settings

**Still in SA Instance:**

1. **Enable Multi Country Sync:** ✅ Turn ON
2. **Current Country:** Select **"Saudi Arabia (SA)"**

3. **EG Instance Configuration:**
   - **API URL:** `https://eg.shagoof.com`
   - **API Key:** Paste the API key you generated in **EG** instance
   - **Enable Sync:** ✅ Turn ON

4. **UAE Instance Configuration:**
   - **API URL:** `https://uae.shagoof.com`
   - **API Key:** Paste the API key you generated in **UAE** instance (or generate it first if not done)
   - **Enable Sync:** ✅ Turn ON

5. **SA Instance Configuration:**
   - **API URL:** `https://sa.shagoof.com` (already set)
   - **API Key:** The key you just generated (already saved)
   - **Enable Sync:** ✅ Turn ON

6. Click **💾 Save**

---

## ✅ Step 3: Update EG Instance with SA API Key

**Switch to EG Instance** (eg.shagoof.com):

1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Find **"SA Instance Configuration"** section
3. **API Key:** Paste the SA API key you generated in Step 1
4. **Enable Sync:** ✅ Make sure it's ON
5. Click **💾 Save**

---

## ✅ Step 4: Update UAE Instance with SA API Key

**Switch to UAE Instance** (uae.shagoof.com):

1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Find **"SA Instance Configuration"** section
3. **API Key:** Paste the SA API key you generated in Step 1
4. **Enable Sync:** ✅ Make sure it's ON
5. Click **💾 Save**

---

## ✅ Step 5: Test Connection

**In SA Instance:**

1. Go to: **Admin Panel → Settings → Multi Country Sync**
2. Scroll to the bottom
3. Click **🔌 Test Connection** button
4. You should see:
   - ✅ **EG:** Connection successful
   - ✅ **UAE:** Connection successful (if UAE is configured)

**If you see errors:**
- Check that API keys are correct
- Check that URLs are correct
- Make sure the plugin is activated in all instances

---

## ✅ Step 6: Test Product Sync

### Test 1: Create Product in SA

1. **In SA Instance:**
   - Go to: **Products → Add New Product**
   - Create a test product (e.g., "Test Product SA")
   - Set status to **Published**
   - Click **Save**

2. **Check EG Instance:**
   - Go to: **Products**
   - Look for "Test Product SA"
   - It should appear automatically (may take a few seconds)

3. **Check UAE Instance:**
   - Go to: **Products**
   - Look for "Test Product SA"
   - It should appear automatically

### Test 2: Update Product in SA

1. **In SA Instance:**
   - Edit the test product
   - Change the name to "Test Product SA Updated"
   - Click **Save**

2. **Check EG and UAE:**
   - The product name should update automatically

---

## 📋 Summary Checklist

- [ ] Generated SA API Key
- [ ] Configured SA instance (Current Country = SA)
- [ ] Added SA API key to EG instance
- [ ] Added SA API key to UAE instance
- [ ] Tested connection from SA
- [ ] Created test product in SA
- [ ] Verified product appears in EG
- [ ] Verified product appears in UAE

---

## 🔑 API Keys Reference

**Where to find API keys:**

- **EG API Key:** Generated in EG instance → EG Instance section
- **UAE API Key:** Generated in UAE instance → UAE Instance section  
- **SA API Key:** Generated in SA instance → SA Instance section

**Important:** Each instance generates its own API key. You need to share these keys between instances.

---

## 🚨 Troubleshooting

**If sync doesn't work:**

1. Check that **Enable Multi Country Sync** is ON in all instances
2. Check that **Current Country** is set correctly in each instance
3. Check that API keys match (copy-paste carefully)
4. Check that URLs are correct (no trailing slashes)
5. Check that **Enable Sync** is ON for each instance
6. Check sync logs: **Admin Panel → Multi Country Sync → Sync Logs** (if available)

**If test connection fails:**

- Verify the plugin is activated in the target instance
- Check API key is correct
- Check URL is accessible
- Check firewall/security settings






