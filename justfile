# =============================================================================
# Project Pod - Development Commands
# =============================================================================
# 
# DEPENDENCY MANAGEMENT (prevents stale volume issues):
#   just composer add <packages>     - Add composer packages with rebuild
#   just composer add <packages> --dev - Add composer dev packages with rebuild  
#   just bun add <packages>          - Add bun packages with rebuild
#   just bun add <packages> --dev    - Add bun dev packages with rebuild
#   just composer remove <packages>  - Remove composer packages with rebuild
#   just bun remove <packages>       - Remove bun packages with rebuild
#
# REBUILD COMMANDS:
#   just rebuild                     - Clear volumes + rebuild (prevents stale deps)
#   just build                       - Standard build (keeps volumes)
#   just nuclear                     - Remove EVERYTHING and rebuild
#
# STANDARD COMMANDS:
#   just up                          - Start services
#   just down                        - Stop services
#   just logs                        - View logs
#   just test [frontend|backend]     - Run tests (defaults to backend)
# =============================================================================

# Start services in detached mode
up:
    #!/usr/bin/env bash
    echo "🚀 Starting services..."
    podman compose up -d

# Rebuild - clears anonymous volumes to prevent stale dependencies
# This is the default rebuild behavior since volume issues are common
rebuild:
    #!/usr/bin/env bash
    echo "🧹 Stopping services and clearing stale volumes..."
    podman compose down
    
    # Remove anonymous volumes to clear stale dependencies
    echo "🗑️  Removing anonymous volumes..."
    podman volume ls -q | grep "project-pod.*_[a-f0-9]" | xargs -r podman volume rm -f
    
    echo "🔨 Building fresh images..."
    podman compose build --no-cache
    
    echo "🚀 Starting services with fresh volumes..."
    podman compose up -d
    
    echo "✅ Rebuild complete!"

# Nuclear option - remove EVERYTHING and rebuild from scratch
nuclear:
    #!/usr/bin/env bash
    echo "☢️  NUCLEAR REBUILD - Removing ALL podman data..."
    echo "This will remove all images, containers, and volumes!"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    if [[ $$REPLY =~ ^[Yy]$$ ]]; then
        podman compose down
        podman system prune -a -f --volumes
        podman compose up -d
        echo "☢️  Nuclear rebuild complete!"
    else
        echo "Nuclear rebuild cancelled."
    fi

# Build services
build:
    #!/usr/bin/env bash
    echo "🔨 Building services..."  
    podman compose build --no-cache

# Stop services
down:
    #!/usr/bin/env bash
    echo "🛑 Stopping services..."
    podman compose down

# Restart services
restart:
    #!/usr/bin/env bash
    echo "🔄 Restarting services..."
    podman compose restart

# Serve a specific service (frontend or backend)
serve service:
    #!/usr/bin/env bash
    if [ "{{service}}" = "frontend" ]; then
        echo "🌐 Starting frontend dev server..."
        podman compose exec frontend bun run dev
    elif [ "{{service}}" = "backend" ]; then
        echo "⚡ Starting backend dev server..."
        podman compose exec api php artisan serve --host=0.0.0.0 --port=80
    else
        echo "❌ Usage: just serve [frontend|backend]"
        exit 1
    fi

# Run Laravel Artisan commands (e.g., just artisan migrate)
artisan *args:
    #!/usr/bin/env bash
    echo "⚡ Running artisan {{args}}..."
    podman compose exec api php artisan {{args}}



# Clean database
resetdb:
    #!/usr/bin/env bash
    echo "🗄️  Resetting database..."
    just artisan migrate:fresh

# Show running containers and their statuses
status:
    #!/usr/bin/env bash
    echo "🐳 Container Status:"
    podman compose ps

# View logs
logs:
    #!/usr/bin/env bash
    echo "📋 Viewing logs..."
    podman compose logs -f

# Run tests (defaults to backend if no argument provided)
test target="backend":
    #!/usr/bin/env bash
    if [ "{{target}}" = "frontend" ]; then
        echo "🧪 Running frontend tests..."
        podman compose exec frontend bun run test
    elif [ "{{target}}" = "backend" ]; then
        echo "🧪 Running backend tests..."
        podman compose exec api php artisan test
    else
        echo "❌ Unknown test target: {{target}}"
        echo "Usage: just test [frontend|backend]"
        exit 1
    fi

# Add composer dependencies with automatic rebuild
composer *args:
    #!/usr/bin/env bash
    if [[ "$1" == "add" ]]; then
        shift  # Remove 'add' from args
        if [[ "$*" == *"--dev"* ]]; then
            echo "📦 Adding composer dev packages: $*"
            cd backend && composer require --dev "$@"
        else
            echo "📦 Adding composer packages: $*"
            cd backend && composer require "$@"
        fi
        echo "🔨 Rebuilding to prevent stale volume issues..."
        just rebuild
    elif [[ "$1" == "remove" ]]; then
        shift  # Remove 'remove' from args
        echo "🗑️ Removing composer packages: $*"
        cd backend && composer remove "$@"
        echo "🔨 Rebuilding to prevent stale volume issues..."
        just rebuild
    else
        echo "📦 Running composer command: $*"
        cd backend && composer "$@"
    fi

# Add bun dependencies with automatic rebuild  
bun *args:
    #!/usr/bin/env bash
    if [[ "$1" == "add" ]]; then
        shift  # Remove 'add' from args
        if [[ "$*" == *"--dev"* ]]; then
            echo "📦 Adding bun dev packages: $*"
            cd frontend && bun add --dev "$@"
        else
            echo "📦 Adding bun packages: $*"
            cd frontend && bun add "$@"
        fi
        echo "🔨 Rebuilding to prevent stale volume issues..."
        just rebuild
    elif [[ "$1" == "remove" ]]; then
        shift  # Remove 'remove' from args
        echo "🗑️ Removing bun packages: $*"
        cd frontend && bun remove "$@"
        echo "🔨 Rebuilding to prevent stale volume issues..."
        just rebuild
    else
        echo "📦 Running bun command: $*"
        cd frontend && bun "$@"
    fi

# Show current anonymous volumes (for debugging)
show volumes:
    @echo "📋 Current anonymous volumes:"
    @podman volume ls | grep -E "(project-pod|VOLUME NAME)"

# Clean only anonymous volumes (safer than nuclear)
clean volumes:
    #!/usr/bin/env bash
    echo "🧹 Cleaning anonymous volumes..."
    podman compose down
    podman volume ls -q | grep "project-pod.*_[a-f0-9]" | xargs -r podman volume rm -f
    echo "✅ Anonymous volumes cleaned!"
