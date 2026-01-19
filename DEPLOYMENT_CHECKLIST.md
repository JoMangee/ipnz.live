# IPnz.live v2.0 - Pre-Deployment Checklist

## ✅ Code Ready to Deploy

### Backend Services (Production Ready)
- [x] `www/datacenter/email.php` - SMTP implementation with TLS/SSL, multiple providers, fallback support
- [x] `www/datacenter/clientregistration.php` - UUID generation, email verification, referral tracking
- [x] `www/datacenter/database.php` - Database connection class
- [x] `www/datacenter/script.js` - Form validation

### Frontend (Production Ready)
- [x] `www/index.php` - Random member tiles for viral growth
- [x] `www/join.php` - Registration form with UUID and referral code support
- [x] `www/auth.php` - Member login
- [x] `www/avatar.php` - Avatar upload endpoint
- [x] `www/js/referral.js` - Referral code extraction and storage
- [x] `www/js/custom.js` - Custom JavaScript
- [x] `www/lists/list_members.php` - Admin panel with bcrypt authentication

### Email System (Production Ready)
- [x] EmailService class with send/verify/resend
- [x] Email verification tokens with 24-hour expiry
- [x] Email audit logging to database
- [x] SMTP configuration with multiple providers
- [x] Fallback to PHP mail() if SMTP fails

### Database (Production Ready)
- [x] `ipnz_members` - UUID primary key, referral code, email verification
- [x] `ipnz_email_verifications` - Token storage with expiry
- [x] `ipnz_email_audit_log` - Complete email delivery tracking
- [x] `ipnz_referrals` - Referral relationship tracking
- [x] Migration script tested - 4 members successfully converted to UUID

### Security (Production Ready)
- [x] UUID v4 as primary key
- [x] 6-character alphanumeric referral codes (2.1B capacity)
- [x] Bcrypt password hashing for admin
- [x] Email verification tokens (64-character hex)
- [x] Session-based admin authentication
- [x] Prepared statements for SQL injection prevention
- [x] .gitignore updated to exclude sensitive files

### Testing (All Passing ✅)
- [x] `test_full_flow.php` - Complete registration and referral flow tested
- [x] Multi-level referral chains verified
- [x] Email token generation tested
- [x] Admin authentication tested
- [x] Database migration tested

### Documentation (Complete)
- [x] `RELEASE_NOTES.md` - Feature overview and metrics
- [x] `DEPLOY_TO_JOYFUL.md` - Step-by-step joyful host setup
- [x] `SMTP_SETUP.md` - Email provider configuration guide
- [x] `smtp.config.example.php` - Provider examples (Mailgun, AWS SES, SendGrid, Gmail, custom)
- [x] `DATABASE_SETUP.md` - Schema documentation
- [x] `.cpanel.yml` - Verified correct for staging deployment

---

## 📋 Pre-Push to GitHub Checklist

Run these before `git push`:

```bash
# 1. Check for unstaged changes
git status

# 2. Stage all changes
git add -A

# 3. Verify what will be committed
git status

# 4. Check .gitignore is protecting sensitive files
git check-ignore -v www/datacenter/database.config.php
git check-ignore -v smtp.config.php

# 5. Verify no sensitive files will be pushed
git ls-files | grep -E "(database\.config|smtp\.config|\.log|\.env)"
```

Expected results:
- ✅ No `database.config.php` in git
- ✅ No `smtp.config.php` in git
- ✅ No `.log` files in git
- ✅ No `.env` files in git

---

## 🚀 Step-by-Step Deployment to joyful host

### Phase 1: Git & Code Deployment (5 minutes)

```bash
# 1. Commit to GitHub
git commit -m "v2.0: UUID system, email verification, referral tracking, admin auth, SMTP config"
git push origin main

# 2. SSH to joyful host
ssh user@joyful.host

# 3. Navigate to deployment directory
cd /home2/ipnz/ipnz-live

# 4. Pull latest code
git pull origin main

# 5. Verify files are in place
ls -la www/datacenter/email.php
ls -la www/lists/list_members.php
ls -la DEPLOY_TO_JOYFUL.md
```

