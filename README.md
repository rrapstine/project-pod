# Project Pod

A client/server architecture web application with independent [Laravel](https://laravel.com) backend and [React](https://reactjs.org) frontend, containerized for development.

## Stack

**Backend:**
- [Laravel](https://laravel.com) 12 (PHP 8.2+)
- SQLite database
- [Laravel Octane](https://laravel.com/docs/octane) for performance

**Frontend:**
- [React](https://reactjs.org) 19
- [TypeScript](https://www.typescriptlang.org)
- [Vite](https://vitejs.dev) with Rolldown bundler
- [Bun](https://bun.sh) package manager

**Infrastructure:**
- [Docker](https://docker.com)/[Podman](https://podman.io) containers
- [Just](https://github.com/casey/just) task runner

## Quick Start

### Prerequisites

- [Docker](https://docker.com) or [Podman](https://podman.io)
- [Just](https://github.com/casey/just) task runner

### Development Setup

1. **Clone and start services:**
   ```bash
   git clone <repository-url>
   cd project-pod
   just up
   ```

2. **Install local dependencies (for IDE/LSP support):**
   ```bash
   cd backend && composer install
   cd ../frontend && bun install
   ```

3. **Access the applications:**
   - Backend API: http://localhost:8000
   - Frontend: http://localhost:3000

## Available Commands

### Service Management
- `just up` - Start all services
- `just down` - Stop all services  
- `just restart` - Restart services
- `just rebuild` - Rebuild and start services
- `just logs` - View container logs

### Backend Commands
- `just artisan <command>` - Run Laravel Artisan commands
- `just artisan migrate` - Run database migrations
- `just resetdb` - Fresh database migration
- `just test` - Run Laravel tests

### Frontend Commands
- `just frontend <command>` - Run commands in frontend container
- `just frontend bun install` - Install new packages

## Development Workflow

The development environment uses full directory mounting, meaning:

- **Live code editing**: Changes are reflected immediately
- **New files auto-included**: No need to update Docker configuration
- **OS compatibility**: `vendor/` and `node_modules/` are container-specific to ensure OS compatibility
- **Clean setup**: Clone, install dependencies (optional), run `just up`, start coding

## Environment Configuration

1. **Backend**: Copy `backend/.env.example` to `backend/.env`
2. **Database**: SQLite file auto-created at `./data/database.sqlite`
3. **Ports**: Backend (8000), Frontend (3000)