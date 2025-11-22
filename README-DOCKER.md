# Docker Setup Guide - Certificate Generator

Complete Docker setup for Laravel Certificate Generator with SQLite (default) and PostgreSQL (optional).

---

## 📋 Prerequisites

-   Docker Engine 20.10+
-   Docker Compose 2.0+
-   Git

---

## 🚀 Quick Start

### Development Environment (with SQLite)

```bash
# 1. Clone the repository
git clone <repository-url>
cd certify

# 2. Copy environment file
cp .env.example .env

# 3. Generate application key
docker compose run --rm php-fpm php artisan key:generate

# 4. Start containers
docker compose up -d

# 5. Run migrations and seed database
docker compose exec php-fpm php artisan migrate --seed

# 6. Create storage link
docker compose exec php-fpm php artisan storage:link

# Access the application at: http://localhost:8000
```

### Production Environment

```bash
# 1. Set up environment
cp .env.example .env
nano .env  # Configure production settings

# 2. Build and start containers
docker compose -f compose.prod.yaml up -d --build

# 3. Run initial setup (first time only)
docker compose -f compose.prod.yaml exec php-fpm php artisan key:generate
docker compose -f compose.prod.yaml exec php-fpm php artisan migrate --seed
docker compose -f compose.prod.yaml exec php-fpm php artisan storage:link
```

---

## 🗄️ Database Configuration

### Option 1: SQLite (Default)

SQLite is configured by default and requires no additional setup.

**.env configuration:**

```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/database/database.sqlite
```

**Advantages:**

-   ✅ No external database server needed
-   ✅ Simple setup and deployment
-   ✅ Perfect for development and small deployments
-   ✅ Zero configuration required

### Option 2: PostgreSQL (Optional)

To use PostgreSQL instead of SQLite:

**1. Uncomment PostgreSQL service in docker-compose.yml:**

```yaml
postgres:
    image: postgres:16-alpine
    container_name: certify-postgres-dev
    restart: unless-stopped
    ports:
        - "${POSTGRES_PORT:-5432}:5432"
    environment:
        - POSTGRES_DB=${DB_DATABASE:-certify}
        - POSTGRES_USER=${DB_USERNAME:-certify}
        - POSTGRES_PASSWORD=${DB_PASSWORD:-secret}
    volumes:
        - postgres-data-dev:/var/lib/postgresql/data
    networks:
        - certify-dev
    healthcheck:
        test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME:-certify}"]
        interval: 10s
        timeout: 5s
        retries: 5
```

**2. Update .env file:**

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=certify
DB_USERNAME=certify
DB_PASSWORD=secret
POSTGRES_PORT=5432
```

**3. Update php-fpm depends_on (if using PostgreSQL):**

```yaml
php-fpm:
    depends_on:
        postgres:
            condition: service_healthy
```

**4. Restart containers:**

```bash
docker compose down
docker compose up -d
docker compose exec php-fpm php artisan migrate --seed
```

---

## 🐳 Docker Services

### Development Stack

| Service      | Container Name       | Port | Purpose               |
| ------------ | -------------------- | ---- | --------------------- |
| **web**      | certify-web-dev      | 8000 | Nginx web server      |
| **php-fpm**  | certify-php-fpm-dev  | 9000 | PHP-FPM with Laravel  |
| **redis**    | certify-redis-dev    | 6379 | Cache & queue driver  |
| **node**     | certify-node-dev     | 5173 | Vite dev server       |
| **postgres** | certify-postgres-dev | 5432 | PostgreSQL (optional) |

### Production Stack

| Service          | Container Name            | Port | Purpose                 |
| ---------------- | ------------------------- | ---- | ----------------------- |
| **web**          | certify-web-prod          | 80   | Nginx (optimized)       |
| **php-fpm**      | certify-php-fpm-prod      | 9000 | PHP-FPM (production)    |
| **redis**        | certify-redis-prod        | 6379 | Cache & queues          |
| **postgres**     | certify-postgres-prod     | 5432 | PostgreSQL (optional)   |
| **queue-worker** | certify-queue-worker-prod | -    | Queue worker (optional) |

---

## 📝 Common Commands

### Container Management

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# Restart services
docker compose restart

# View logs
docker compose logs -f

# View specific service logs
docker compose logs -f php-fpm

# Rebuild containers
docker compose up -d --build
```

