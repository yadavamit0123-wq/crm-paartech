# cPanel Simple Install (Hindi) — crm.paartech.in

Sirf **5 steps**. SQL import ki zaroorat nahi — `install.php` sab tables bana dega.

---

## Step 1: Purana folder delete karo (fresh start)

cPanel File Manager → `crm.paartech.in` folder ke andar **saari files delete** karo (ya poora folder delete karke naya banao).

---

## Step 2: Naya ZIP upload + extract

1. Apne computer se **updated CRM zip** upload karo
2. Extract karo — structure aisa ho:

```
crm.paartech.in/
├── app/
├── public/
├── vendor/          ← agar zip mein hai to theek, warna Step 3 dekho
├── .htaccess        ← NEW — public folder auto redirect
├── .env.example
└── ...
```

---

## Step 3: `.env` file banao

1. File Manager → **Show Hidden Files** ON karo
2. `.env.example` → Copy → naam rakho **`.env`**
3. `.env` edit karo — sirf password change karo:

```env
APP_URL=https://crm.paartech.in
APP_PLATFORM_DOMAIN=crm.paartech.in
DB_DATABASE=paartech_crm
DB_USERNAME=paartech_crm
DB_PASSWORD=Amit@123456
APP_KEY=
```

> **APP_KEY khali chhodo** — install.php automatically generate karega.  
> Galat key (jaise `dfghjkl...`) mat daalo!

---

## Step 4: vendor folder (sirf ek baar)

Agar zip mein **`vendor/` folder nahi** hai, cPanel Terminal mein **yeh commands ek-ek karke chalao**.

> ⚠️ **Common error:** `Could not open input file: composer.phar`  
> Matlab aapne **pehle composer download nahi kiya**. Neeche **Step A** pehle chalao, phir Step B.

### Pehle check karo — php kaam kar raha hai?

```bash
cd ~/crm.paartech.in
php -v
```

Agar `php: command not found` aaye to try karo:

```bash
/usr/local/bin/php -v
# ya
php82 -v
```

PHP **8.2+** chahiye (Laravel 11).

### Option 1 — Manual (recommended)

**Step A — Composer download karo (PEHLE YEH!):**

```bash
cd ~/crm.paartech.in
curl -sS https://getcomposer.org/installer | php
```

Agar `curl` fail ho to:

```bash
wget -qO- https://getcomposer.org/installer | php
```

Check: `ls composer.phar` — file dikhni chahiye.

**Step B — Vendor install:**

```bash
php composer.phar config audit.block-insecure false
php composer.phar install --optimize-autoloader --no-dev --no-scripts
```

**Step C — Verify:**

```bash
ls vendor/autoload.php
```

`vendor/autoload.php` dikhe = success ✅

### Option 2 — Ek script se (deploy-vendor.sh)

Zip mein `deploy-vendor.sh` hai. Terminal mein:

```bash
cd ~/crm.paartech.in
bash deploy-vendor.sh
```

### Option 3 — Global composer (agar installed ho)

```bash
which composer
composer --version
cd ~/crm.paartech.in
composer install --optimize-autoloader --no-dev --no-scripts
```

> `--no-scripts` zaroori hai taaki install complete ho.

---

## Step 5: Web Installer chalao (SQL ki jagah)

Browser mein kholo:

```
https://crm.paartech.in/install.php
```

Yeh automatically karega:
- APP_KEY generate
- Database tables create (migrate)
- Demo users + data (seed)
- Cache setup

Success ke baad **install.php delete** kar do (File Manager se).

---

## Code Update (zip upload) — purani site pe naya code

Pehle se chal rahi site pe **sirf code update** karte waqt yeh rules follow karo:

### ✅ Zaroor rakho (overwrite mat karo)

| Item | Kyun |
|------|------|
| **`.env`** | APP_KEY + DB password — overwrite = 500 error |
| **`vendor/`** | Zip mein usually nahi hota — delete = har page 500 |

### Update steps

1. **Pehle backup** — `.env` download kar lo (File Manager se)
2. Naya zip upload karo — **sirf** `app/`, `public/`, `database/`, `resources/`, `routes/`, etc. overwrite karo
3. **`.env` touch mat karo** (aapne sahi kiya agar same rakha)
4. File Manager mein check karo: **`vendor/` folder hai ya nahi?**
   - **Nahi hai** → Terminal mein composer install (Step 4 wale commands)
   - **Hai** → composer skip, seedha step 5
5. Browser mein kholo:

```
https://crm.paartech.in/repair.php
```

6. **"Auto-Fix Chalao"** button dabao — yeh karega:
   - Purani bootstrap cache delete
   - Naye migrations run
   - Config/route cache rebuild
   - Storage permissions fix

7. Login try karo → success ke baad **`repair.php` delete** kar do

### Zip upload ke baad 500 — sabse common reasons

| # | Reason | Fix |
|---|--------|-----|
| 1 | **`vendor/` delete** ho gaya | `composer install` (Step 4) |
| 2 | **Stale cache** (`bootstrap/cache/config.php`) | `repair.php` Auto-Fix |
| 3 | **Storage permissions** reset | `repair.php` ya File Manager se 775 |
| 4 | Naye DB columns missing | `repair.php` migrate chalata hai |

> **Note:** `/login` bhi 500 de raha hai = bootstrap issue (vendor/cache). Sirf `/leads` 500 = migration issue.

### Terminal fallback (repair.php ke bina)

```bash
cd ~/crm.paartech.in
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/packages.php bootstrap/cache/services.php
php composer.phar install --optimize-autoloader --no-dev --no-scripts   # sirf agar vendor missing
php artisan migrate --force
php artisan config:clear
php artisan config:cache
php artisan route:cache
```

---

## Login

```
https://crm.paartech.in/login
```

**Leads CRM Hub (sab modules yahan):**
```
https://crm.paartech.in/leads/dashboard
```

| Email | Password |
|-------|----------|
| admin@demo.com | Demo@123 |
| admin@platform.com | Admin@123 |

---

## Permissions (File Manager se)

In folders par **775** set karo (recursive):
- `storage/`
- `bootstrap/cache/`

---

## Cron Job (optional — reminders ke liye)

cPanel → Cron Jobs:
```
* * * * * cd /home/paartech/crm.paartech.in && php artisan schedule:run
```

---

## Common Problems

| Problem | Fix |
|---------|-----|
| Folder list dikhe | Root mein `.htaccess` file check karo |
| 404 on /login | `.htaccess` missing — naya zip upload karo |
| 500 after zip upload | `vendor/` check karo, phir `repair.php` chalao |
| 500 fresh install | APP_KEY khali rakho, `install.php` chalao |
| DB error | `.env` mein DB name/user/password sahi karo |
| vendor missing | Step 4 ke 3 commands chalao |

---

## SQL file?

`database/crm_database.sql` ab **optional** hai.  
**Recommended:** `install.php` use karo — yeh migrations se sahi tables banata hai.

Agar phpMyAdmin se purana SQL import kiya hai, koi problem nahi — `install.php` missing tables add kar dega.
