# Agents Guide

## Git Workflow
- **CRITICAL**: Create new branch for ALL new features
- **ALWAYS**: Ask for review and confirmation before commits or merges
- **NEVER**: Commit or merge without explicit user approval

## Project Structure
- **Backend**: Laravel 12 PHP application (backend/)
- **Frontend**: React + TypeScript + Vite (frontend/)
- **Container**: Uses Podman with docker-compose

## Build/Test Commands
- `just up` - Start services in detached mode
- `just test` - Run backend tests via PHPUnit
- `just artisan test --filter=TestName` - Run single test
- `cd frontend && bun run lint` - Lint frontend
- `cd frontend && bun run build` - Build frontend

## Package Management - MISSION CRITICAL
- **NEVER EDIT**: composer.json, package.json, or any package manifest files directly
- **ALWAYS USE**: Package manager commands instead:
  - `composer require package-name` (production dependencies)
  - `composer require --dev package-name` (development dependencies)
  - `bun add package-name` (production dependencies)
  - `bun add --dev package-name` (development dependencies)
- **REASONING**: Package managers handle versioning, conflicts, and lock files correctly
- **EXAMPLES**: Testing libraries should use --dev flag, runtime dependencies should not

## Code Style
- **PHP**: PSR-4 autoloading, snake_case methods, PascalCase classes
- **TypeScript**: camelCase variables/functions, PascalCase components
- **Imports**: Group by external libs first, then local imports
- **Files**: Use absolute paths from project root
- **Error Handling**: Use Laravel exceptions, try/catch for async operations

## Testing
- Backend tests in `tests/Feature/` and `tests/Unit/`
- Use `$this->get()` for HTTP tests, `assertStatus()` for responses
- PHPUnit configuration in `phpunit.xml`