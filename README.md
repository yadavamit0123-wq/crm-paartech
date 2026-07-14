# SaaS CRM Platform

Multi-Tenant SaaS CRM built for **cPanel + AlmaLinux + MySQL** deployment.

## Phase 5 Features (Complete)

### Marketing Dashboard
- Overview: scheduled posts, ad spend, leads, latest SEO score
- Quick links to Social, SEO, Ads, Integrations

### Social Media Scheduler
- Schedule posts for Facebook, Instagram, LinkedIn, X, GMB, YouTube
- Draft / Scheduled / Published / Failed status
- Manual publish mode (mark as published after posting on platform)
- Cron: `crm:publish-scheduled-posts` auto-marks due posts as published

### SEO Audit
- Enter any URL → instant on-page SEO analysis
- Checks: title, meta description, H1, viewport, canonical, alt tags, OG tags, schema, word count, load time
- Score 0–100 with actionable recommendations
- Audit history saved per tenant

### Ad Campaign Tracker
- Track Google, Meta, WhatsApp, LinkedIn campaigns
- Budget, spend, impressions, clicks, leads, CPL
- **Sync Leads** — counts CRM leads by source/campaign UTM
- External campaign ID field for future API sync

## Phase 4 Features (Complete)

### Website Lead Capture
- REST API: `POST /api/leads/capture`
- Embeddable HTML form (copy from Integrations page)
- Duplicate detection by phone/email
- UTM campaign tracking

### WhatsApp Integration
- Webhook: `/api/webhooks/whatsapp/{tenant}`
- Incoming messages auto-create leads
- Outbound review request messages
- Verify token support

### Meta (Facebook/Instagram) Lead Ads
- Webhook: `/api/webhooks/meta/{tenant}`
- Lead form auto-sync to CRM
- Graph API fetch for lead field data
- Signature verification

### Google Ads Lead Forms
- Webhook: `POST /api/webhooks/google/{tenant}`
- Lead form extension data mapping
- Webhook secret authentication

### Google Reviews
- Send review request from lead page (WhatsApp)
- Auto-reply generator (rule-based + OpenAI optional)
- Manual review entry for testing
- Edit/regenerate replies
- Sentiment detection (positive/neutral/negative)

### Integrations Dashboard
- All webhook URLs with copy button
- Per-tenant API credentials in settings
- Webhook activity logs
- Website embed code generator

## Phase 3 Features (Complete)

### Accounting Dashboard
- Monthly sales, purchases, GST summary
- Output GST vs Input Tax Credit (ITC)
- Net GST liability calculation
- Payments received tracker

### Expense Entry (Purchase Register)
- Vendor details with GSTIN
- GST breakup: CGST + SGST (same state) or IGST (inter-state)
- Categories: office, rent, travel, software, etc.
- Bill/receipt upload
- GST ON/OFF toggle for non-GST purchases

### Payroll (Basic)
- Employee salary profiles (Basic, HRA, Allowances)
- Deductions: PF, ESI, TDS
- Monthly payroll processing
- Payslip batch PDF download
- Mark payroll as paid

### GST Reports Export
- **GSTR-1** — outward supplies (CSV + JSON)
- **GSTR-3B** — tax liability with ITC (CSV + JSON)
- **Sales Register** — all invoices CSV
- **Purchase Register** — all expenses CSV
- Export history log
- B2B vs B2C invoice split in GSTR-1

## Phase 2 Features (Complete)

### Documents (Quotation, Proforma, Invoice)
- Create Quotation, Proforma Invoice, Tax Invoice
- **GST + Non-GST toggle** on every document
- Auto GST calculation (CGST+SGST intra-state, IGST inter-state)
- Line items with HSN/SAC, discount, quantity
- Professional PDF download (DomPDF)
- Document chain: Quotation → Proforma → Invoice
- Auto document numbering (QUO-2026-001, PRO-2026-001, INV-2026-001)

### Lead → Customer Conversion
- One-click convert lead to customer
- Auto move lead to "Won" stage
- Link customer back to original lead

### Milestone Payments
- Create payment plan with custom milestones (e.g. 60%-30%-10%)
- Percentages must total 100%
- **Approve milestone first**, then generate payment link + QR
- Payment methods: Online (Razorpay), Cash, Bank, UPI
- Public payment page with QR code
- WhatsApp payment link sharing
- Payment tracking per customer

## Phase 1 Features (Complete)

### Multi-Tenant Architecture
- Tenant isolation via `tenant_id`
- Subdomain support (`demo.yourdomain.com`)
- Super Admin + Tenant Admin panels
- Subscription plans (Monthly/Yearly)

### Auth + RBAC
- Roles: Super Admin, Admin, Manager, Senior, Junior, Accountant, HR, Marketing
- Granular permission matrix
- Employee management

### Lead CRM
- Create lead manually
- Bulk CSV upload
- Custom lead stages (create, edit, reorder, delete)
- Kanban + List views
- Stage update with activity log
- Forward lead to senior/junior with note

### Lead Actions (per lead)
- Call, WhatsApp, Email, SMS
- Reminder, Follow-up, Schedule Meeting, Demo
- Company Profile, Sticky Notes, Internal Chat
- Audio Call Recording upload
- Google Review request (logged)
- Edit, Delete

### Notifications
- Browser push notifications
- Sound alert on due reminders
- Notification bell with polling (60s)
- Cron-based reminder processing

### Dashboard
- Total/New/Won leads stats
- Follow-ups today, overdue count
- Pipeline stage breakdown
- Recent leads list

### API
- Website lead capture endpoint (`POST /api/leads/capture`)

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | Laravel 11 (PHP 8.2+) |
| Frontend | Livewire 3 + Tailwind CSS (CDN) |
| Database | MySQL 8 / MariaDB |
| Queue | Database queue (no Redis) |
| Cache | Database cache |
| Scheduler | cPanel cron |

## Quick Start (Local/Server)

```bash
composer install
cp .env.example .env
# Configure DB in .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Visit: `http://localhost:8000`

## Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@platform.com | Admin@123 |
| Tenant Admin | admin@demo.com | Demo@123 |

## Deployment

See **[DEPLOYMENT.md](DEPLOYMENT.md)** for complete cPanel setup guide (Hindi + English).

## Project Structure

```
app/
├── Livewire/          # UI components
├── Models/            # Eloquent models
├── Services/          # Tenant, Permission services
├── Http/Middleware/   # Tenant resolver, RBAC
database/
├── migrations/        # All table schemas
├── seeders/           # Demo data
├── crm_database.sql   # phpMyAdmin import file
resources/views/       # Blade templates
routes/
├── web.php            # Web routes
├── api.php            # API routes
```

## Roadmap

- [x] **Phase 1:** CRM Core, RBAC, Leads, Reminders
- [x] **Phase 2:** Quotation, Proforma, Invoice, Customer Conversion, Milestone Payments
- [x] **Phase 3:** Accounting, GST Reports, Payroll, Expenses
- [x] **Phase 4:** Meta/Google/WhatsApp Integrations, Google Reviews
- [x] **Phase 5:** Social Media Scheduler, SEO Audit, Ad Campaign Tracker

## Bulk Upload CSV Format

```csv
name,email,phone,company,source,city,state,priority
Rajesh Kumar,rajesh@example.com,9876543210,Tech Solutions,website,Bangalore,Karnataka,medium
```

## License

MIT
