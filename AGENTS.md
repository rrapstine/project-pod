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
- `just test` - Run backend tests via PHPUnit
- `just artisan test --filter=TestName` - Run single test
- `just artisan test tests/Feature/ExampleTest.php` - Run specific test file
- `cd frontend && bun run lint` - Lint frontend
- `cd frontend && bun run build` - Build frontend
- `vendor/bin/pint --dirty` - Format PHP code (REQUIRED before commits)

## Package Management - MISSION CRITICAL
- **NEVER EDIT**: composer.json, package.json directly - use commands:
  - `just composer add package-name` (with rebuild)
  - `just composer add package-name --dev` (dev deps with rebuild)
  - `just bun add package-name` (with rebuild)
  - **WHY**: Prevents stale volume issues and handles versioning correctly

## Laravel Boost Requirements
- **ALWAYS** use `php artisan make:` commands for new files (controllers, models, etc.)
- **REQUIRED**: Form Request classes for validation (not inline validation)
- Use PHP 8.4 constructor promotion: `public function __construct(public GitHub $github) {}`
- **ALWAYS** explicit return types: `protected function getName(): string`
- Use Eloquent relationships over raw queries, prevent N+1 with eager loading

## Code Style
- **PHP**: PSR-4, snake_case methods, PascalCase classes, curly braces always
- **TypeScript**: camelCase variables/functions, PascalCase components
- **NO COMMENTS** in code - use PHPDoc blocks only when needed
- Group imports: external libs first, then local imports

## Testing & Quality
- Use `$this->get()`, `assertStatus()` for HTTP tests
- Run specific test after changes: `just artisan test --filter=testName`
- **REQUIRED**: Run `vendor/bin/pint --dirty` before finalizing changes