# Certify - Certificate Generation & Management System

A professional Laravel-based certificate generation and management system for events, courses, and training programs. Built with Laravel 12, Tailwind CSS 4, and Docker support.

## Features

### 🎯 Core Features
- **Template Management**: Create and customize certificate templates with drag-and-drop field positioning
- **Event Management**: Organize events with customizable registration forms
- **Bulk Certificate Generation**: Generate certificates from registrations or manual data entry
- **QR Code Verification**: Each certificate includes a unique QR code for verification
- **PDF Generation**: Automatic PDF generation with custom layouts using DomPDF
- **Public Registration Forms**: Share event-specific registration links
- **Search & Filter**: Full-text search using Laravel Scout with TNTSearch
- **Soft Deletes**: Safe deletion with recovery options
- **Role-based Access**: Built-in authentication and authorization

### 📋 Template System
- Upload custom background images
- Define custom fields with configurable properties
- Predefined fields: name, email, event_name, date
- Visual field positioning (x, y coordinates)
- Font customization (family, size, color, style)
- Form field configuration (show in registration/certificate)
- Static value fields (auto-filled from event settings)

### 🎫 Event Management
- Link events to certificate templates
- Enable/disable public registration
- Configure static values for certificate fields
- Manage event-specific registration fields
- Track registrations and certificates
- Unique event slugs for public URLs

### 📜 Certificate Features
- Auto-generated certificate numbers (CRT-YYYY-NNNNNN)
- QR code generation for verification
- PDF export with custom layouts
- Bulk generation from registrations
- Manual certificate creation
- Certificate regeneration support
- Email distribution capability

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 20+ & NPM
- SQLite (default) or PostgreSQL/MySQL
- Docker & Docker Compose (optional)

## Installation

### Local Development (Without Docker)

1. **Clone the repository**
```bash
git clone https://github.com/Nazrulalif/certify.git
cd certify
```

2. **Install dependencies and setup**
```bash
composer setup
```

This command will:
- Install PHP dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run migrations
- Install NPM dependencies
- Build frontend assets

3. **Configure environment**

Edit `.env` file:
```env
APP_NAME="Certify"
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite

# For MySQL/PostgreSQL, uncomment and configure:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=certify
# DB_USERNAME=root
# DB_PASSWORD=
```

4. **Create SQLite database (if using SQLite)**
```bash
touch database/database.sqlite
```

5. **Run the application**
```bash
composer dev
```

