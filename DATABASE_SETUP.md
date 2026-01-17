# IPnz.live Database Setup Guide

## 🔒 Security First Setup

### 1. Database Configuration

**DO NOT use the default database.php settings in production!**

#### Option A: Using Config File (Recommended for Development)

1. Copy the example config:
   ```bash
   cp www/datacenter/database.config.example.php www/datacenter/database.config.php
   ```

2. Edit `database.config.php` with your credentials:
   ```php
   return [
       'host' => 'localhost',
       'user' => 'your_database_user',      // Change this!
       'password' => 'your_secure_password', // Change this!
       'database' => 'ipnz_db',
       'charset' => 'utf8mb4',
       'timeout' => 300
   ];
   ```

3. The file is automatically ignored by git (safe from commits)

#### Option B: Using Environment Variables (Recommended for Production)

Set these environment variables:
```bash
DB_HOST=localhost
DB_USER=your_database_user
DB_PASSWORD=your_secure_password
DB_NAME=ipnz_db
DB_CHARSET=utf8mb4
DB_TIMEOUT=300
```

### 2. Database Setup

#### New Installation

Use the improved schema:
```bash
mysql -u your_user -p < ipnz_db_improved.sql
```

This creates:
- `ipnz_members` - Member registrations
- `ipnz_contacts` - Contact form submissions  
- `ipnz_member_activity` - Activity audit log
- Views for active/pending members and new contacts

#### Migrating Existing Database

If you have existing data:
```bash
mysql -u your_user -p ipnz_db < migration_to_improved_schema.sql
```

This safely:
- Backs up old table as `ipnz_members_old`
- Migrates members (status=NULL/0) to new structure
- Migrates contacts (status=1) to `ipnz_contacts` table
- Converts join_type from 0/1 to 'early_access'/'standard'
- Strips "Avatar URL: " prefix from avatar URLs

After confirming migration success:
```sql
DROP TABLE ipnz_members_old;
```

### 3. File Permissions

Ensure the web server can read config files:
```bash
chmod 600 www/datacenter/database.config.php
chown www-data:www-data www/datacenter/database.config.php
```

### 4. Verify Setup

Test your connection:
```bash
php -r "require 'www/datacenter/database.php'; echo 'Connected successfully!';"
```

## 📋 Schema Overview

### ipnz_members
- Email addresses **can be shared** (family accounts)
- New signups start with `status='pending'`
- Email verification ready (use `email_verified` flag)
- Soft delete support (`deleted_at` timestamp)

### ipnz_contacts  
- Separate table for contact form submissions
- No duplicate detection (contacts ≠ members)

### Views
- `view_active_members` - Public display (status='active')
- `view_pending_members` - Admin approval queue
- `view_new_contacts` - Unread messages

## ⚠️ Important Notes

1. **Never commit** `database.config.php` or `ipnz_db.sql` (with real data)
2. **Always use** prepared statements when adding queries
3. **Test email verification** before production (TODO in clientregistration.php)
4. **Set up backups** - automated SQL dumps to secure location
5. **Monitor** `ipnz_member_activity` for suspicious patterns

## 🆘 Troubleshooting

**Connection fails:**
- Check credentials in config file or environment variables
- Verify MySQL is running: `systemctl status mysql`
- Check user permissions: `SHOW GRANTS FOR 'user'@'localhost';`

**Migration issues:**
- Old data remains in `ipnz_members_old` table
- Check error logs: `tail -f /var/log/mysql/error.log`

**Email sharing not working:**
- Confirm UNIQUE constraint removed: `SHOW CREATE TABLE ipnz_members;`
- Should show `KEY idx_email` not `UNIQUE KEY`
