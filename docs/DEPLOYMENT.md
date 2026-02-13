# Deployment Guide — Nginx + DigitalOcean + Ubuntu

> Step-by-step guide to deploy the Lake Sebu NHS Enrollment System to a DigitalOcean droplet running Ubuntu with Nginx.

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Create DigitalOcean Droplet](#2-create-digitalocean-droplet)
3. [Initial Server Setup](#3-initial-server-setup)
4. [Install Required Software](#4-install-required-software)
5. [Install Google Chrome (for PDF Generation)](#5-install-google-chrome-for-pdf-generation)
6. [Configure MySQL Database](#6-configure-mysql-database)
7. [Deploy the Application](#7-deploy-the-application)
8. [Configure Environment](#8-configure-environment)
9. [Configure Nginx](#9-configure-nginx)
10. [SSL Certificate (Let's Encrypt)](#10-ssl-certificate-lets-encrypt)
11. [Configure Queue Worker (Optional)](#11-configure-queue-worker-optional)
12. [Post-Deployment Checklist](#12-post-deployment-checklist)
13. [Maintenance & Updates](#13-maintenance--updates)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Prerequisites

Before deploying, make sure you have:

- [ ] A DigitalOcean account
- [ ] A domain name pointing to DigitalOcean (optional but recommended)
- [ ] SSH key pair generated on your local machine
- [ ] The project code pushed to a Git repository (GitHub/GitLab)
- [ ] Local project tested and working

**Tech Stack being deployed:**

| Component | Version |
|-----------|---------|
| PHP | 8.2+ |
| MySQL | 8.0 |
| Node.js | 20 LTS |
| Nginx | Latest |
| Composer | Latest |
| Laravel | 12.x |

---

## 2. Create DigitalOcean Droplet

1. Log in to [DigitalOcean](https://cloud.digitalocean.com)
2. Click **Create > Droplets**
3. Choose settings:

| Setting | Recommended Value |
|---------|------------------|
| Region | Singapore (closest to Philippines) |
| Image | **Ubuntu 24.04 LTS** |
| Size | **Basic $12/mo** (2 GB RAM, 1 vCPU, 50 GB SSD) |
| Authentication | SSH Key (add your public key) |
| Hostname | `enrollment` or `lsnhs-enrollment` |

> **Minimum:** 2 GB RAM is recommended. 1 GB will work but may struggle with PDF generation (Chrome uses ~500MB).

4. Click **Create Droplet**
5. Note the **IP address** (e.g., `143.198.xxx.xxx`)

---

## 3. Initial Server Setup

SSH into your droplet:

```bash
ssh root@YOUR_SERVER_IP
```

### Create a deploy user (don't run the app as root)

```bash
# Create user
adduser deploy

# Add to sudo group
usermod -aG sudo deploy

# Copy SSH keys to deploy user
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy

# Switch to deploy user
su - deploy
```

### Configure firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status
```

### Set timezone (Philippines)

```bash
sudo timedatectl set-timezone Asia/Manila
```

---

## 4. Install Required Software

### PHP 8.2 + Extensions

```bash
sudo apt update && sudo apt upgrade -y

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.2 and required extensions
sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-readline \
    php8.2-tokenizer \
    php8.2-fileinfo \
    php8.2-dom

# Verify
php -v
```

### Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

### Node.js 20 LTS + npm

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node -v
npm -v
```

### Nginx

```bash
sudo apt install -y nginx

# Verify
sudo systemctl status nginx
```

### MySQL 8

```bash
sudo apt install -y mysql-server

# Secure the installation
sudo mysql_secure_installation
# Answer: Y, set root password, Y to all remaining prompts
```

### Git

```bash
sudo apt install -y git
```

---

## 5. Install Google Chrome (for PDF Generation)

The project uses `spatie/laravel-pdf` which requires a headless Chrome browser to render PDFs (SF9 Report Cards, SF10 Permanent Records).

```bash
# Add Google Chrome repository
wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo gpg --dearmor -o /usr/share/keyrings/google-chrome.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/google-chrome.gpg] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list

# Install Chrome
sudo apt update
sudo apt install -y google-chrome-stable

# Verify installation
google-chrome-stable --version
# Should output something like: Google Chrome 120.x.x.x

# Verify path
which google-chrome-stable
# Should output: /usr/bin/google-chrome-stable
```

### Install Chrome dependencies (if needed)

```bash
sudo apt install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxkbcommon0 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libpango-1.0-0 \
    libasound2 \
    libxshmfence1
```

> **Why Chrome?** `spatie/laravel-pdf` uses Browsershot which launches a headless Chrome browser to convert HTML into pixel-perfect PDFs. This produces better quality than pure PHP solutions (dompdf), especially for complex DepEd school forms.

---

## 6. Configure MySQL Database

```bash
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE enrollment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create application user (DON'T use root for the app)
CREATE USER 'enrollment_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON enrollment.* TO 'enrollment_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

> Replace `YOUR_STRONG_PASSWORD_HERE` with a strong password. Save it — you'll need it for the `.env` file.

---

## 7. Deploy the Application

### Clone the repository

```bash
cd /var/www
sudo mkdir enrollment
sudo chown deploy:deploy enrollment
git clone YOUR_REPOSITORY_URL enrollment
cd enrollment
```

### Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Install Node dependencies and build assets

```bash
npm install
npm run build
```

### Install Puppeteer (for PDF generation)

```bash
npm install puppeteer
```

### Set permissions

```bash
# Storage and cache must be writable by the web server
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Create required directories
mkdir -p storage/app/temp
mkdir -p storage/fonts
chmod 775 storage/app/temp
chmod 775 storage/fonts
```

---

## 8. Configure Environment

### Create .env file

```bash
cp .env.example .env
nano .env
```

### Update these values in `.env`:

```env
APP_NAME="LSNHS Enrollment"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enrollment
DB_USERNAME=enrollment_user
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=sync
```

> **IMPORTANT:** Set `APP_DEBUG=false` in production. Never expose debug info to users.

### Generate app key

```bash
php artisan key:generate
```

### Run migrations and seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

### Cache configuration (production optimization)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
```

### Create storage symlink

```bash
php artisan storage:link
```

---

## 9. Configure Nginx

### Create Nginx site configuration

```bash
sudo nano /etc/nginx/sites-available/enrollment
```

Paste this configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/enrollment/public;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;

    charset utf-8;

    # Max upload size (for Excel imports)
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

### Enable the site

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/enrollment /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

### Configure PHP-FPM

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Ensure these settings:

```ini
user = deploy
group = www-data
listen.owner = deploy
listen.group = www-data
```

Also increase memory limit and execution time for PDF generation:

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
memory_limit = 256M
max_execution_time = 120
upload_max_filesize = 20M
post_max_size = 25M
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.2-fpm
```

---

## 10. SSL Certificate (Let's Encrypt)

**Free HTTPS** using Certbot:

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get certificate (replace with your domain)
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Follow the prompts:
# - Enter email for renewal notices
# - Agree to terms
# - Choose to redirect HTTP to HTTPS (option 2)

# Verify auto-renewal
sudo certbot renew --dry-run
```

After SSL is set up, update your `.env`:

```env
APP_URL=https://your-domain.com
```

Then rebuild caches:

```bash
php artisan config:cache
```

---

## 11. Configure Queue Worker (Optional)

If using background queues (for email, long exports, etc.):

### Create systemd service

```bash
sudo nano /etc/systemd/system/enrollment-queue.service
```

```ini
[Unit]
Description=Enrollment Queue Worker
After=network.target

[Service]
User=deploy
Group=www-data
Restart=always
RestartSec=3
WorkingDirectory=/var/www/enrollment
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable enrollment-queue
sudo systemctl start enrollment-queue
```

---

## 12. Post-Deployment Checklist

Run through these checks after deployment:

### Functional Checks

| Check | Command / Action | Expected |
|-------|-----------------|----------|
| App loads | Visit `https://your-domain.com` | Welcome page renders |
| Login works | Login with `admin@lsnhs.edu.ph` / `password` | Dashboard loads |
| Dashboard stats | Check dashboard page | Stats cards show data |
| Student CRUD | Create/edit a student | No errors |
| Enrollment wizard | Enroll a test student | 5-step wizard completes |
| Grade entry | Enter test grades | Grades save correctly |
| PDF generation | Generate SF9 report card | PDF downloads |
| Excel export | Export class list | Excel file downloads |
| Excel import | Import test student file | Students created |

### Security Checks

```bash
# Verify debug is OFF
grep "APP_DEBUG" /var/www/enrollment/.env
# Should show: APP_DEBUG=false

# Verify .env is not accessible via browser
curl -I https://your-domain.com/.env
# Should return 403 Forbidden or 404

# Verify storage is not publicly accessible
curl -I https://your-domain.com/storage/logs/laravel.log
# Should return 403 or 404
```

### Performance Checks

```bash
# Verify caches are built
php artisan config:show app.debug
# Should show: false

# Check if route cache exists
ls -la bootstrap/cache/routes-v7.php

# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm
```

### Change Default Password

**IMPORTANT:** Change the admin password immediately after first login.

---

## 13. Maintenance & Updates

### Deploying Updates

When you push new code, run this on the server:

```bash
cd /var/www/enrollment

# Pull latest code
git pull origin main

# Install any new PHP dependencies
composer install --no-dev --optimize-autoloader

# Install any new Node dependencies & rebuild
npm install
npm run build

# Run any new migrations
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart PHP-FPM to pick up changes
sudo systemctl restart php8.2-fpm
```

### Optional: Create a deploy script

Save as `/var/www/enrollment/deploy.sh`:

```bash
#!/bin/bash
set -e

echo "Deploying..."

cd /var/www/enrollment

# Maintenance mode ON
php artisan down

# Pull code
git pull origin main

# Dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Database
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart services
sudo systemctl restart php8.2-fpm

# Maintenance mode OFF
php artisan up

echo "Deployed successfully!"
```

```bash
chmod +x deploy.sh
./deploy.sh
```

### Monitoring Logs

```bash
# Laravel logs
tail -f /var/www/enrollment/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### Database Backup

Set up daily backups:

```bash
# Create backup script
sudo nano /usr/local/bin/backup-enrollment.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/enrollment"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u enrollment_user -pYOUR_PASSWORD enrollment > "$BACKUP_DIR/db_$DATE.sql"

# Keep only last 7 days of backups
find $BACKUP_DIR -name "db_*.sql" -mtime +7 -delete

echo "Backup completed: db_$DATE.sql"
```

```bash
sudo chmod +x /usr/local/bin/backup-enrollment.sh

# Add to cron (daily at 2 AM)
sudo crontab -e
# Add this line:
0 2 * * * /usr/local/bin/backup-enrollment.sh >> /var/log/enrollment-backup.log 2>&1
```

---

## 14. Troubleshooting

### Common Issues

#### 502 Bad Gateway
```bash
# Check PHP-FPM is running
sudo systemctl status php8.2-fpm

# Restart it
sudo systemctl restart php8.2-fpm

# Check Nginx error log
tail -20 /var/log/nginx/error.log
```

#### Permission Denied (storage/logs)
```bash
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### PDF Generation Fails
```bash
# Check Chrome is installed
google-chrome-stable --version

# Check Chrome path
which google-chrome-stable

# Test Chrome headless
google-chrome-stable --headless --disable-gpu --dump-dom https://example.com

# Check Node/Puppeteer
node -e "const p = require('puppeteer'); console.log('OK');"

# Check storage/app/temp is writable
ls -la storage/app/temp/
```

#### Migrations Fail
```bash
# Check database connection
php artisan db:show

# Run with verbose output
php artisan migrate --force -v
```

#### Assets Not Loading (CSS/JS)
```bash
# Rebuild assets
npm run build

# Check if build output exists
ls -la public/build/

# Clear view cache
php artisan view:clear
```

#### Session/CSRF Errors (419)
```bash
# Make sure session table exists (if using database driver)
php artisan session:table
php artisan migrate --force

# Clear config cache and rebuild
php artisan config:clear
php artisan config:cache
```

#### Excel Import Fails (memory)
```bash
# Increase PHP memory limit
sudo nano /etc/php/8.2/fpm/php.ini
# Set: memory_limit = 512M

sudo systemctl restart php8.2-fpm
```

---

## Quick Reference

### Server Access

```bash
ssh deploy@YOUR_SERVER_IP
```

### Service Commands

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart enrollment-queue  # if using queues
```

### Laravel Commands

```bash
cd /var/www/enrollment

php artisan down          # Maintenance mode ON
php artisan up            # Maintenance mode OFF
php artisan migrate       # Run migrations
php artisan config:cache  # Cache config
php artisan route:cache   # Cache routes
php artisan cache:clear   # Clear app cache
php artisan queue:restart # Restart queue workers
```

### Application URLs

| URL | Purpose |
|-----|---------|
| `https://your-domain.com` | Landing page |
| `https://your-domain.com/login` | Login |
| `https://your-domain.com/dashboard` | Dashboard |

### Default Admin Login

| Field | Value |
|-------|-------|
| Email | `admin@lsnhs.edu.ph` |
| Password | `password` |

> **Change this password immediately after first login in production.**

---

## Cost Estimate

| Service | Monthly Cost |
|---------|-------------|
| DigitalOcean Droplet (2GB) | $12 |
| Domain name | ~$1/mo (~$12/year) |
| SSL Certificate | Free (Let's Encrypt) |
| **Total** | **~$13/month** |

> For a school with ~500 students, the $12 droplet is sufficient. If you have 1000+ students or heavy concurrent usage during enrollment week, consider upgrading to the $24 droplet (4GB RAM).
