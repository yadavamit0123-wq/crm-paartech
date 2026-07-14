# cPanel Deployment Guide / cPanel पर Deploy करें

## Requirements / जरूरी चीजें

- **cPanel** on AlmaLinux
- **PHP 8.2+** (enable extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)
- **MySQL 8** or MariaDB 10.6+
- **Composer 2.x** (via SSH or cPanel Terminal)
- **Document Root** must point to `/public` folder

---

## Step 1: Upload Project / Project Upload करें

1. Local machine par project folder ko **ZIP** karein
2. cPanel → **File Manager** → `public_html` (ya subdomain folder)
3. ZIP upload karein aur **Extract** karein
4. Folder structure aisa hona chahiye:
   ```
   public_html/crm/
   ├── app/
   ├── public/        ← Document root yahan point karein
   ├── database/
   ├── ...
   ```

> **Important:** Document root **`crm/public`** par set karein, root folder par nahi.

---

## Step 2: Create MySQL Database / Database बनाएं

1. cPanel → **MySQL Databases**
2. New Database: `username_crm`
3. New User: `username_crmuser` with strong password
4. Add user to database → **ALL PRIVILEGES**
5. Note karein: DB name, username, password

---

## Step 3: Configure Environment / .env Setup

1. File Manager me `.env.example` ko copy karke `.env` banayein
2. Edit `.env`:

```env
APP_NAME="SaaS CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_PLATFORM_DOMAIN=yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_crm
DB_USERNAME=username_crmuser
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Step 4: Install Dependencies (SSH/Terminal)

cPanel → **Terminal** ya SSH se project folder me jayein:

```bash
cd ~/public_html/crm

# Composer install
composer install --optimize-autoloader --no-dev

# Agar "security advisories" error aaye (Composer 2.9+):
# php composer.phar config audit.block-insecure false
# php composer.phar install --optimize-autoloader --no-dev --no-interaction
# Ya: COMPOSER_NO_BLOCKING=1 php composer.phar install --optimize-autoloader --no-dev

# App key generate
php artisan key:generate

# Database tables + demo data
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

# Cache optimize (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Alternative: phpMyAdmin SQL Import

Agar SSH na ho:
1. phpMyAdmin → apna database select karein
2. **Import** → `database/crm_database.sql` upload karein
3. Phir bhi SSH se ye commands zaroor chalayein:
   ```bash
   php artisan key:generate
   php artisan db:seed --force
   php artisan storage:link
   ```

---

## Step 5: File Permissions / Permissions Set करें

```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

cPanel File Manager se:
- `storage/` → Permissions → **775** (recursive)
- `bootstrap/cache/` → Permissions → **775** (recursive)

---

## Step 6: Cron Job Setup (Reminders + Queue)

cPanel → **Cron Jobs** → Add:

```bash
* * * * * cd /home/USERNAME/public_html/crm && php artisan schedule:run >> /dev/null 2>&1
```

> Replace `USERNAME` aur path apne account ke hisaab se.

Yeh cron har minute:
- Follow-up reminders process karega
- Background queue jobs chalayega (emails, bulk upload)

---

## Step 7: Document Root Setup

### Main Domain
cPanel → **Domains** → Document Root → `/public_html/crm/public`

### Subdomain (Multi-tenant: demo.yourdomain.com)
1. Subdomain banayein: `demo.yourdomain.com`
2. Document root: same `/public_html/crm/public`
3. `.env` me: `APP_PLATFORM_DOMAIN=yourdomain.com`

---

## Step 8: SSL Certificate

cPanel → **SSL/TLS** → **AutoSSL** enable karein (Let's Encrypt free)

---

## Demo Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@platform.com | Admin@123 |
| Tenant Admin | admin@demo.com | Demo@123 |
| Senior | senior@demo.com | Demo@123 |
| Junior | junior@demo.com | Demo@123 |

> **Production me login ke baad passwords change karein!**

---

## Website Lead Capture API (Phase 1 Ready)

POST request to: `https://yourdomain.com/api/leads/capture`

```json
{
  "tenant_key": "demo",
  "name": "Rajesh Kumar",
  "email": "rajesh@example.com",
  "phone": "9876543210",
  "source": "website",
  "message": "Interested in your services"
}
```

---

## Troubleshooting / समस्या हल

