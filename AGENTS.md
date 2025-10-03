# AGENTS.md

## Build, Lint, and Test
- Backend: `just test` (all), `just artisan test --filter=TestName` (single), `just artisan test tests/Feature/ExampleTest.php` (file)
- Frontend: `cd frontend && bun run lint` (lint), `cd frontend && bun run build` (build)
- Format PHP: `vendor/bin/pint --dirty` (REQUIRED before commits)

## Package Management
- NEVER edit composer.json or package.json directly
- Use: `just composer add package-name` (backend), `just bun add package-name` (frontend)

## Code Style
- PHP: PSR-4, snake_case methods, PascalCase classes, curly braces always
- TypeScript: camelCase for variables/functions, PascalCase for components
- Group imports: external libs first, then local imports
- NO comments in code; use PHPDoc only when needed
- Always use explicit return types in PHP

## Laravel/Project Rules
- Use `php artisan make:` for new files
- Use Form Request classes for validation
- Use Eloquent relationships, eager load to prevent N+1
- Use PHP 8.4 constructor promotion

## Auth Architecture
- SPA: Session-based auth via Sanctum (stateful)
- NO API tokens, bearer tokens, or HasApiTokens trait
- Frontend must call `/sanctum/csrf-cookie` before auth requests

## Git Workflow
- Create new branch for all features
- Ask for review before commits/merges
- Update README.md before merging to main

## Commit Management
- **ALWAYS** create semantic commits for all changes since last session
- **NEVER** commit without explicit user request
- Use semantic commit format: `type(scope): description`
- Types: feat, fix, refactor, docs, chore, test, style
- Group related changes into logical commits
- Check `git status`, `git diff`, and `git log` before committing
- Create multiple commits if changes span different concerns
- Example: `feat(auth): implement logout with session management`
