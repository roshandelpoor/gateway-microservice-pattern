# Gateway Services Docker Setup

This repository contains three Laravel microservices: Account, Order, and Payment, configured to run in Docker containers.

## Services

- **Account Service**: Runs on port 8081
- **Order Service**: Runs on port 8082  
- **Payment Service**: Runs on port 8083

## Prerequisites

- Docker
- Docker Compose

## Setup Instructions

1. **Create .env files for each service** (copy from .env.example if available):
   ```bash
   # For Account service
   cp Account/.env.example Account/.env
   
   # For Order service  
   cp Order/.env.example Order/.env
   
   # For Payment service
   cp Payment/.env.example Payment/.env
   ```

2. **Generate application keys** for each service:
   ```bash
   # Generate keys for each service
   docker-compose exec account php artisan key:generate
   docker-compose exec order php artisan key:generate
   docker-compose exec payment php artisan key:generate
   ```

3. **Run database migrations**:
   ```bash
   docker-compose exec account php artisan migrate
   docker-compose exec order php artisan migrate
   docker-compose exec payment php artisan migrate
   ```

## Running the Services

### Start all services:
```bash
docker-compose up -d
```

### Start specific service:
```bash
docker-compose up -d account
docker-compose up -d order
docker-compose up -d payment
```

### View logs:
```bash
docker-compose logs -f account
docker-compose logs -f order
docker-compose logs -f payment
```

### Stop services:
```bash
docker-compose down
```

## Accessing the Services

- Account Service: http://localhost:8081
- Order Service: http://localhost:8082
- Payment Service: http://localhost:8083

## Database

Each service uses SQLite by default. If you want to use MySQL instead, update the environment variables in docker-compose.yml and uncomment the MySQL service.

## Development

The services are configured with volume mounts for development, so changes to the code will be reflected immediately without rebuilding the containers.

## Troubleshooting

1. **Permission issues**: Make sure the storage directories have proper permissions:
   ```bash
   sudo chown -R $USER:$USER Account/storage Order/storage Payment/storage
   sudo chmod -R 755 Account/storage Order/storage Payment/storage
   ```

2. **Container won't start**: Check logs for specific errors:
   ```bash
   docker-compose logs service-name
   ```

3. **Database issues**: Ensure SQLite files exist and have proper permissions:
   ```bash
   touch Account/database/database.sqlite
   touch Order/database/database.sqlite
   touch Payment/database/database.sqlite
   ```

## RUN
   ```bash
   ./start.sh

   curl http://localhost:8090/accounts/api/health
   curl http://localhost:8090/orders/api/health
   curl http://localhost:8090/payments/api/health
   ```


## check-list
   ```bash
   1-  octan swoole
   2-  change Dockerfile for octan
   3-  k6 stress test
   4-  rate limit and config enable this and config count of rate limit
   5-  logging middleware for all services and add uuid tracker in all logs in all services as unique key
   6-  elk stack
   7-  implement authentication
   8-  implement circute breaker
   9-  login with account and add check token in payment and order from header bearer
   10- config with service needs authentication
   11- saga pattern
   ```