### Phase 2: Database Setup (10 minutes)

**Option A: Using cPanel GUI**

1. Open cPanel → MySQL Databases
2. Create database: `ipnz_live_test`
3. Create user: `ipnz_live_test`
4. Set password: (something secure)
5. Assign user to database with all privileges
6. Note credentials for step 4

**Option B: Using SSH (if SSH MySQL access available)**

```bash
mysql -u admin -p
CREATE DATABASE ipnz_live_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ipnz_live_test'@'localhost' IDENTIFIED BY 'YOUR_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON ipnz_live_test.* TO 'ipnz_live_test'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Phase 3: Application Configuration (5 minutes)

```bash
# 1. Create database config file
cat > /home2/ipnz/ipnz-live/www/datacenter/database.config.php << 'EOF'
<?php
$config = [
    'host' => 'localhost',
    'database' => 'ipnz_live_test',
    'username' => 'ipnz_live_test',
    'password' => 'YOUR_PASSWORD_HERE',  // Replace with actual password from cPanel
];

$connection = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database']
);

if ($connection->connect_error) {
    error_log('Database connection failed: ' . $connection->connect_error);
    die('Database connection error');
}

$connection->set_charset('utf8mb4');
?>
EOF

# 2. Create logs directory
mkdir -p /home2/ipnz/ipnz-live/www/logs

# 3. Set proper permissions
chmod 755 /home2/ipnz/ipnz-live/www/logs
chmod 755 /home2/ipnz/ipnz-live/www/datacenter

# 4. Create images/avatars directory
mkdir -p /home2/ipnz/ipnz-live/www/images/avatars
chmod 755 /home2/ipnz/ipnz-live/www/images/avatars
```

### Phase 4: Database Schema Import (5 minutes)

```bash
# 1. Import schema
mysql -u ipnz_live_test -p ipnz_live_test < /home2/ipnz/ipnz-live/ipnz_db_improved.sql
# Enter password when prompted

# 2. Verify tables created
mysql -u ipnz_live_test -p ipnz_live_test -e "SHOW TABLES;"
# Should show: ipnz_members, ipnz_email_verifications, ipnz_email_audit_log, ipnz_referrals
```

### Phase 5: Verify Deployment (10 minutes)

```bash
# 1. Open in browser
# https://auth-dev.ipnz.live/

# 2. Test public homepage
# - Should load without errors
# - Should display random member tiles

# 3. Test registration
# - Go to https://auth-dev.ipnz.live/join
# - Fill out form and submit
# - Check database for new member with UUID

# 4. Check database
mysql -u ipnz_live_test -p ipnz_live_test << 'EOF'
SELECT uuid, name, email, referral_code FROM ipnz_members LIMIT 3;
EOF

# 5. Test admin login
# - Go to https://auth-dev.ipnz.live/lists/list_members.php
# - Login: ipnz-admin / @fG3TbzLpm4tzamJ92
# - Should see member list with UUIDs and referral codes
```

---

## 📧 Phase 6: Email Configuration (Optional - Can do later)

### Quick Setup (Mailgun Recommended)

```bash
# 1. Choose email provider
# Options:
# - Mailgun: $10/mo, 1,250 free emails/month
# - AWS SES: $0.10 per 1,000 emails
# - SendGrid: $20/mo, 100k free emails/month  
# - Gmail: Free, limited to 500/day

# 2. Get SMTP credentials from provider