### Laravel Artisan Commands

```bash
# Run migrations
docker compose exec php-fpm php artisan migrate

# Seed database
docker compose exec php-fpm php artisan db:seed

# Clear cache
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan route:clear
docker compose exec php-fpm php artisan view:clear

# Generate application key
docker compose exec php-fpm php artisan key:generate

# Create storage link
docker compose exec php-fpm php artisan storage:link

# Run queue worker (development)
docker compose exec php-fpm php artisan queue:work

# Create new user
docker compose exec php-fpm php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'role' => 1]);
```

### Composer & NPM

```bash
# Install PHP dependencies
docker compose exec php-fpm composer install

# Update dependencies
docker compose exec php-fpm composer update

# Install Node dependencies
docker compose exec node npm install

# Build assets
docker compose exec node npm run build
```

### Database Management

```bash
# SQLite: Access database file
docker compose exec php-fpm sqlite3 /var/www/database/database.sqlite

# PostgreSQL: Access database
docker compose exec postgres psql -U certify -d certify

# Import SQL dump (PostgreSQL)
docker compose exec -T postgres psql -U certify -d certify < backup.sql

# Export database (PostgreSQL)
docker compose exec postgres pg_dump -U certify certify > backup.sql

# Reset database
docker compose exec php-fpm php artisan migrate:fresh --seed
```

---

## 🔧 Environment Variables

### Application Settings

```env
APP_NAME="certify"
APP_ENV=local              # local | production
APP_DEBUG=true             # false in production
APP_URL=http://localhost:8000
APP_KEY=                   # Generate with: php artisan key:generate
```

### Docker Configuration

```env
NGINX_PORT=8000           # Web server port
REDIS_PORT=6379           # Redis port
VITE_PORT=5173            # Vite dev server port
POSTGRES_PORT=5432        # PostgreSQL port (if using)
```

### Auto Migration & Seeding

```env
AUTO_MIGRATE=false        # Auto-run migrations on container start
AUTO_SEED=false           # Auto-seed database on container start
```

**⚠️ Warning:** Only enable `AUTO_MIGRATE` and `AUTO_SEED` for development!

### Database - SQLite (Default)

```env
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/database/database.sqlite
```

### Database - PostgreSQL (Optional)

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=certify
DB_USERNAME=certify
DB_PASSWORD=secret
```

### Redis Configuration

```env
REDIS_HOST=redis          # Use 'redis' for Docker, '127.0.0.1' for local
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

---

## 📂 Volume Mounts

### Development

-   **Application Code**: `./ → /var/www` (live reload)
-   **SQLite Database**: `./database → /var/www/database`
-   **Node Modules**: Named volume for performance

### Production

-   **Storage**: `laravel-storage-production` (certificates, uploads)
-   **SQLite Database**: `sqlite-data-production` (if using SQLite)
-   **PostgreSQL Data**: `postgres-data-production` (if using PostgreSQL)

---

## 🔒 Security Best Practices

### Production Checklist

-   [ ] Set `APP_ENV=production`
-   [ ] Set `APP_DEBUG=false`
-   [ ] Generate secure `APP_KEY`
-   [ ] Use strong database passwords
-   [ ] Configure proper file permissions
-   [ ] Enable HTTPS with reverse proxy
-   [ ] Set up regular backups
-   [ ] Configure firewall rules
-   [ ] Use Redis for sessions/cache
-   [ ] Enable queue workers for async jobs

### File Permissions

```bash
# Set proper permissions
docker compose exec php-fpm chown -R www-data:www-data /var/www/storage
docker compose exec php-fpm chmod -R 775 /var/www/storage
```

---

## 🚦 Troubleshooting

### Issue: Permission Denied

