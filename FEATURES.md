# IPnz.live — The User Journey ✨

> **Last updated:** January 19, 2026  
> **What is this?** A walkthrough of how IPnz.live creates awesome member experiences with privacy-respecting referrals, secure joins, and seamless updates.

---

## 🌟 The Story: From Discovery to Advocacy

### Act 1: Discovery (Someone Shares the Love)

**Sarah** is already a member. She loves the Internet Party movement and wants her friend **Alex** to join. Sarah clicks the **X share icon** on the homepage:

1. **Share Link Generated**  
   - Sarah's referral code `m42` (her member ID) is embedded in the URL: `https://IPnz.live?ref=m42`  
   - Tweet/Post prefills with: _"Join the Internet Party movement! 🚀 #IPnz #InternetParty"_ + the referral link  
   - Opens in a new window (secure, no `opener` access) with fallback from `x.com` → `twitter.com`

2. **Alex Clicks the Link**  
   - Lands on IPnz.live homepage with `?ref=m42` in the URL  
   - `referral.js` captures the `ref` parameter and stores it in `localStorage`:  
     - `ipnz_incoming_ref = "m42"`  
     - `ipnz_incoming_ref_id = "42"` (numeric ID extracted)

3. **Welcome Banner Appears** 🎉  
   - Alex sees a vibrant banner at the top:  
     > _"Hey! Someone thought this was awesome and invited you to join IPnz.live!  
     > Be part of the Internet Party movement. Join us and get your own referral link too."_  
   - Big **"Join Now"** button, plus a dismiss (×) link  
   - Banner only shows if:  
     - `?ref=` was present in URL, AND  
     - Alex hasn't joined yet (no `ipnz_member_id` in localStorage)

---

### Act 2: Joining (Alex Becomes a Member)

**Alex clicks "Join Now"** → lands on `/join`

1. **Form Loads with Hidden Referrer Tracking**  
   - `referral.js` populates the hidden `referrer_id` field with `42` from localStorage  
   - Alex fills in: Name, Email, Phone, Join Type (Early Access / Standard), optional message  
   - Avatar is optional — skipping it uses a friendly default Ready Player Me avatar