| Problem | Solution |
|---------|----------|
| 500 Error | Check `storage/logs/laravel.log`, permissions 775 |
| CSS/JS not loading | Document root `/public` par hai confirm karein |
| Login not working | `php artisan db:seed --force` run karein |
| Upload failed | `php artisan storage:link` + storage permissions |
| Reminders not working | Cron job check karein |
| White page | `.env` me `APP_DEBUG=true` temporarily, error dekhein |

---

## Phase 4: Integrations Setup

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Webhook URLs (tenant = demo)
| Platform | URL |
|----------|-----|
| Website | `POST /api/leads/capture` |
| WhatsApp | `/api/webhooks/whatsapp/demo` |
| Meta | `/api/webhooks/meta/demo` |
| Google Ads | `POST /api/webhooks/google/demo` |

### Setup Steps
1. **Integrations** page → copy webhook URLs
2. Meta Developer Console → Webhooks → paste Meta URL + verify token
3. WhatsApp Cloud API → Configuration → paste WhatsApp URL
4. Google Ads → Lead Form → Webhook integration
5. Website → paste embed form code
6. Google Review link set karein → Reviews auto-reply enable karein

### OpenAI (optional)
`.env` me `OPENAI_API_KEY=sk-...` for smarter review replies

---

## Phase 3: Accounting & GST

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Modules
| Module | Route | Permission |
|--------|-------|------------|
| Accounting Dashboard | `/accounting` | accounting.view |
| Expenses | `/expenses` | expenses.manage |
| Payroll | `/payroll` | payroll.manage |
| GST Reports | `/gst-reports` | gst.export |

### GST Export
1. **GST Reports** page par jayein
2. Month select karein
3. GSTR-1 / GSTR-3B CSV ya JSON download karein
4. GST Portal par manually upload karein

Accountant login: `admin@demo.com` (full accounting access)

---

## Phase 2: Documents & Payments

After Phase 1 deploy, run migration for new tables:

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Razorpay Setup (Optional — for online payments)

`.env` me add karein:
```env
RAZORPAY_KEY=rzp_live_xxxxxxxx
RAZORPAY_SECRET=your_secret_key
```

Bina Razorpay ke bhi payment link + QR kaam karega (manual payment page).

### Payment Flow
1. Lead → **Convert to Customer**
2. Customer page → **Payment Plan** create karein (60-30-10%)
3. Har milestone → **Approve** karein
4. Phir **Generate Link + QR** — tabhi link jayega
5. Cash/Bank/UPI se manual mark bhi kar sakte hain

### Document Flow
1. **Documents** → Create Quotation (GST on/off)
2. Mark Sent → Mark Accepted → Convert to Proforma → Convert to Invoice
3. **PDF Download** button se professional invoice download karein

---

## Phase 5: Marketing Setup

### Routes
| Page | URL | Permission |
|------|-----|------------|
| Marketing Dashboard | `/marketing` | `marketing.view` |
| Social Posts | `/social-posts` | `social.manage` |
| SEO Audit | `/seo-audit` | `seo.audit` |
| Ad Campaigns | `/ad-campaigns` | `ads.manage` |

### Social Media Scheduler
1. **Marketing** → **Social Posts** → **+ New Post**
2. Platform select karein, content likhein, schedule time set karein
3. Cron har minute `crm:publish-scheduled-posts` chalata hai — due posts auto "published" mark hote hain
4. Actual platform par post manually karein, phir **Publish Now** dabayein

### SEO Audit
1. **SEO Audit** page par apni website URL daalein (e.g. `https://yourdomain.com`)
2. **Run Audit** — score + checks + recommendations milenge
3. History mein purane audits dekh sakte hain

### Ad Campaign Tracker
1. **Ad Campaigns** → **+ New Campaign**
2. Platform, budget, spend, clicks, leads manually enter karein
3. **Sync Leads** — CRM se matching source (google/meta/whatsapp) ke leads count karta hai
4. CPL (Cost Per Lead) auto calculate hota hai

### Demo Marketing User
- Email: `marketing@demo.com` / Password: `Demo@123`

### Cron (required for scheduled posts)
```
* * * * * cd /path/to/crm && php artisan schedule:run
```

---

## Support

Project path: `/crm`  
Stack: Laravel 11 + Livewire 3 + MySQL + Tailwind CSS
