# Laravel Project Deployment Guide

## Prerequisites
- Server with PHP 8.1+ (check: `php -v`)
- Composer installed (check: `composer --version`)
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- SSH access to server
- FTP/SFTP client or SCP access

---

## Step 1: Prepare Your Local Project

### 1.1 Update `.env` for Production
Create a production `.env` file (don't upload your local `.env`):

```env
APP_NAME="Tally Management"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=http://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 1.2 Generate Application Key
```bash
php artisan key:generate
```

### 1.3 Optimize for Production
```bash
# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 2: Upload Files to Server

### Option A: Using FTP/SFTP Client (FileZilla, WinSCP, etc.)
1. Connect to your server via FTP/SFTP
2. Upload all files EXCEPT:
   - `.env` (create new one on server)
   - `node_modules/` (if exists)
   - `.git/` (optional)
   - `storage/logs/*` (keep folder, delete log files)
   - `storage/framework/cache/*` (keep folder, delete cache files)
   - `storage/framework/sessions/*` (keep folder, delete session files)
   - `storage/framework/views/*` (keep folder, delete view cache)

### Option B: Using Git (Recommended)
```bash
# On your local machine
git init
git add .
git commit -m "Initial commit"
git remote add origin your-git-repository-url
git push -u origin main

# On server
cd /var/www/html/your-project
git clone your-git-repository-url .
```

### Option C: Using SCP (Linux/Mac)
```bash
scp -r /path/to/tally user@your-server-ip:/var/www/html/
```

---

## Step 3: Server Setup

### 3.1 Set Correct Permissions
```bash
# Navigate to project directory
cd /var/www/html/tally/tally

# Set ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Set special permissions for storage and bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 3.2 Create `.env` File on Server
```bash
cd /var/www/html/tally/tally
cp .env.example .env
nano .env  # or use your preferred editor
```

Update the `.env` file with your production settings (database, app URL, etc.)

### 3.3 Generate Application Key on Server
```bash
php artisan key:generate
```

---

## Step 4: Install Dependencies

### 4.1 Install Composer Dependencies
```bash
cd /var/www/html/tally/tally
composer install --optimize-autoloader --no-dev
```

### 4.2 Install NPM Dependencies (if using frontend assets)
```bash
npm install
npm run build
```

---

## Step 5: Database Setup

### 5.1 Create Database
```sql
CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_database_user'@'localhost' IDENTIFIED BY 'your_database_password';
GRANT ALL PRIVILEGES ON your_database_name.* TO 'your_database_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5.2 Run Migrations
```bash
php artisan migrate --force
```

### 5.3 Seed Database (if needed)
```bash
php artisan db:seed --force
```

---

## Step 6: Configure Web Server

### Option A: Apache Configuration

Create `/etc/apache2/sites-available/tally.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/tally/tally/public

    <Directory /var/www/html/tally/tally/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/tally_error.log
    CustomLog ${APACHE_LOG_DIR}/tally_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2enmod rewrite
sudo a2ensite tally.conf
sudo systemctl restart apache2
```

### Option B: Nginx Configuration

Create `/etc/nginx/sites-available/tally`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/tally/tally/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/tally /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Step 7: Final Optimizations

```bash
cd /var/www/html/tally/tally

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Step 8: SSL Certificate (Optional but Recommended)

### Using Let's Encrypt (Free SSL):
```bash
sudo apt-get install certbot python3-certbot-apache  # For Apache
# OR
sudo apt-get install certbot python3-certbot-nginx   # For Nginx

sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
# OR
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## Step 9: Verify Deployment

1. Visit `http://yourdomain.com` in your browser
2. Check if the application loads correctly
3. Test login functionality
4. Verify database connections
5. Check file uploads (if applicable)

---

## Troubleshooting

### Permission Issues
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 500 Error
- Check `.env` file exists and is configured correctly
- Check `APP_DEBUG=false` in production
- Check web server error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- Verify `storage` and `bootstrap/cache` permissions

### Database Connection Error
- Verify database credentials in `.env`
- Check if database exists
- Verify database user has proper permissions
- Check if MySQL service is running: `sudo systemctl status mysql`

### Route Not Found (404)
- Run: `php artisan route:cache`
- Check web server configuration
- Verify `.htaccess` file exists in `public` folder (Apache)

---

## Quick Checklist

- [ ] Files uploaded to server
- [ ] `.env` file created and configured
- [ ] Application key generated
- [ ] Composer dependencies installed
- [ ] Database created and configured
- [ ] Migrations run successfully
- [ ] Storage and cache folders have correct permissions
- [ ] Web server configured correctly
- [ ] Application accessible via browser
- [ ] SSL certificate installed (optional)

---

## Additional Notes

- Always backup your database before running migrations
- Keep `APP_DEBUG=false` in production
- Regularly update dependencies: `composer update`
- Set up automated backups for database
- Monitor server logs regularly
- Consider using a process manager like Supervisor for queues (if using queues)
