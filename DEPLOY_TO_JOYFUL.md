# Pre-Deployment Checklist for auth-dev.ipnz.live

## Files to Create on Joyful Host (Required)

### 1. Database Configuration
**Location:** `/home2/ipnz/ipnz-live/www/datacenter/database.config.php`

```php
<?php
return [
    'host' => 'localhost',
    'user' => 'ipnz_live_test',
    'password' => 'YOUR_STRONG_PASSWORD', // Generate in cPanel
    'database' => 'ipnz_live_test',
    'charset' => 'utf8mb4',
    'timeout' => 300
];
```

### 2. (Optional) SMTP Configuration
**Location:** `/home2/ipnz/ipnz-live/smtp.config.php` (only if you want to send real emails)

See `smtp.config.example.php` for examples with Mailgun, AWS SES, SendGrid, etc.

## Setup Steps

### Step 1: SSH to Joyful Host
```bash
ssh ipnz@joyful.host
cd /home2/ipnz/ipnz-live
```

### Step 2: Create Database via cPanel
1. Go to cPanel Dashboard → MySQL Databases
2. **Create Database:**
   - Name: `ipnz_live_test`
   - Click "Create"

3. **Create Database User:**
   - Username: `ipnz_live_test`
   - Password: (generate strong password)
   - Click "Create"

4. **Assign User to Database:**
   - Select user `ipnz_live_test`
   - Select database `ipnz_live_test`
   - Check "All Privileges"
   - Click "Make Changes"

### Step 3: Create database.config.php
```bash
mkdir -p /home2/ipnz/ipnz-live/www/datacenter

cat > /home2/ipnz/ipnz-live/www/datacenter/database.config.php << 'EOF'
<?php
return [
    'host' => 'localhost',
    'user' => 'ipnz_live_test',
    'password' => 'YOUR_PASSWORD_HERE',  # Use password from Step 2
    'database' => 'ipnz_live_test',
    'charset' => 'utf8mb4',
    'timeout' => 300
];
EOF

chmod 600 /home2/ipnz/ipnz-live/www/datacenter/database.config.php
```

### Step 4: Import Database Schema
```bash
# Get the password variable
DBPASS='YOUR_PASSWORD_HERE'

# Run migration script via PHP (recommended)
php -r "
  require_once '/home2/ipnz/ipnz-live/www/datacenter/database.php';
  require_once '/home2/ipnz/ipnz-live/www/datacenter/migrate_to_uuid.php';
"

# OR import the SQL file directly
mysql -u ipnz_live_test -p'$DBPASS' ipnz_live_test < /home2/ipnz/ipnz-live/ipnz_db_improved.sql
```

### Step 5: Create Required Directories
```bash
# Create directories
mkdir -p /home2/ipnz/ipnz-live/www/images/avatars
mkdir -p /home2/ipnz/ipnz-live/www/logs

# Set permissions
chmod 755 /home2/ipnz/ipnz-live/www/logs
chmod 755 /home2/ipnz/ipnz-live/www/images/avatars

# Make sure PHP process can write to logs
# This varies by host, but typically:
chown -R nobody:nogroup /home2/ipnz/ipnz-live/www/logs 2>/dev/null || true
```

### Step 6: Deploy via Git
```bash
# Via cPanel Git Version Control:
# 1. Go to cPanel → Git Version Control
# 2. Click "Pull or Deploy"
# 3. Select main branch
# 4. Click "Pull"

# OR manually via SSH:
cd /home2/ipnz/ipnz-live
git pull origin main
```

## Verification Steps

### 1. Check Frontend Loads
```bash
curl -k https://auth-dev.ipnz.live/
# Should return HTML
```

### 2. Test Admin Login
- Navigate to: https://auth-dev.ipnz.live/lists/list_members.php
- Login with: `ipnz-admin` / `@fG3TbzLpm4tzamJ92`
- Should see member list table

### 3. Test Registration
- Navigate to: https://auth-dev.ipnz.live/join
- Fill in test form
- Submit
- Check database for new member:
  ```bash
  mysql -u ipnz_live_test -p'password' ipnz_live_test -e "SELECT * FROM ipnz_members ORDER BY created_at DESC LIMIT 1;"
  ```

### 4. Test Referral Link
- Navigate to: https://auth-dev.ipnz.live/join?ref=KJF9EX
- Should see referral banner (if member tiles show)
- Fill and submit form
- Check referral was tracked:
  ```bash
  mysql -u ipnz_live_test -p'password' ipnz_live_test -e "SELECT * FROM ipnz_referrals ORDER BY created_at DESC LIMIT 1;"
  ```

### 5. Check for Errors
```bash
# Check PHP error log
tail -f ~/.cpanel/logs/php-errors
# or check cPanel error log
```

## What's Included in This Deployment

✅ UUID-based member system
✅ Alphanumeric referral codes (6-char, 2.1B capacity)
✅ Email verification system with audit logging
✅ Referral tracking and attribution
✅ Admin authentication (bcrypt protected)
✅ Production SMTP configuration support
✅ Email logging to database
✅ Random member tiles on homepage
✅ Complete test suite

## Database Tables Created

- `ipnz_members` - Main members table (UUID primary key)
- `ipnz_email_verifications` - Email verification tokens
- `ipnz_email_audit_log` - Email delivery tracking
- `ipnz_referrals` - Referral relationships

## Admin Credentials

- **Username:** `ipnz-admin`
- **Password:** `@fG3TbzLpm4tzamJ92`
- **Access:** https://auth-dev.ipnz.live/lists/list_members.php

## Important Notes

1. **Keep database.config.php private** - Never commit this file
2. **Strong database password** - Generate a complex password for security
3. **Email logging** - Dev mode logs to file, production needs SMTP config
4. **File permissions** - Ensure PHP can write to `/logs` directory
5. **SSL certificate** - Should be set up already on auth-dev.ipnz.live

## Rollback Plan

If anything goes wrong:

```bash
# Revert to previous version
git revert HEAD
git push origin main

# Redeploy via cPanel Git
```

## Support

Issues during deployment? Check:
1. `/home2/ipnz/ipnz-live/www/logs/` for PHP errors
2. cPanel MySQL databases are created
3. `database.config.php` has correct credentials
4. Git pull completed successfully
5. File permissions are correct

Good luck with the deployment! 🚀
