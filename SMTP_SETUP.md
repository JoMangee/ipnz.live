# IPnz.live Production SMTP Configuration Guide

## Overview

The email system supports three ways to configure SMTP for production:

1. **Environment Variables** (Recommended for Docker)
2. **Configuration File** (smtp.config.php)
3. **PHP mail() fallback** (Not recommended)

## How Email Routing Works

### Development Mode (localhost)
- Emails are logged to `/var/www/html/logs/emails.log`
- No actual emails are sent
- Perfect for testing the flow

### Production Mode
- Emails are sent via configured SMTP server
- Falls back to PHP `mail()` if SMTP fails
- Emails are logged to database audit table

## Configuration Methods

### Method 1: Environment Variables (Docker) - RECOMMENDED

Edit `docker-compose.yml` and add to the `ipnz-web` service:

```yaml
environment:
  - APP_ENV=production
  - SMTP_HOST=smtp.mailgun.org
  - SMTP_PORT=587
  - SMTP_ENCRYPTION=tls
  - SMTP_USERNAME=postmaster@mail.ipnz.live
  - SMTP_PASSWORD=your-api-key-here
  - MAIL_FROM_EMAIL=noreply@mail.ipnz.live
  - MAIL_FROM_NAME=IPnz.live
```

Then restart containers:
```bash
docker-compose down
docker-compose up -d
```

### Method 2: Configuration File

1. Copy `smtp.config.example.php` to `smtp.config.php` (same directory level as www/)
2. Edit with your SMTP credentials:

```php
return [
    'host' => 'smtp.mailgun.org',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'postmaster@mail.ipnz.live',
    'password' => 'your-mailgun-api-key',
    'from_email' => 'noreply@mail.ipnz.live',
    'from_name' => 'IPnz.live'
];
```

3. **IMPORTANT:** Add to `.gitignore`:
```
smtp.config.php
```

## Recommended Email Providers

### Mailgun (Best for high volume)
- **Host:** `smtp.mailgun.org`
- **Port:** `587` (TLS) or `465` (SSL)
- **Username:** `postmaster@mail.yourdomain.com`
- **Password:** SMTP Password from Mailgun dashboard
- **Website:** https://www.mailgun.com/
- **Cost:** Free tier includes 300 emails/month, then $0.50 per 1000

### AWS SES (Cost-effective)
- **Host:** `email-smtp.us-east-1.amazonaws.com`
- **Port:** `587` (TLS)
- **Username/Password:** Generated in AWS console
- **Website:** https://aws.amazon.com/ses/
- **Cost:** $0.10 per 1000 emails

### SendGrid
- **Host:** `smtp.sendgrid.net`
- **Port:** `587` (TLS)
- **Username:** `apikey`
- **Password:** `SG.your-api-key`
- **Website:** https://sendgrid.com/
- **Cost:** Free tier 100 emails/day, then $15-19/month

## Step-by-Step Setup Example (Mailgun)

### 1. Create Mailgun Account
- Sign up at https://www.mailgun.com/
- Create account and verify domain

### 2. Get SMTP Credentials
- Go to Mailgun Dashboard → Sending → Domain
- Click your domain
- Find SMTP Credentials section
- Copy:
  - **Default SMTP Login:** `postmaster@mail.ipnz.live`
  - **Default Password:** (generate or use existing)

### 3. Configure in Docker
Add to `docker-compose.yml`:

```yaml
services:
  ipnz-web:
    environment:
      - SMTP_HOST=smtp.mailgun.org
      - SMTP_PORT=587
      - SMTP_ENCRYPTION=tls
      - SMTP_USERNAME=postmaster@mail.ipnz.live
      - SMTP_PASSWORD=your-mailgun-password
      - MAIL_FROM_EMAIL=noreply@mail.ipnz.live
      - MAIL_FROM_NAME=IPnz.live
```

### 4. Restart Docker
```bash
docker-compose down
docker-compose up -d
```

### 5. Test Email Sending
Create a test member via the registration form and check:
- Email log: `/var/www/html/logs/emails.log`
- Database audit: `ipnz_email_audit_log` table
- Your inbox (if verification email was sent)

## Verification Email Testing

Once configured:

1. Go to https://ipnz.live/join
2. Fill in registration form
3. Submit
4. Check:
   - Database for new member with UUID
   - `ipnz_email_audit_log` table for delivery status
   - Your email inbox for verification link
   - Click link to verify email

## Email Event Tracking

### Database Audit Log (`ipnz_email_audit_log`)
```sql
SELECT * FROM ipnz_email_audit_log ORDER BY created_at DESC;
```

Columns:
- `status`: 'pending', 'sent', 'failed', 'bounced', 'rejected'
- `error_message`: Details if failed
- `sent_at`: Timestamp when email was sent
- `member_uuid`: Who received it

### Environment Detection
The system automatically detects:
- **Development:** `localhost` or `APP_ENV=local` → Logs only
- **Production:** Any other hostname → Sends via SMTP

## Troubleshooting

### Emails not sending
1. Check `docker ps` - container running?
2. Check logs: `docker logs ipnz-web`
3. Check database audit log for errors
4. Verify SMTP credentials are correct

### SMTP Connection Failed
1. Check hostname and port correct
2. Check firewall allows outgoing on that port
3. Enable TLS on port 587 (most common)
4. Try different encryption method

### Timeout Errors
- Increase timeout or check network connectivity
- Some SMTP providers block connections from certain IPs

### "Authentication Failed"
- Verify username and password are correct (copy-paste carefully)
- Some providers need app-specific passwords
- Check if credentials are URL-encoded characters

## Security Best Practices

1. **Never commit credentials** - Always use environment variables or config file
2. **Add smtp.config.php to .gitignore**
3. **Use SMTP_USERNAME and SMTP_PASSWORD** - Not plain text
4. **Use TLS encryption** - Port 587
5. **Rotate credentials regularly** - If provider supports it

## Email Log Cleanup

Over time, the audit log will grow. Archive/delete old entries:

```sql
-- Delete logs older than 90 days
DELETE FROM ipnz_email_audit_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Archive to backup table first (safer)
CREATE TABLE ipnz_email_audit_log_archive AS
SELECT * FROM ipnz_email_audit_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM ipnz_email_audit_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

## Monitoring

Create a simple monitoring query to check recent failures:

```sql
SELECT 
  COUNT(*) as failed_count,
  recipient_email,
  error_message
FROM ipnz_email_audit_log
WHERE status = 'failed'
  AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY error_message;
```

## Next Steps

1. Choose email provider (Mailgun recommended)
2. Get SMTP credentials
3. Configure environment variables or config file
4. Test registration and email verification
5. Monitor audit log for any issues
