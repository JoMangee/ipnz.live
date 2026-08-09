# cPanel Deployment Guide for IPnz.live

## Two Deployment Environments

### Production: ipnz.live
- **Path:** `/home2/ipnz/public_html`
- **Config:** `.cpanel.yml` (in repository root)
- **Database:** Production database
- **URL:** https://ipnz.live

### Staging: auth-dev.ipnz.live  
- **Path:** `/home2/ipnz/ipnz-live/www`
- **Config:** `.cpanel.staging.yml` (rename to `.cpanel.yml` in staging repo)
- **Database:** Test/staging database (separate from production!)
- **URL:** https://auth-dev.ipnz.live

## Initial Setup (First Deployment Only)

### For STAGING (auth-dev.ipnz.live)

1. **In cPanel Git Version Control:**
   - Create repository pointing to staging branch (e.g., `dev` or `staging`)
   - Set deployment path: `/home2/ipnz/ipnz-live/www`
   - In your local repo, copy the staging config:
     ```bash
     cp .cpanel.staging.yml .cpanel.yml
     git add .cpanel.yml
2. **Create PRODUCTION database config:**

```bash
ssh user@your-server.com
cd /home2/ipnz/public_html/datacenter
cp database.config.example.php database.config.php
nano database.config.php
```

Update with PRODUCTION credentials:
```php
return [
    'host' => 'localhost',
    'user' => 'ipnz_prod',              // Production database user
    'password' => 'secure_prod_pass',    // Strong production password!
    'database' => 'ipnz_db',             // Production database
    'charset' => 'utf8mb4',
    'timeout' => 300
];
```

3. **Set secure permissions:**

```bash
chmod 600 database.config.php
```

4. **Import to PRODUCTION database:**

3. **Import to STAGING database:**
```bash
mysql -u ipnz_staging -p ipnz_db_staging < ~/ipnz_db_improved.sql
```

### For PRODUCTION (ipnz.live)

1. **In cPanel Git Version Control:**
   - Create repository pointing to `main` branch
   - Set deployment path: `/home2/ipnz/public_html`
   - The `.cpanel.yml` in main branch is already configured for production

2. **Create PRODUCTION database config:**

```bash
cp database.config.example.php database.config.php
nano database.config.php
```

Update with your actual credentials:
```php
return [
    'host' => 'localhost',
    'user' => 'ipnz_dbuser',           // Your cPanel database user
    'password' => 'your_password_here', // Your database password
    'database' => 'ipnz_db',            // Your database name
    'charset' => 'utf8mb4',
    'timeout' => 300
];
```

### 3. Set secure permissions

```bash
chmod 600 database.config.php
```

### 4. Import database schema

Via cPanel phpMyAdmin:
- Select your database
- Click "Import"
- Upload `ipnz_db_improved.sql`
- Click "Go"

Or via SSH:
```bash
mysql -u ipnz_dbuser -p ipnz_db < ~/ipnz_db_improved.sql
```Staging Deployment Workflow (Recommended)

1. **Develop and test locally**
2. **Push to staging branch:**
   ```bash
   git checkout staging
   git merge develop  # or your feature branch
   git push origin staging
   ```

3. **Deploy to auth-dev.ipnz.live:**
   - cPanel → Git Version Control → Manage (auth-dev repo)
   - Pull or Deploy tab → Update from Remote
   - Deploy HEAD Commit

4. **Test on staging:** https://auth-dev.ipnz.live
   - Test join form with test data
   - Test contact form
   - Verify database writes to staging DB
   - Check error logs

5. **If staging tests pass, deploy to production:**
   ```bash
   git checkout main
   git merge staging
   git push origin main
   ```

6. **Deploy to ipnz.live:**
   - cPanel → Git Version Control → Manage (ipnz.live repo)
   - Pull or Deploy tab → Update from Remote
   - Deploy HEAD Commit

### 

## Deploying Updates

### Via cPanel Git Version Control Interface

1. Go to: cPanel → Git Version Control
2. Click "Manage" on your repository
3. Click "Pull or Deploy" tab
4. Click "Update from Remote" (pulls latest from GitHub)
5. Click "Deploy HEAD Commit" (runs .cpanel.yml deployment)

### Via SSH

```bash
cd /home2/ipnz/ipnz-live
git pull origin main
/usr/local/cpanel/3rdparty/bin/git cpanel deploy
```

## Post-Deployment Checklist

- [ ] Test database connection: visit https://yourdomain.com/
- [ ] Check join form: https://yourdomain.com/join.php
- [ ] Verify contact form works
- [ ] Check file permission

### Staging Environment
```
/home2/ipnz/
├── ipnz-live/
│   ├── .git/                # Git repo for staging (branch: staging)
│   └── www/                 # Deployed staging files
│       ├── index.php
│       ├── join.php
│       ├── datacenter/
│       │   ├── database.config.php  ← Staging DB credentials
│       │   └── database.php
│       └── ...
```
**Access:** https://auth-dev.ipnz.live

### Production Environment
```
/home2/ipnz/
├── public_html/             # Deployed production files
│   ├── .git/                # Git repo for production (branch: main)
│   ├── index.php
│   ├── join.php
│   ├── datacenter/
│   │   ├── database.config.php  ← Production DB credentials
│   │   └── database.php
│   └── ...
```
**Access:** https://ipnz.live

## Branch Strategy

```
main (production)
  ↑
  └─ staging (auth-dev)
       ↑
       └─ develop / feature branches
```

- **main** → ipnz.live (production)
- **staging** → auth-dev.ipnz.live (staging/testing)
- **develop** → local development

## Security Notes

⚠️ **CRITICAL:**
- Staging and Production use **SEPARATE databases**
- **NEVER** use production database credentials in staging
- Test data in staging will **NOT** affect production
- Both `database.config.php` files must be created manually
- **NEVER** commit `database.config.php` to git

## File Structure on Servers on datacenter/database.config.php (should be 600)
- [ ] Test avatar creation with Ready Player Me
- [ ] Check error logs: cPanel → Errors

## Troubleshooting

### "Cannot deploy" error
- Ensure `.cpanel.yml` exists in repository root
- No uncommitted changes on your branch
- Push your commits to GitHub first

### Database connection fails
- Verify `database.config.php` exists and has correct credentials
- Check database user permissions in cPanel
- Verify database name matches in cPanel and config

### Permission denied errors
```bash
chmod 755 /home2/ipnz/public_html/datacenter
chmod 600 /home2/ipnz/public_html/datacenter/database.config.php
```

### PHP errors showing
Edit `.htaccess` in public_html:
```apache
php_flag display_errors Off
php_flag log_errors On
```

## Security Notes

⚠️ **NEVER commit `database.config.php` to git!**
- It contains sensitive credentials
- Already in `.gitignore`
- Must be created manually on server
- Set permissions to 600 (owner read/write only)

## File Structure on Server

```
/home2/ipnz/
├── ipnz-live/           # Git repository (cPanel managed)
└── public_html/         # Deployed web files
    ├── index.php
    ├── join.php
    ├── auth.php
    ├── datacenter/
    │   ├── database.config.php  ← Create this manually!
    │   ├── database.php
    │   └── clientregistration.php
    ├── images/
    │   └── avatars/
    └── ...
```

## Support

For deployment issues:
- Check cPanel error logs
- Review deployment output in cPanel Git interface
- Check PHP error logs: `tail -f ~/public_html/error_log`
