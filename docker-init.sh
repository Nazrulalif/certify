#!/bin/bash

# Docker Initialization Script for Certificate Generator
# This script sets up the Docker environment for the first time

set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   Certificate Generator - Docker Initialization${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker is not installed. Please install Docker first.${NC}"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo -e "${RED}✗ Docker Compose is not installed. Please install Docker Compose first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is installed${NC}"
echo -e "${GREEN}✓ Docker Compose is installed${NC}"
echo ""

# Ask for environment type
echo -e "${YELLOW}Which environment do you want to set up?${NC}"
echo "1) Development (with SQLite)"
echo "2) Development (with PostgreSQL)"
echo "3) Production (with SQLite)"
echo "4) Production (with PostgreSQL)"
read -p "Enter your choice [1-4]: " env_choice

case $env_choice in
    1)
        ENV_TYPE="development"
        DB_TYPE="sqlite"
        COMPOSE_FILE="docker-compose.yml"
        ;;
    2)
        ENV_TYPE="development"
        DB_TYPE="postgresql"
        COMPOSE_FILE="docker-compose.yml"
        ;;
    3)
        ENV_TYPE="production"
        DB_TYPE="sqlite"
        COMPOSE_FILE="compose.prod.yaml"
        ;;
    4)
        ENV_TYPE="production"
        DB_TYPE="postgresql"
        COMPOSE_FILE="compose.prod.yaml"
        ;;
    *)
        echo -e "${RED}Invalid choice. Exiting.${NC}"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}Setting up ${ENV_TYPE} environment with ${DB_TYPE}...${NC}"
echo ""

# Step 1: Copy environment file
if [ ! -f .env ]; then
    echo -e "${YELLOW}→ Copying .env.docker to .env...${NC}"
    cp .env.docker .env
    echo -e "${GREEN}✓ .env file created${NC}"
else
    echo -e "${YELLOW}⚠ .env file already exists. Skipping...${NC}"
fi

# Step 2: Configure database
if [ "$DB_TYPE" = "postgresql" ]; then
    echo -e "${YELLOW}→ Configuring PostgreSQL...${NC}"

    # Generate random password
    DB_PASSWORD=$(openssl rand -base64 12)

    # Update .env file
    sed -i 's/DB_CONNECTION=sqlite/# DB_CONNECTION=sqlite/g' .env
    sed -i 's/# DB_CONNECTION=pgsql/DB_CONNECTION=pgsql/g' .env
    sed -i "s/# DB_PASSWORD=secret/DB_PASSWORD=${DB_PASSWORD}/g" .env

    echo -e "${GREEN}✓ PostgreSQL configured with password: ${DB_PASSWORD}${NC}"
    echo -e "${YELLOW}⚠ Save this password! It's also in your .env file${NC}"
fi

# Step 3: Update APP_ENV
if [ "$ENV_TYPE" = "production" ]; then
    sed -i 's/APP_ENV=local/APP_ENV=production/g' .env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=false/g' .env
    echo -e "${GREEN}✓ Production environment configured${NC}"
fi

# Step 4: Build and start containers
echo ""
echo -e "${YELLOW}→ Building Docker containers...${NC}"
docker compose -f $COMPOSE_FILE up -d --build

echo -e "${GREEN}✓ Containers started${NC}"
echo ""

# Step 5: Wait for containers to be ready
echo -e "${YELLOW}→ Waiting for containers to be ready...${NC}"
sleep 5

# Step 6: Generate application key
echo -e "${YELLOW}→ Generating application key...${NC}"
docker compose -f $COMPOSE_FILE exec php-fpm php artisan key:generate --force

# Step 7: Run migrations
echo -e "${YELLOW}→ Running database migrations...${NC}"
docker compose -f $COMPOSE_FILE exec php-fpm php artisan migrate --force

# Step 8: Seed database
echo -e "${YELLOW}→ Seeding database...${NC}"
docker compose -f $COMPOSE_FILE exec php-fpm php artisan db:seed --force

# Step 9: Create storage link
echo -e "${YELLOW}→ Creating storage symbolic link...${NC}"
docker compose -f $COMPOSE_FILE exec php-fpm php artisan storage:link

# Step 10: Optimize (production only)
if [ "$ENV_TYPE" = "production" ]; then
    echo -e "${YELLOW}→ Optimizing application...${NC}"
    docker compose -f $COMPOSE_FILE exec php-fpm php artisan config:cache
    docker compose -f $COMPOSE_FILE exec php-fpm php artisan route:cache
    docker compose -f $COMPOSE_FILE exec php-fpm php artisan view:cache
fi

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   🎉 Setup Complete!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}Application is running at:${NC}"

if [ "$ENV_TYPE" = "production" ]; then
    echo -e "  ${YELLOW}http://localhost:80${NC}"
else
    echo -e "  ${YELLOW}http://localhost:8000${NC}"
fi

echo ""
echo -e "${GREEN}Default accounts:${NC}"
echo -e "  Root: ${YELLOW}root@certify.com${NC} / ${YELLOW}password${NC}"
echo -e "  User: ${YELLOW}user@certify.com${NC} / ${YELLOW}password${NC}"
echo ""
echo -e "${GREEN}Useful commands:${NC}"
echo -e "  View logs:        ${YELLOW}docker compose -f $COMPOSE_FILE logs -f${NC}"
echo -e "  Stop containers:  ${YELLOW}docker compose -f $COMPOSE_FILE down${NC}"
echo -e "  Restart:          ${YELLOW}docker compose -f $COMPOSE_FILE restart${NC}"
echo -e "  Shell access:     ${YELLOW}docker compose -f $COMPOSE_FILE exec php-fpm sh${NC}"
echo ""
echo -e "${GREEN}For more commands, see README-DOCKER.md${NC}"
echo ""
