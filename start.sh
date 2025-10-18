#!/bin/bash

echo "🚀 Starting Gateway Services..."

# Create .env files
echo "📝 Setting up environment files..."

if [ ! -f "Account/.env" ]; then
    echo "Creating Account/.env from example..."
    if [ -f "Account/.env.example" ]; then
        cp Account/.env.example Account/.env
    else
        cat > Account/.env << EOF
APP_NAME=Account
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8081
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    fi
fi

if [ ! -f "Order/.env" ]; then
    echo "Creating Order/.env from example..."
    if [ -f "Order/.env.example" ]; then
        cp Order/.env.example Order/.env
    else
        cat > Order/.env << EOF
APP_NAME=Order
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8082
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    fi
fi

if [ ! -f "Payment/.env" ]; then
    echo "Creating Payment/.env from example..."
    if [ -f "Payment/.env.example" ]; then
        cp Payment/.env.example Payment/.env
    else
        cat > Payment/.env << EOF
APP_NAME=Payment
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8083
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    fi
fi

if [ ! -f "GatewayOctan/.env" ]; then
    echo "Creating GatewayOctan/.env from example..."
    if [ f "GatewayOctan/.env.example" ]; then
        cp GatewayOctan/.env.example GatewayOctan/.env
    else
        cat > GatewayOctan/.env << EOF
APP_NAME=GatewayOctan
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8090
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    fi
fi

# Create SQLite databases
echo "🗄️ Creating SQLite databases..."
touch Account/database/database.sqlite
touch Order/database/database.sqlite
touch Payment/database/database.sqlite
touch GatewayOctan/database/database.sqlite

# permissions
echo "🔐 Setting permissions..."
chmod -R 755 Account/storage Order/storage Payment/storage GatewayOctan/storage
chmod 664 Account/database/database.sqlite Order/database/database.sqlite Payment/database/database.sqlite GatewayOctan/database/database.sqlite

# Build and start containers
echo "🐳 Building and starting Docker containers..."
docker-compose up -d --build

# Wait for containers
echo "⏳ Waiting for containers to be ready..."
sleep 15

# Generate application keys
echo "🔑 Generating application keys..."
docker-compose exec -T account php artisan key:generate --force
docker-compose exec -T order php artisan key:generate --force
docker-compose exec -T payment php artisan key:generate --force
docker-compose exec -T gateway-octan php artisan key:generate --force

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec -T account php artisan migrate --force
docker-compose exec -T order php artisan migrate --force
docker-compose exec -T payment php artisan migrate --force
docker-compose exec -T gateway-octan php artisan migrate --force

echo "✅ Gateway Services are now running!"
echo ""
echo "🌐 Access your services:"
echo "   Account Service:       http://localhost:8081"
echo "   Order Service:         http://localhost:8082"
echo "   Payment Service:       http://localhost:8083"
echo "   Gateway Octan Service: http://localhost:8090"
echo ""
echo "📋 Useful commands:"
echo "   View logs:        docker-compose logs -f"
echo "   Stop services:    docker-compose down"
echo "   Restart:          docker-compose restart"
