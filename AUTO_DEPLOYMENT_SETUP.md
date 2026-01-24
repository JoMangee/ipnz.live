# Auto-Deployment Setup Guide for IPnz.live

## Current Status
✅ `.cpanel.yml` is properly configured and ready for auto-deployment

## Steps to Enable Auto-Deployment

### 1. In cPanel (Joyful Host)

```
cPanel Dashboard
  → "Git Version Control" (or "Git™ Repository Management")
    → Select repository: /home2/ipnz/repositories/ipnz.live
      → Click "Manage Deployment"
        → Enable automatic deployment
          → Select branch: "ahmad"
          → Copy the webhook URL
```

### 2. Configure GitHub Webhook

Once cPanel generates the webhook URL:

1. Go to: https://github.com/JoMangee/ipnz.live/settings/hooks
2. Click **"Add webhook"**
3. Paste the cPanel webhook URL into **"Payload URL"**
4. Select:
   - Content type: `application/json`
   - Events: `Push events` ✅
   - Active: ✅ (checked)
5. Click **"Add webhook"**

### 3. Test Auto-Deployment

Once configured:
```bash
# Push to GitHub
git push origin ahmad

# This will automatically:
# 1. Trigger GitHub webhook
# 2. cPanel receives the webhook
# 3. .cpanel.yml tasks run automatically
# 4. Files deployed to /home2/ipnz/ipnz-live/www/

# Result: https://auth-dev.ipnz.live/ updates automatically
```

## What Gets Deployed (.cpanel.yml Tasks)

✅ All files from `www/` directory
✅ `.htaccess` (security headers, rewrites)
✅ `test_referral_flow.php` (test script)
✅ Preserves existing database config
✅ Clears PHP opcode cache

## Deployment Verification

After pushing, check:
1. GitHub → Webhooks → Recent Deliveries (green checkmark = success)
2. cPanel → Git Version Control → Deployment Log
3. Browser: https://auth-dev.ipnz.live/ should show latest changes

## Troubleshooting

If webhook doesn't trigger:
1. **Check webhook URL** - Must be unique and accessible
2. **Verify branch name** - Should match "ahmad" 
3. **Check cPanel logs** - `/home2/ipnz/.log`
4. **Resend webhook** - GitHub allows manual resend for testing
5. **Fallback** - Can always deploy manually via cPanel

## Timeline with Auto-Deploy

### Without Auto-Deploy (Current)
- Push to GitHub ✓
- SSH to cPanel or use cPanel UI
- Manually click "Deploy HEAD Commit" button
- Wait 1-2 minutes
- Total: ~3-5 minutes

### With Auto-Deploy
- Push to GitHub ✓
- Automatic webhook triggers
- Deployment runs immediately
- ~1-2 minutes automatically
- Total: ~2 minutes

## Commands for Manual Deploy (if needed)

If auto-deploy fails, deploy manually:
```bash
# SSH to cPanel
ssh user@joyful.host

# Navigate to repo
cd /home2/ipnz/repositories/ipnz.live

# Pull latest
git pull origin ahmad

# Run deployment tasks manually
bash -c 'export DEPLOYPATH=/home2/ipnz/ipnz-live/www; /bin/cp -R www/* $DEPLOYPATH/; /bin/cp .htaccess $DEPLOYPATH/.htaccess; /bin/cp test_referral_flow.php $DEPLOYPATH/test_referral_flow.php'
```

---

**Recommended**: Enable auto-deployment to reduce manual steps. It's much faster once configured!