2. **Submit → Backend Magic** (`clientregistration.php`)  
   - **Validation**: Email format check (preserves form data on error)  
   - **Referrer Credit**: Checks if `referrer_id=42` exists in `ipnz_members` → if valid, records it in Alex's new row  
   - **Insert**: New member row created:
     ```sql
     INSERT INTO ipnz_members (name, email, phone, join_type, additional_request, 
                                avatar_url, has_custom_avatar, referrer_id, status)
     VALUES ('Alex', 'alex@example.com', '0212345678', 'early_access', '', 
             'https://default-avatar.png', 0, 42, 'pending')
     ```
   - **Success Response**:  
     - Green banner: _"Sign up Success! A verification email has been sent to alex@example.com. Your referral link: https://IPnz.live?ref=m83"_  
     - `localStorage` stores:
       - `ipnz_ref = "m83"` (Alex's new referral code)  
       - `ipnz_member_id = "83"`  
       - `ipnz_member_profile = {name, email, phone, join_type, ...}` (for prefill on return visits)

3. **Sarah Gets Credit** 🎖️  
   - Alex's row now has `referrer_id = 42` pointing to Sarah  
   - Future analytics queries can count how many people Sarah referred  
   - Sarah's impact is tracked without exposing Alex's personal info

---

### Act 3: Returning (Alex Updates Their Profile)

**Alex visits `/join` again** (maybe to update their phone or avatar):

1. **Auto-Prefill Magic**  
   - `join.php` client-side script reads `ipnz_member_profile` from localStorage  
   - All fields populate with Alex's existing data  
   - Fields are **disabled** by default (safe, read-only view)  
   - Submit button reads: **"Update details"**  
   - Hidden `member_id` field set to `83`

2. **"Edit details" Link**  
   - Clicking this re-enables Name, Phone, Message, Avatar, Join Type fields  
   - Email stays disabled (changes require verification for security)  
   - Button changes to: **"Save changes"**

3. **Submit → Update Flow**  
   - `clientregistration.php` sees `member_id=83` in POST data  
   - Runs `UPDATE` query:
     ```sql
     UPDATE ipnz_members 
     SET name=?, phone=?, join_type=?, additional_request=?, avatar_url=?, has_custom_avatar=?
     WHERE id=83 AND email='alex@example.com' AND deleted_at IS NULL
     ```
   - Success: _"Details updated successfully"_  
   - `localStorage` profile refreshed with new values

---

## 🔒 Privacy & Security Foundations

### What We Protect

1. **Shared Email Addresses**  
   - Email is **not unique** in the schema (families, couples can share an address)  
   - Duplicate email signup → soft notification (no personal info leaked):  
     > _"Note: This email address is associated with other accounts."_  
   - Future: send security alert to existing account holders (planned)

2. **Prepared Statements Everywhere**  
   - All DB queries use `mysqli` prepared statements (`bind_param`)  
   - Zero SQL injection risk in `clientregistration.php`, views, etc.

3. **External Links**  
   - All outbound links use `rel="noopener"` (prevents reverse tabnabbing)  
   - Referrer header preserved for analytics while blocking malicious `window.opener` access

4. **Credentials Externalized**  
   - `database.php` loads from `database.config.php` (local dev) or env vars (production)  
   - `.gitignore` keeps secrets out of version control

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
Sarah (Member #42)
  ↓ Shares link with ref=m42
Alex (Visitor)
  ↓ Sees welcome banner
Alex Joins → Member #83
  ↓ Gets ref=m83
Alex Shares with Jordan
  ↓ ...and the cycle continues
```

**Key Metrics Trackable**:
- Referrals by member (via `referrer_id` foreign key)  
- Conversion rate (referred visitors → members)  
- Referral chain depth (future: multi-level tracking)

---

## 🛠️ Technical Architecture

### Database Schema

**Core Tables**:
- `ipnz_members`: Name, email, phone, join_type, avatar_url, **referrer_id**, status, timestamps  
- `ipnz_contacts`: Contact form submissions (separate from members)  
- `ipnz_member_activity`: Login, profile updates, avatar changes

**Views for UI**:
- `view_active_members`: Public member grid (status='active', not deleted)  
- `view_pending_members`: Admin approval queue  
- `view_new_contacts`: Unread contact messages

**Indexes & FK**:
- `idx_referrer_id` + `fk_referrer` foreign key on `ipnz_members.referrer_id`  
- Cascading delete: if referrer is deleted, `referrer_id` → NULL (preserves member record)

### Client-Side JavaScript

**`referral.js`** (auto-loaded on all pages):
- Captures `?ref=` from URL → stores in localStorage  
- Populates hidden `referrer_id` field on join form  
- Shows/hides welcome banner based on member status  
- Dismissal persists via `ipnz_ref_banner_dismissed` flag

**`share.js`** (loaded on pages with share icon):
- Builds X/Twitter intent URL with referral code from localStorage  
- Prefills tweet text, hashtags, via handle  
- Opens in new window (secure)

### Backend PHP

**`clientregistration.php`**:
- Validates inputs (email format, referrer existence)  
- Maps radio `join-type` (0/1) → ENUM (`early_access`/`standard`)  
- Default avatar if none provided  
- INSERT with `referrer_id` for new members  
- UPDATE with `member_id` match for returning members  
- Stores profile in `localStorage` via inline `<script>` on success

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
- **`.htaccess` Variants**: Dev (no HTTPS), Local (trusted cert), Production (force HTTPS + security headers)  
- **Environment Configs**: Separate DB credentials for staging/live (env vars in cPanel)

---

## 🧪 Testing Strategy

### Automated (Playwright)

- **`tests/e2e/home.spec.ts`**: Share icon builds correct intent URL  
- **`tests/e2e/join.spec.ts`**: Validation errors, form persistence, referral localStorage  
- **CI Workflow** (`.github/workflows/e2e.yml`): Runs on every push, uses Docker services

### Manual Test Plan (`TESTS.md`)

**Critical Paths**:
1. Referral capture: Visit `/?ref=m1` → banner appears → localStorage set  
2. Join with referral: Fill form → submit → `referrer_id` recorded in DB  
3. Return visit: Prefilled form → edit → update → DB reflects changes  
4. Share link: Click X icon → tweet prefills with `?ref=mYOUR_ID`

**Edge Cases**:
- Invalid referrer ID → ignored, join still succeeds  
- Shared email → soft notification, no PII leak  
- Dismiss banner → doesn't reappear (localStorage flag)

---

## 🎯 What Makes This Awesome

### For Members Like Sarah

- **Effortless Sharing**: One-click X share with auto-generated referral link  
- **Credit Where Due**: See who you've brought in (future: referral dashboard)  
- **No Spam**: Privacy-first — we never expose referrer info to referred users

### For New Visitors Like Alex

- **Warm Welcome**: Friendly banner acknowledging they were invited  
- **Quick Join**: Simple form, optional avatar, clear CTAs  
- **Instant Gratification**: Get your own referral link immediately after joining

### For the Movement

- **Viral Growth**: Every member becomes an advocate with their unique link  
- **Data Insights**: Track referral chains, identify top advocates  
- **Privacy Compliance**: GDPR-ready consent tracking, soft deletes, audit trail

---

## 🗺️ Roadmap: Next Level Features

### Phase 2 (Planned)

- [ ] **Email Verification**: Tokenized links to confirm email addresses  
- [ ] **Referral Dashboard**: Member stats — "You've referred 12 people! 🎉"  
- [ ] **Leaderboard**: Top advocates of the month  
- [ ] **Referral Rewards**: Badges, early access perks, swag eligibility

### Phase 3 (Future)

- [ ] **Multi-Level Tracking**: See your full referral tree (Alex → Jordan → Casey)  
- [ ] **Admin Panel**: Approve pending members, view referral analytics  
- [ ] **Member Profiles**: Public pages with avatar, bio, join date  
- [ ] **Social Login**: OAuth with Google/Facebook (keeps referral tracking)

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