# cPanel Deployment Workflow for IPnz.live

## Current Status
✅ `.cpanel.yml` is properly configured for deployments
✅ cPanel Git Version Control is set up and ready

## Deployment Method (Manual)

Your cPanel version uses manual deployment buttons in the "Pull or Deploy" tab.

### Simple 2-Step Deployment Process

**Step 1: Update from Remote**
```
cPanel → Git Version Control → ipnz.live staging
  → "Pull or Deploy" tab
    → Click "Update from Remote"
      → Pulls latest commits from GitHub (branch: ahmad)
```

**Step 2: Deploy HEAD Commit**
```
cPanel → Git Version Control → ipnz.live staging
  → "Pull or Deploy" tab
    → Click "Deploy HEAD Commit"
      → Runs .cpanel.yml tasks
      → Deploys to https://auth-dev.ipnz.live/
```

## Typical Workflow

### Development (Local Machine)
```bash
# Make changes locally
git add .
git commit -m "Your commit message"
git push origin ahmad
```

### Staging Deployment (cPanel)
```
1. Log into cPanel → Git Version Control
2. Click "Update from Remote" button
3. Wait for success message
4. Click "Deploy HEAD Commit" button
5. Wait for deployment complete (1-2 minutes)
6. Test at https://auth-dev.ipnz.live/
```

## What Gets Deployed

When you click "Deploy HEAD Commit", the `.cpanel.yml` file runs these tasks:

✅ All files from `www/` directory
✅ `.htaccess` (security headers, URL rewrites)
✅ `test_referral_flow.php` (test script)
✅ Preserves existing database config
✅ Clears PHP opcode cache

## Deployment Verification

After deployment completes:
1. Check cPanel → History (shows deployment log)
2. Browser: https://auth-dev.ipnz.live/ should show latest changes
3. Check test script: https://auth-dev.ipnz.live/test_referral_flow.php

## Timeline with Manual Deployment

- Push to GitHub ✓
- SSH to cPanel or use cPanel UI
- Click "Update from Remote" (30 seconds)
- Click "Deploy HEAD Commit" (1-2 minutes)
- Total: ~2-3 minutes

## Troubleshooting

If deployment fails:
1. **Check cPanel History** - Shows error messages
2. **Verify branch** - Should be "ahmad"
3. **Check file permissions** - `.htaccess` and PHP files need correct perms
4. **Review .cpanel.yml** - Ensure syntax is correct
5. **Check logs** - `/home2/ipnz/.log` for deployment errors

## Manual SSH Deployment (Fallback)

If cPanel deployment fails, deploy manually:

```bash
# SSH to cPanel
ssh user@ipnz.ipnz.live

# Navigate to repo
cd /home2/ipnz/repositories/ipnz.live

# Pull latest from GitHub
git pull origin ahmad

# Export deploy path
export DEPLOYPATH=/home2/ipnz/ipnz-live/www

# Copy www files
/bin/cp -R www/* $DEPLOYPATH/

# Copy critical root files
/bin/cp .htaccess $DEPLOYPATH/.htaccess
/bin/cp test_referral_flow.php $DEPLOYPATH/test_referral_flow.php

# Create avatars directory
mkdir -p $DEPLOYPATH/images/avatars

# Set permissions
chmod 600 $DEPLOYPATH/datacenter/database.config.php

# Clear cache
touch $DEPLOYPATH/index.php $DEPLOYPATH/join.php $DEPLOYPATH/auth.php

echo "Deployment complete!"
```

## Summary

**Your current setup**:
- ✅ Repository configured in cPanel
- ✅ Branch checked out: `ahmad`
- ✅ `.cpanel.yml` ready for deployment
- ✅ Manual "Update from Remote" + "Deploy HEAD Commit" buttons available

**No webhook setup needed** - the manual buttons work perfectly fine for a staging environment. Just 2 clicks in cPanel after each git push!
