@echo off
setlocal enabledelayedexpansion

REM Docker Initialization Script for Certificate Generator (Windows)
REM This script sets up the Docker environment for the first time

echo ================================================================
echo    Certificate Generator - Docker Initialization
echo ================================================================
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker is not installed. Please install Docker Desktop first.
    pause
    exit /b 1
)

REM Check if Docker Compose is installed
docker compose version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker Compose is not installed. Please install Docker Compose first.
    pause
    exit /b 1
)

echo [OK] Docker is installed
echo [OK] Docker Compose is installed
echo.

REM Ask for environment type
echo Which environment do you want to set up?
echo 1) Development (with SQLite)
echo 2) Development (with PostgreSQL)
echo 3) Production (with SQLite)
echo 4) Production (with PostgreSQL)
set /p env_choice="Enter your choice [1-4]: "

if "%env_choice%"=="1" (
    set ENV_TYPE=development
    set DB_TYPE=sqlite
    set COMPOSE_FILE=docker-compose.yml
) else if "%env_choice%"=="2" (
    set ENV_TYPE=development
    set DB_TYPE=postgresql
    set COMPOSE_FILE=docker-compose.yml
) else if "%env_choice%"=="3" (
    set ENV_TYPE=production
    set DB_TYPE=sqlite
    set COMPOSE_FILE=compose.prod.yaml
) else if "%env_choice%"=="4" (
    set ENV_TYPE=production
    set DB_TYPE=postgresql
    set COMPOSE_FILE=compose.prod.yaml
) else (
    echo [ERROR] Invalid choice. Exiting.
    pause
    exit /b 1
)

echo.
echo Setting up %ENV_TYPE% environment with %DB_TYPE%...
echo.

REM Step 1: Copy environment file
if not exist .env (
    echo [INFO] Copying .env.docker to .env...
    copy .env.docker .env >nul
    echo [OK] .env file created
) else (
    echo [WARNING] .env file already exists. Skipping...
)

REM Step 2: Configure database
if "%DB_TYPE%"=="postgresql" (
    echo [INFO] Configuring PostgreSQL...
    echo [WARNING] Please manually update .env file for PostgreSQL configuration
)

REM Step 3: Update APP_ENV for production
if "%ENV_TYPE%"=="production" (
    echo [INFO] Production environment configured
    echo [WARNING] Please update APP_ENV=production and APP_DEBUG=false in .env file
)

REM Step 4: Build and start containers
echo.
echo [INFO] Building Docker containers...
docker compose -f %COMPOSE_FILE% up -d --build

if errorlevel 1 (
    echo [ERROR] Failed to start containers
    pause
    exit /b 1
)

echo [OK] Containers started
echo.

REM Step 5: Wait for containers to be ready
echo [INFO] Waiting for containers to be ready...
timeout /t 5 /nobreak >nul

REM Step 6: Generate application key
echo [INFO] Generating application key...
docker compose -f %COMPOSE_FILE% exec php-fpm php artisan key:generate --force

REM Step 7: Run migrations
echo [INFO] Running database migrations...
docker compose -f %COMPOSE_FILE% exec php-fpm php artisan migrate --force

REM Step 8: Seed database
echo [INFO] Seeding database...
docker compose -f %COMPOSE_FILE% exec php-fpm php artisan db:seed --force

REM Step 9: Create storage link
echo [INFO] Creating storage symbolic link...
docker compose -f %COMPOSE_FILE% exec php-fpm php artisan storage:link

REM Step 10: Optimize (production only)
if "%ENV_TYPE%"=="production" (
    echo [INFO] Optimizing application...
    docker compose -f %COMPOSE_FILE% exec php-fpm php artisan config:cache
    docker compose -f %COMPOSE_FILE% exec php-fpm php artisan route:cache
    docker compose -f %COMPOSE_FILE% exec php-fpm php artisan view:cache
)

echo.
echo ================================================================
echo    Setup Complete!
echo ================================================================
echo.
echo Application is running at:

if "%ENV_TYPE%"=="production" (
    echo   http://localhost:80
) else (
    echo   http://localhost:8000
)

echo.
echo Default accounts:
echo   Root: root@certify.com / password
echo   User: user@certify.com / password
echo.
echo Useful commands:
echo   View logs:        docker compose -f %COMPOSE_FILE% logs -f
echo   Stop containers:  docker compose -f %COMPOSE_FILE% down
echo   Restart:          docker compose -f %COMPOSE_FILE% restart
echo   Shell access:     docker compose -f %COMPOSE_FILE% exec php-fpm sh
echo.
echo For more commands, see README-DOCKER.md
echo.
pause
