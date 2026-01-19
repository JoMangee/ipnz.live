# IPnz.live v2.0 — The User Journey ✨

> **Last updated:** January 20, 2026  
> **Version:** 2.0 (UUID Architecture with Email Verification)  
> **What is this?** A walkthrough of how IPnz.live creates awesome member experiences with privacy-respecting referrals, secure joins, email verification, and seamless updates.

---

## 🌟 The Story: From Discovery to Advocacy

### Act 1: Discovery (Someone Shares the Love)

**Sarah** is already a member. She loves the Internet Party movement and wants her friend **Alex** to join. Sarah clicks the **X share icon** on the homepage:

1. **Share Link Generated**  
   - Sarah's referral code `GEWEEN` (6-character alphanumeric) is embedded in the URL: `https://IPnz.live?ref=GEWEEN`  
   - Tweet/Post prefills with: _"Join the Internet Party movement! 🚀 #IPnz #InternetParty"_ + the referral link  
   - Opens in a new window (secure, no `opener` access) with fallback from `x.com` → `twitter.com`

2. **Alex Clicks the Link**  
   - Lands on IPnz.live homepage with `?ref=GEWEEN` in the URL  
   - `referral.js` captures the `ref` parameter and stores it in `localStorage`:  
     - `ipnz_incoming_ref = "GEWEEN"`  
     - Legacy format `m42` still supported for backwards compatibility

3. **Welcome Banner Appears** 🎉  
   - Alex sees a vibrant banner at the top:  
     > _"Hey! Someone thought this was awesome and invited you to join IPnz.live!  
     > Be part of the Internet Party movement. Join us and get your own referral link too."_  
   - Big **"Join Now"** button, plus a dismiss (×) link  
   - Banner only shows if:  
     - `?ref=` was present in URL, AND  
     - Alex hasn't joined yet (no `ipnz_member_uuid` in localStorage)

---

### Act 2: Joining (Alex Becomes a Member)

**Alex clicks "Join Now"** → lands on `/join`

1. **Form Loads with Hidden Referrer Tracking**  
   - `referral.js` populates the hidden `referrer_code` field with `GEWEEN` from localStorage  
   - Alex fills in: Name, Email, Phone, Join Type (Early Access / Standard), optional message  
   - Avatar is optional — skipping it uses a friendly default Ready Player Me avatar

