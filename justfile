# Start services in detached mode
up:
    podman compose up -d

# Start services with rebuild in detached mode
rebuild:
    podman compose build --no-cache
    podman compose up -d

# Build services
build:
    podman compose build

# Stop services
down:
    podman compose down

# Restart services
restart:
    podman compose restart

# Run Laravel Artisan commands (e.g., just artisan migrate)
artisan *args:
    podman compose exec api php artisan {{args}}

# Run frontend commands (e.g., just frontend bun install)
frontend *args:
    podman compose exec frontend {{args}}

# Clean database
resetdb:
    just artisan migrate:fresh

# View logs
logs:
    podman compose logs -f

# Run tests
test:
    podman compose exec api php artisan test