This command starts:
- Laravel development server (http://localhost:8000)
- Queue worker
- Log viewer (Laravel Pail)
- Vite dev server for hot module replacement

6. **Create storage link**
```bash
php artisan storage:link
```

### Docker Development

1. **Clone and configure**
```bash
git clone https://github.com/Nazrulalif/certify.git
cd certify
cp .env.example .env
```

2. **Edit `.env` for Docker**
```env
# Use PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=certify
DB_USERNAME=certify
DB_PASSWORD=secret

# Docker ports
NGINX_PORT=8000
POSTGRES_PORT=5432
REDIS_PORT=6379
VITE_PORT=5173

# Auto setup (optional)
AUTO_MIGRATE=true
AUTO_SEED=true
```

3. **Uncomment PostgreSQL service** in `docker-compose.yml`

4. **Build and start containers**

**Windows:**
```powershell
docker-init.bat
```

**Linux/macOS:**
```bash
chmod +x docker-init.sh
./docker-init.sh
```

5. **Access the application**
- Web: http://localhost:8000
- Vite HMR: http://localhost:5173

### Production Deployment

1. **Build for production**
```bash
docker-compose -f compose.prod.yaml up -d --build
```

2. **Configure production environment**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use production database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=certify

# Configure mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

3. **Run production commands**
```bash
docker exec certify-php-fpm-prod php artisan migrate --force
docker exec certify-php-fpm-prod php artisan optimize
docker exec certify-php-fpm-prod php artisan storage:link
```

## Usage

### Creating a Certificate Template

1. Navigate to **Templates** in the dashboard
2. Click **Create Template**
3. Upload a background image (recommended: A4 landscape, 1122×794px)
4. Configure template fields:
   - **Predefined fields**: name, email, event_name, date
   - **Custom fields**: Add additional fields as needed
5. Position fields on the template canvas:
   - Drag fields to desired positions
   - Adjust font size, family, color
   - Set text alignment and styling
6. Configure field visibility:
   - **Show in Form**: Display in registration form
   - **Show in Certificate**: Display on generated certificate

### Setting Up an Event

1. Navigate to **Events** → **Create Event**
2. Enter event details:
   - Name and description
   - Select certificate template
   - Enable/disable public registration
3. Configure static values:
   - Set values for fields not shown in form (e.g., event_name, date)
4. Customize registration fields (inherited from template)
5. Copy the public registration URL to share

### Generating Certificates

#### From Registrations
1. Navigate to **Events** → Select event → **Registrations**
2. Select registrations to process
3. Click **Generate Certificates**
4. Certificates are created with auto-generated numbers

#### Manual Entry
1. Navigate to **Certificates** → **Create Certificate**
2. Select event
3. Fill in certificate data manually
4. Click **Generate**

### Certificate Verification

Each certificate includes:
- **Unique Certificate Number**: CRT-YYYY-NNNNNN format
- **QR Code**: Scannable code linking to verification page
- **Verification URL**: `https://yourdomain.com/verify/{certificate-number}`

Public verification page displays:
- Certificate details
- Issue date
- Event information
- QR code

## Project Structure

```
certify/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Route controllers
│   │   └── Middleware/       # Custom middleware
│   ├── Models/               # Eloquent models
│   │   ├── Certificate.php
│   │   ├── Event.php
│   │   ├── Registration.php
│   │   └── Template.php
│   ├── Services/             # Business logic
│   │   ├── CertificateService.php
│   │   ├── EventConfigurationService.php
│   │   └── RegistrationService.php
│   └── Providers/
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── docker/                   # Docker configurations
│   ├── development/
│   └── production/
├── public/                   # Public assets
├── resources/
│   ├── css/                  # Styles
│   ├── js/                   # Frontend JavaScript
│   └── views/                # Blade templates
├── routes/
│   └── web/                  # Route definitions
└── storage/
    └── app/
        └── public/
            └── certificates/ # Generated PDFs & QR codes
```

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: SQLite/PostgreSQL/MySQL
- **PDF Generation**: DomPDF
- **QR Codes**: SimpleSoftwareIO/simple-qrcode
- **Search**: Laravel Scout + TNTSearch
- **DataTables**: Yajra DataTables

### Frontend
- **CSS**: Tailwind CSS 4
- **Build Tool**: Vite 7
- **JavaScript**: Vanilla JS + Axios

### DevOps
- **Containers**: Docker + Docker Compose
- **Web Server**: Nginx (Alpine)
- **PHP**: PHP-FPM 8.2
- **Database**: PostgreSQL 16 (optional)
- **Cache**: Redis (optional)

## Available Commands

### Composer Scripts
```bash
composer setup      # Complete project setup
composer dev        # Start development servers
composer test       # Run PHPUnit tests
```

### Artisan Commands
```bash
php artisan migrate              # Run migrations
php artisan db:seed              # Seed database
php artisan storage:link         # Create storage symlink
php artisan queue:work           # Process queue jobs
php artisan scout:import         # Index models for search
php artisan optimize             # Cache config & routes
php artisan optimize:clear       # Clear all caches
```

### NPM Scripts
```bash
npm run dev         # Start Vite dev server
npm run build       # Build for production
```

### Docker Commands
```bash
# Start containers
docker-compose up -d

# View logs
docker-compose logs -f

# Execute commands in container
docker exec certify-php-fpm-dev php artisan migrate

# Stop containers
docker-compose down

# Rebuild containers
docker-compose up -d --build
```

## Configuration

### Database Setup

**SQLite (Default)**
```env
DB_CONNECTION=sqlite
```

**PostgreSQL**
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=certify
DB_USERNAME=certify
DB_PASSWORD=secret
```

**MySQL**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=certify
DB_USERNAME=root
DB_PASSWORD=
```

### File Storage

Update `config/filesystems.php` for cloud storage:

```php
'default' => env('FILESYSTEM_DISK', 'public'),

'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Queue Configuration

For production, use Redis or database queues:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

## API Documentation

This application uses web routes with Livewire components. For API endpoints, refer to:
- `routes/web.php` - Main route definitions
- `routes/web/` - Modular route files

## Testing

```bash
# Run all tests
composer test

# Run specific test
php artisan test --filter CertificateTest

# Run with coverage
php artisan test --coverage
```

## Troubleshooting

### Storage Link Issues
```bash
php artisan storage:link
# If permission denied, check storage folder permissions
chmod -R 775 storage bootstrap/cache
```

### PDF Generation Errors
- Ensure `storage/app/public` is writable
- Check `dompdf` configuration in `config/dompdf.php`
- Verify background images exist in storage

### QR Code Not Displaying
- Check `simple-qrcode` is installed
- Verify SVG files are generated in `storage/app/public/certificates/qrcodes`

### Docker Permission Issues
```bash
# Fix file ownership
docker exec certify-php-fpm-dev chown -R www-data:www-data /var/www/storage
docker exec certify-php-fpm-dev chmod -R 775 /var/www/storage
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Security

If you discover any security-related issues, please email nazrulism17@gmail.com instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- **Author**: Nazrul Alif
- **Email**: nazrulism17@gmail.com
- **Framework**: [Laravel](https://laravel.com)
- **UI**: [Tailwind CSS](https://tailwindcss.com)

## Changelog

### Version 1.0.0 (2025-01-07)
- Initial release
- Template management system
- Event and registration handling
- Certificate generation with QR codes
- PDF export functionality
- Public registration forms
- Search and filtering
- Docker support
- Responsive UI with Tailwind CSS 4

## Support

For support, email nazrulism17@gmail.com or open an issue on GitHub.

---

Made with ❤️ by [Nazrul Alif](https://github.com/Nazrulalif)