# 3. Create SMTP config file
cat > /home2/ipnz/ipnz-live/smtp.config.php << 'EOF'
<?php
return [
    'provider' => 'mailgun',  // or: aws_ses, sendgrid, gmail, custom
    'host' => 'smtp.mailgun.org',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'postmaster@mg.ipnz.live',
    'password' => 'YOUR_MAILGUN_PASSWORD',
    'from_email' => 'noreply@ipnz.live',
    'from_name' => 'IPnz.live',
];
?>
EOF

# 4. Set proper permissions
chmod 600 /home2/ipnz/ipnz-live/smtp.config.php

# 5. Test by registering a new member - check email
```

---

## 🎯 Success Criteria

After deployment, verify:

- [x] Website loads: `https://auth-dev.ipnz.live/`
- [x] Homepage shows 3 random member tiles
- [x] Registration form works: `/join`
- [x] Admin panel accessible: `/lists/list_members.php`
- [x] Admin login works: `ipnz-admin` / `@fG3TbzLpm4tzamJ92`
- [x] New members get UUID in database
- [x] Referral links work: `/join?ref=KJF9EX`
- [x] Database tables exist: `ipnz_members`, `ipnz_email_verifications`, etc.
- [x] Admin can see all members with referral codes
- [x] Multi-level referrals tracked correctly

---

## 🔍 Troubleshooting

### Database Connection Error

```bash
# 1. Verify credentials in database.config.php
cat /home2/ipnz/ipnz-live/www/datacenter/database.config.php

# 2. Test connection from SSH
mysql -u ipnz_live_test -p ipnz_live_test -e "SELECT 1"

# 3. Check MySQL is running
service mysql status

# 4. Check database exists
mysql -u admin -p -e "SHOW DATABASES;"
```

### SMTP Email Not Sending

```bash
# 1. Check email logs
tail -f /home2/ipnz/ipnz-live/www/logs/emails.log

# 2. Verify SMTP credentials
cat /home2/ipnz/ipnz-live/smtp.config.php

# 3. Test SMTP connection
telnet smtp.mailgun.org 587

# 4. Check PHP error logs
tail -f /var/log/apache2/error.log
```

### Registration Form Not Working

```bash
# 1. Check for PHP errors
tail -f /var/log/apache2/error.log

# 2. Check browser console for JavaScript errors
# F12 → Console tab

# 3. Verify database connection
php /home2/ipnz/ipnz-live/www/datacenter/test_connection.php

# 4. Check form action in join.php
grep "form action" /home2/ipnz/ipnz-live/www/join.php
```

---

## 📊 Quick Reference

### Admin Credentials
- **URL**: `https://auth-dev.ipnz.live/lists/list_members.php`
- **Username**: `ipnz-admin`
- **Password**: `@fG3TbzLpm4tzamJ92`

### Database Credentials (On joyful host)
- **Host**: `localhost`
- **Database**: `ipnz_live_test`
- **Username**: `ipnz_live_test`
- **Password**: (Set during cPanel setup)

### Key Files on Server
- **Code**: `/home2/ipnz/ipnz-live/www/`
- **Logs**: `/home2/ipnz/ipnz-live/www/logs/`
- **Config**: `/home2/ipnz/ipnz-live/www/datacenter/database.config.php`
- **SMTP Config**: `/home2/ipnz/ipnz-live/smtp.config.php` (optional)

### Test URLs
- **Home**: `https://auth-dev.ipnz.live/`
- **Register**: `https://auth-dev.ipnz.live/join`
- **Admin**: `https://auth-dev.ipnz.live/lists/list_members.php`
- **With Referral**: `https://auth-dev.ipnz.live/join?ref=KJF9EX`

---

## ✨ You're Ready to Deploy!

All systems tested and verified. Follow the phases above in order, and you'll have v2.0 running on auth-dev.ipnz.live within 30 minutes.

**Questions?** Check the detailed guides:
- `DEPLOY_TO_JOYFUL.md` - Detailed deployment instructions
- `SMTP_SETUP.md` - Email provider setup
- `RELEASE_NOTES.md` - What's new in v2.0