2. **Submit → Backend Magic** (`clientregistration.php`)  
   - **UUID Generation**: Creates globally unique identifier (e.g., `c4ca4238-a0b9-3382-8dcc-509a6f75849b`)
   - **Referral Code Generation**: Creates 6-character alphanumeric code (e.g., `PU2VSQ`)
   - **Validation**: Email format check (preserves form data on error)  
   - **Referrer Credit**: Checks if `referrer_code=GEWEEN` exists in `ipnz_members` → if valid, records it in `ipnz_referrals` table
   - **Email Verification**: Creates 64-character token, sends verification email
   - **Insert**: New member row created:
     ```sql
     INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, 
                                additional_request, avatar_url, has_custom_avatar, 
                                status, email_verified)
     VALUES ('c4ca4238-a0b9-3382-8dcc-509a6f75849b', 'PU2VSQ', 'Alex', 
             'alex@example.com', '0212345678', 'early_access', '', 
             'https://default-avatar.png', 0, 'pending', 0)
     ```
   - **Referral Tracking**: If valid referrer code provided:
     ```sql
     INSERT INTO ipnz_referrals (referrer_uuid, referral_uuid, referral_code)
     VALUES ('sarah-uuid', 'alex-uuid', 'GEWEEN')
     ```
   - **Success Response**:  
     - Green banner: _"Sign up Success! A verification email has been sent to alex@example.com. Your referral link: https://IPnz.live?ref=PU2VSQ"_  
     - `localStorage` stores:
       - `ipnz_ref = "PU2VSQ"` (Alex's new referral code)  
       - `ipnz_member_uuid = "c4ca4238-a0b9-3382-8dcc-509a6f75849b"`  
       - `ipnz_member_profile = {name, email, phone, join_type, ...}` (for prefill on return visits)

3. **Sarah Gets Credit** 🎖️  
   - `ipnz_referrals` table now has a row linking Sarah's UUID to Alex's UUID
   - Future analytics queries can count how many people Sarah referred  
   - Sarah's impact is tracked without exposing Alex's personal info
   - Multi-level referral chains preserved (Alex can refer others, creating viral growth)

4. **Email Verification** 📧
   - Alex receives email with verification link: `/verify?token=abc123...`
   - Token expires in 24 hours
   - Click link → `status` changes from `pending` to `active`
   - Email audit log tracks delivery status (pending/sent/failed/bounced)

---

### Act 3: Returning (Alex Updates Their Profile)

**Alex visits `/join` again** (maybe to update their phone or avatar):

1. **Auto-Prefill Magic**  
   - `join.php` client-side script reads `ipnz_member_profile` from localStorage  
   - All fields populate with Alex's existing data  
   - Fields are **disabled** by default (safe, read-only view)  
   - Submit button reads: **"Update details"**  
   - Hidden `member_uuid` field set to Alex's UUID

2. **"Edit details" Link**  
   - Clicking this re-enables Name, Phone, Message, Avatar, Join Type fields  
   - Email stays disabled (changes require verification for security)  
   - Button changes to: **"Save changes"**

3. **Submit → Update Flow**  
   - `clientregistration.php` sees `member_uuid` in POST data  
   - Runs `UPDATE` query:
     ```sql
     UPDATE ipnz_members 
     SET name=?, phone=?, join_type=?, additional_request=?, avatar_url=?, has_custom_avatar=?
     WHERE uuid=? AND email=? AND deleted_at IS NULL
     ```
   - Success: _"Details updated successfully"_  
   - `localStorage` profile refreshed with new values

---

## 🔒 Privacy & Security Foundations

### What We Protect

1. **UUID Primary Keys**  
   - Sequential member IDs **no longer exposed** (v2.0 upgrade)
   - UUIDs prevent enumeration attacks (can't guess valid member IDs)
   - Legacy ID column preserved for future bulk imports (nullable)

2. **Alphanumeric Referral Codes**  
   - 6-character codes (e.g., `GEWEEN`, `PU2VSQ`) replace sequential IDs
   - 2.1 billion unique combinations (36^6)
   - Character set excludes ambiguous characters (no 0/O, 1/I/l)
   - Short and memorable for sharing

3. **Email Verification System**  
   - Tokenized links with 64-character hex codes
   - 24-hour expiration for security
   - Email audit trail tracks all send attempts
   - Status tracking: pending/sent/failed/bounced/rejected
   - Resend capability with rate limiting

4. **Shared Email Addresses**  
   - Email is **not unique** in the schema (families, couples can share an address)  
   - Duplicate email signup → soft notification (no personal info leaked):  
     > _"Note: This email address is associated with other accounts."_  
   - Future: send security alert to existing account holders (planned)

5. **Prepared Statements Everywhere**  
   - All DB queries use `mysqli` prepared statements (`bind_param`)  
   - Zero SQL injection risk in `clientregistration.php`, views, etc.

6. **External Links**  
   - All outbound links use `rel="noopener"` (prevents reverse tabnabbing)  
   - Referrer header preserved for analytics while blocking malicious `window.opener` access

7. **Credentials Externalized**  
   - `database.php` loads from `database.config.php` (local dev) or env vars (production)  
   - `.gitignore` keeps secrets out of version control
   - SMTP credentials in `smtp.config.php` (also gitignored)

8. **Admin Authentication**  
   - Bcrypt password hashing (never plain text)
   - Session-based authentication
   - Admin panel separate from public views: `/lists/list_members.php`

---

## 🎨 User Experience Highlights

### Homepage

- **JOIN Button**: Big, bold CTA in navbar (mobile + desktop)  
- **Share Icons**: X (formerly Twitter), Apple, Instagram, YouTube, Pinterest  
- **Members Section**: Live grid of active members (fetched from `view_active_members`)  
- **About Section**: Link to [IPA Constitution](https://ip.org.nz/constitution) for transparency  
- **Referral Banner**: Dynamically appears for referred visitors (dismissible)

### Join Page

- **Progressive Disclosure**:  
  - Start simple: Name, Email, Phone, Join Type  
  - Advanced: Ready Player Me avatar creation (collapsible iframe)  
  - Optional message field for requests
- **Visual Feedback**:  
  - Green success banner with referral link  
  - Red error banner with preserved form data  
  - Disabled fields on return visits (toggle with "Edit details")

---

## 🚀 Viral Growth Loop

```
Sarah (UUID: abc-123, Code: GEWEEN)
  ↓ Shares link with ref=GEWEEN
Alex (Visitor)
  ↓ Sees welcome banner
Alex Joins → (UUID: def-456, Code: PU2VSQ)
  ↓ Referral tracked in ipnz_referrals table
Alex Shares with Jordan (ref=PU2VSQ)
  ↓ ...and the cycle continues
```

**Key Metrics Trackable**:
- Referrals by member (via `ipnz_referrals` join table)  
- Conversion rate (referred visitors → verified members)  
- Referral chain depth (multi-level tracking with UUIDs)
- Email verification rates (pending → active status transitions)
- Top advocates (most referrals in period)

---

## 🛠️ Technical Architecture

### Database Schema (v2.0)

**Core Tables**:
- `ipnz_members`: UUID (PK), referral_code (unique), name, email, phone, join_type, avatar_url, has_custom_avatar, status, email_verified, timestamps
  - `id` column nullable (reserved for legacy imports)
  - `uuid` is primary key (char 36)
  - `referral_code` is unique 6-char alphanumeric
- `ipnz_referrals`: Tracks who referred whom (referrer_uuid, referral_uuid, referral_code, timestamp)
- `ipnz_email_verifications`: Email verification tokens (member_uuid, token, expires_at, verified_at)
- `ipnz_email_audit_log`: Email delivery tracking (recipient, type, status, error_message)
- `ipnz_contacts`: Contact form submissions (separate from members)  
- `ipnz_member_activity`: Login, profile updates, avatar changes

**Views for UI**:
- `view_active_members`: Public member grid (status='active', email_verified=1, not deleted)  
- `view_pending_members`: Admin approval queue (status='pending')
- `view_new_contacts`: Unread contact messages

**Indexes & FK**:
- Primary key on `uuid` (not `id`)
- Unique indexes on `referral_code` and legacy `id`
- Foreign keys in `ipnz_referrals` table linking referrer/referral UUIDs
- Cascading delete: if member deleted, referrals preserved (SET NULL)

### Client-Side JavaScript

**`referral.js`** (auto-loaded on all pages):
- Captures `?ref=` from URL → stores in localStorage  
- Supports both new format (`GEWEEN`) and legacy (`m42`)
- Populates hidden `referrer_code` field on join form  
- Shows/hides welcome banner based on member status (checks `ipnz_member_uuid`)
- Dismissal persists via `ipnz_ref_banner_dismissed` flag

**`share.js`** (loaded on pages with share icon):
- Builds X/Twitter intent URL with referral code from localStorage  
- Prefills tweet text, hashtags, via handle  
- Opens in new window (secure)

### Backend PHP

**`clientregistration.php`** (v2.0):
- Generates UUID v4 for new members (`generateUUID()`)
- Generates 6-character alphanumeric referral code via `EmailService->generateReferralCode()`
- Validates inputs (email format, referrer code existence)  
- Maps radio `join-type` (0/1) → ENUM (`early_access`/`standard`)  
- Default avatar if none provided  
- INSERT with `uuid`, `referral_code`, `status='pending'` for new members
- Creates email verification token (64-char hex, 24hr expiry)
- Sends verification email via `EmailService->sendVerificationEmail()`
- Tracks referral in `ipnz_referrals` table if valid referrer code
- UPDATE with `member_uuid` match for returning members  
- Stores profile in `localStorage` via inline `<script>` on success

**`email.php`** (EmailService class):
- `sendVerificationEmail()`: Styled HTML template with token link
- `sendViaSmtp()`: Native SMTP implementation (TLS/SSL)
- `createVerificationToken()`: Generates 64-char random token
- `verifyToken()`: Validates and marks email as verified
- `logEmailAttempt()`: Audit trail for all email sends
- `generateReferralCode()`: Creates unique 6-char alphanumeric codes
- Supports multiple providers: Mailgun, AWS SES, SendGrid, Gmail, custom
- Automatic dev/prod detection (localhost → file logging, production → SMTP)

**`verify.php`** (NEW in v2.0):
- Validates email verification tokens
- Updates `email_verified=1` and `status='active'`
- Marks token as used (`verified_at` timestamp)

**`resend.php`** (NEW in v2.0):
- Resends verification email to members
- Rate-limited to prevent abuse
- Creates new token if previous expired

**`lists/list_members.php`** (Admin panel):
- Bcrypt-protected authentication
- Session-based login
- Shows all members with UUIDs, referral codes, verification status
- Admin credentials: `ipnz-admin` / (hashed password)

**`database.php`**:
- Connection abstraction with error handling  
- Charset set to `utf8mb4` (emoji-safe, prevents charset injection)  
- Configurable timeout for long queries

---

## 📦 Deployment & Dev Workflow

### Local Development

```powershell
# Start Docker stack (Apache+PHP 8.2, MariaDB 10.4)
docker-compose up -d

# Import schema (Windows PowerShell)
Get-Content ipnz_db_improved.sql | docker exec -i ipnz-db mysql -uipnz -pipnz_dev_password ipnz_live

# Seed test data
Get-Content scripts/seed.sql | docker exec -i ipnz-db mysql -uipnz -pipnz_dev_password ipnz_live

# View logs
docker logs ipnz-web --tail 50

# Access site
https://localhost (trusted cert via trust-cert.bat)
```

### Staging & Production

- **cPanel Git Deployment**: `.cpanel.yml` auto-deploys on push  
- **LiteSpeed Web Server**: Staging uses LiteSpeed (not Apache)
  - `.htaccess` updated with `RewriteBase /` for compatibility
  - Specific routes placed BEFORE extension removal rules
- **Environment Configs**: Separate DB credentials for staging/live (env vars in cPanel or config files)
- **Staging**: `https://auth-dev.ipnz.live` (ahmad branch)
- **Production**: `https://ipnz.live` (main branch)

---

## 🧪 Testing Strategy

### Automated (Playwright)

- **`tests/e2e/home.spec.ts`**: Share icon builds correct intent URL  
- **`tests/e2e/join.spec.ts`**: Validation errors, form persistence, referral localStorage  
- **CI Workflow** (`.github/workflows/e2e.yml`): Runs on every push, uses Docker services

### Manual Test Plan (`TESTS.md`)

**Critical Paths**:
1. Referral capture: Visit `/?ref=GEWEEN` → banner appears → localStorage set  
2. Join with referral: Fill form → submit → UUID + referral code generated → tracked in DB
3. Email verification: Click link in email → status changes to active
4. Return visit: Prefilled form → edit → update → DB reflects changes  
5. Share link: Click X icon → tweet prefills with `?ref=YOUR_CODE`
6. Admin panel: Login → view members → see UUIDs and referral codes

**Edge Cases**:
- Invalid referrer code → ignored, join still succeeds  
- Shared email → soft notification, no PII leak  
- Dismiss banner → doesn't reappear (localStorage flag)
- Expired verification token → resend flow works
- Multiple referral levels → all tracked correctly

---

## 🎯 What Makes This Awesome

### For Members Like Sarah

- **Effortless Sharing**: One-click X share with auto-generated referral link  
- **Credit Where Due**: See who you've brought in via admin dashboard (UUID-based tracking)
- **No Spam**: Privacy-first — we never expose referrer info to referred users
- **Unique Codes**: Memorable 6-character codes easy to share verbally

### For New Visitors Like Alex

- **Warm Welcome**: Friendly banner acknowledging they were invited  
- **Quick Join**: Simple form, optional avatar, clear CTAs  
- **Email Verification**: Professional verification flow with resend capability
- **Instant Gratification**: Get your own referral link immediately after joining

### For the Movement

- **Viral Growth**: Every member becomes an advocate with their unique link  
- **Data Insights**: Track referral chains, identify top advocates, monitor verification rates
- **Privacy Compliance**: GDPR-ready consent tracking, soft deletes, audit trail
- **Security**: UUID primary keys prevent enumeration attacks
- **Scalability**: 2.1 billion unique referral codes support massive growth

---

## 🗺️ Roadmap: Next Level Features

### ✅ Phase 2 (COMPLETED - v2.0)

- [x] **UUID Primary Keys**: Security hardening to prevent enumeration
- [x] **Alphanumeric Referral Codes**: 6-character codes (2.1B capacity)
- [x] **Email Verification**: Tokenized links with 24-hour expiry  
- [x] **Email Audit Logging**: Complete delivery tracking
- [x] **Referral Tracking Table**: Separate table for multi-level referrals
- [x] **Admin Authentication**: Bcrypt-protected admin panel
- [x] **Production SMTP**: Multi-provider support with fallback

### Phase 3 (Planned)

- [ ] **Referral Dashboard**: Member stats — "You've referred 12 people! 🎉"  
- [ ] **Leaderboard**: Top advocates of the month  
- [ ] **Referral Rewards**: Badges, early access perks, swag eligibility
- [ ] **Email Templates**: Welcome series, notifications, security alerts

### Phase 4 (Future)

- [ ] **Multi-Level Analytics**: See your full referral tree (Alex → Jordan → Casey)  
- [ ] **Enhanced Admin Panel**: Approve pending members, view referral analytics, export reports
- [ ] **Member Profiles**: Public pages with avatar, bio, join date  
- [ ] **Social Login**: OAuth with Google/Facebook (keeps referral tracking)
- [ ] **Legacy Member Import**: Bulk import of 15k+ existing members with sequential IDs

---

## 📚 Quick Links

- **Constitution**: [https://ip.org.nz/constitution](https://ip.org.nz/constitution)  
- **GitHub Repo**: [Private — reach out for access]  
- **Staging Site**: [https://staging.ipnz.live](https://staging.ipnz.live)  
- **Production**: [https://ipnz.live](https://ipnz.live)

---

## 🙌 Credits

**Built with**:
- PHP 8.2 + Apache 2.4  
- MariaDB 10.4  
- Bootstrap 5 (responsive UI)  
- Ready Player Me (3D avatars)  
- Matomo (privacy-respecting analytics)

**Special Thanks**:
- Internet Party of New Zealand for the mission  
- Open-source community for the tools  
- Every member who shares and grows the movement ❤️

---

_This isn't just a sign-up form — it's a movement amplifier. Every referral link is a conversation starter. Every new member is a voice for a free, fair, and connected society. Let's make it awesome together._