```bash
# Fix storage permissions
docker compose exec php-fpm chown -R www-data:www-data /var/www/storage
docker compose exec php-fpm chmod -R 775 /var/www/storage
```

### Issue: Database Connection Failed

**For SQLite:**

```bash
# Check if database file exists
docker compose exec php-fpm ls -la /var/www/database/

# Create database file manually
docker compose exec php-fpm touch /var/www/database/database.sqlite
docker compose exec php-fpm chmod 664 /var/www/database/database.sqlite
```

**For PostgreSQL:**

```bash
# Check if PostgreSQL is running
docker compose ps postgres

# Check PostgreSQL logs
docker compose logs postgres

# Verify connection
docker compose exec php-fpm php artisan db:show
```

### Issue: Port Already in Use

```bash
# Change port in .env file
NGINX_PORT=8080  # or any available port

# Restart containers
docker compose down
docker compose up -d
```

### Issue: Container Won't Start

```bash
# View detailed logs
docker compose logs -f

# Rebuild containers
docker compose down
docker compose up -d --build --force-recreate
```

### Issue: Assets Not Loading

```bash
# Rebuild assets
docker compose exec node npm run build

# Clear Laravel cache
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan view:clear
```

---

## 📊 Performance Optimization

### Production Optimizations

```bash
# Cache configuration
docker compose exec php-fpm php artisan config:cache

# Cache routes
docker compose exec php-fpm php artisan route:cache

# Cache views
docker compose exec php-fpm php artisan view:cache

# Optimize Composer autoloader
docker compose exec php-fpm composer install --optimize-autoloader --no-dev
```

### Resource Limits

Add resource limits to `compose.prod.yaml`:

```yaml
php-fpm:
    deploy:
        resources:
            limits:
                cpus: "1"
                memory: 512M
            reservations:
                cpus: "0.5"
                memory: 256M
```

---

## 🔄 Backup & Restore

### Backup SQLite Database

```bash
# Copy database file
docker compose cp certify-php-fpm-dev:/var/www/database/database.sqlite ./backup-$(date +%Y%m%d).sqlite

# Or use docker volume backup
docker run --rm -v certify_sqlite-data-production:/data -v $(pwd):/backup alpine tar czf /backup/database-backup-$(date +%Y%m%d).tar.gz /data
```

### Backup PostgreSQL Database

```bash
# Export database
docker compose exec postgres pg_dump -U certify certify > backup-$(date +%Y%m%d).sql

# Compressed backup
docker compose exec postgres pg_dump -U certify certify | gzip > backup-$(date +%Y%m%d).sql.gz
```

### Restore Database

**SQLite:**

```bash
# Copy backup file to container
docker compose cp backup-20250107.sqlite certify-php-fpm-dev:/var/www/database/database.sqlite

# Run migrations to update schema
docker compose exec php-fpm php artisan migrate --force
```

**PostgreSQL:**

```bash
# Restore from backup
docker compose exec -T postgres psql -U certify certify < backup-20250107.sql
```

### Backup Storage Files

```bash
# Backup certificates and uploads
docker compose cp certify-php-fpm-dev:/var/www/storage/app/public ./storage-backup-$(date +%Y%m%d)
```

---

## 🔗 Useful Links

-   **Laravel Documentation**: https://laravel.com/docs
-   **Docker Documentation**: https://docs.docker.com
-   **PHP-FPM Health Check**: https://github.com/renatomefi/php-fpm-healthcheck
-   **Nginx Configuration**: https://nginx.org/en/docs/

---

## 🆘 Support

### Test Accounts

-   **Root**: root@certify.com / password
-   **User**: user@certify.com / password

### Log Files

```bash
# Laravel logs
docker compose exec php-fpm tail -f /var/www/storage/logs/laravel.log

# Nginx logs
docker compose exec web tail -f /var/log/nginx/error.log
docker compose exec web tail -f /var/log/nginx/access.log
```

---

## 📜 License

Laravel Certificate Generator - Professional certificate generation and management system.
