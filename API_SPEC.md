# Project Pod API Specification

## Overview

- **Auth Routes**: Direct paths (e.g., `/auth/login`, `/auth/register`)
- **API Routes**: Versioned paths with `/v1/` prefix (e.g., `/v1/workspaces`, `/v1/projects`)
- **Authentication**: Laravel Sanctum Session-based (stateful SPA)
- **Content Type**: `application/json`
- **Framework**: Laravel 12 with Eloquent ORM
- **Response Format**: ApiResponses trait wrapping API Resources (`{message, data, status}`)
- **Pagination**: Not implemented for MVP (returns all resources)

## Routing Philosophy

- **Read operations** (index, show): Can be top-level or nested
- **Create operations**: Nested under parent resource to establish relationship
- **Update/Delete operations**: Top-level (simpler for frontend, security enforced via policies)

## Authentication

**Note**: This API uses Laravel Sanctum with session-based authentication for SPA. No bearer tokens are used. The frontend must call `/sanctum/csrf-cookie` before any auth requests.

### POST `/auth/register`
Register a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 201
}
```

### POST `/auth/login`
Authenticate user and create session.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 200
}
```

### POST `/auth/logout`
End user session.

**Response (200):**
```json
{
  "message": "Logout successful",
  "data": null,
  "status": 200
}
```

### GET `/sanctum/csrf-cookie`
Set CSRF cookie for subsequent requests.

**Response (204):** No content (sets CSRF cookies)

## Workspaces ✅ IMPLEMENTED

### GET `/v1/workspaces`
List all workspaces for authenticated user.

**Authentication**: Session-based (no auth header required)

**Response (200):**
```json
{
  "message": "Workspaces retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Personal Projects",
      "description": "My personal side projects",
      "color": "#3b82f6",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "status": 200
}
```

**Note**: No pagination for MVP. Returns all user workspaces.

### POST `/v1/workspaces`
Create a new workspace.

**Authentication**: Session-based

**Request Body:**
```json
{
  "name": "Work Projects",
  "description": "Professional work projects",
  "color": "#ef4444"
}
```

**Response (201):**
```json
{
  "message": "Workspace created successfully",
  "data": {
    "id": 2,
    "name": "Work Projects",
    "description": "Professional work projects",
    "color": "#ef4444",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 201
}
```

### GET `/v1/workspaces/{workspace}`
Get workspace details.

**Authentication**: Session-based

**Response (200):**
```json
{
  "message": "Workspace retrieved successfully",
  "data": {
    "id": 1,
    "name": "Personal Projects",
    "description": "My personal side projects",
    "color": "#3b82f6",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 200
}
```

### PUT `/v1/workspaces/{workspace}`
Update workspace.

**Authentication**: Session-based

**Request Body:**
```json
{
  "name": "Updated Workspace Name",
  "description": "Updated description",
  "color": "#10b981"
}
```

**Response (200):**
```json
{
  "message": "Workspace updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Workspace Name",
    "description": "Updated description",
    "color": "#10b981",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  },
  "status": 200
}
```

### DELETE `/v1/workspaces/{workspace}`
Delete workspace.

**Authentication**: Session-based

**Response (204):** No content

## Projects 📋 PENDING IMPLEMENTATION

### GET `/v1/projects`
List all projects across all workspaces for authenticated user.

**Authentication**: Session-based

**Response (200):**
```json
{
  "message": "Projects retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Task Manager App",
      "description": "A simple task management application",
      "workspace_id": 1,
      "archived": false,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "workspace": {
        "id": 1,
        "name": "Personal Projects",
        "color": "#3b82f6"
      }
    }
  ],
  "status": 200
}
```

## Projects ✅ IMPLEMENTED

### GET `/v1/projects`
List all projects for authenticated user (across all workspaces).

**Authentication**: Session-based

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Task Manager App",
      "description": "A simple task management application",
      "archived": false,
      "workspace_id": 1,
      "workspace_name": "Personal Projects",
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

**Note**: No pagination for MVP. Uses `archived` boolean. Always includes `workspace_name` via eager loading.

### GET `/v1/workspaces/{workspace}/projects`
List projects within specific workspace.

**Authentication**: Session-based

**Response (200):** Same format as `/v1/projects`, filtered to workspace

### POST `/v1/workspaces/{workspace}/projects`
Create project in specific workspace.

**Authentication**: Session-based

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Project description"
}
```

**Notes**: 
- `archived` is optional (defaults to false)
- `workspace_id` and `user_id` are set automatically from route and auth

**Response (201):**
```json
{
  "message": "New Project project created successfully",
  "data": {
    "id": 2,
    "name": "New Project",
    "description": "Project description",
    "archived": false,
    "workspace_id": 1,
    "workspace_name": "Personal Projects",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 201
}
```

### GET `/v1/projects/{project}`
Get project details.

**Authentication**: Session-based

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Task Manager App",
    "description": "A simple task management application",
    "archived": false,
    "workspace_id": 1,
    "workspace_name": "Personal Projects",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### PUT `/v1/projects/{project}`
Update project (supports partial updates).

**Authentication**: Session-based

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "description": "Updated description",
  "archived": true
}
```

**Notes**:
- All fields are optional (partial updates supported)
- Cannot change `workspace_id` or `user_id`

**Response (200):**
```json
{
  "message": "Updated Project Name project updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Project Name",
    "description": "Updated description",
    "archived": true,
    "workspace_id": 1,
    "workspace_name": "Personal Projects",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  },
  "status": 200
}
```

### DELETE `/v1/projects/{project}`
Delete project and all associated tasks (cascade delete).

**Authentication**: Session-based

**Response (204):**
```json
{
  "message": "Project Name project deleted successfully",
  "data": [],
  "status": 204
}
```

## Tasks 📋 PENDING IMPLEMENTATION

### GET `/v1/tasks`
List all tasks across all projects for authenticated user.

**Authentication**: Session-based

**Query Parameters:**
- `status` (string): Filter by status (`todo`, `in_progress`, `done`)
- `project_id` (integer): Filter by project
- `due_date` (date): Tasks due on date (Y-m-d)

### GET `/v1/projects/{project}/tasks`
List tasks within specific project.

**Authentication**: Session-based

**Query Parameters:**
- `status` (string): Filter by status (`todo`, `in_progress`, `done`)

**Response (200):**
```json
{
  "message": "Tasks retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Set up authentication",
      "description": "Implement Laravel Sanctum for API authentication",
      "project_id": 1,
      "status": "in_progress",
      "due_date": null,
      "completed_at": null,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "status": 200
}
```

### POST `/v1/projects/{project}/tasks`
Create task in specific project.

**Authentication**: Session-based

**Request Body:**
```json
{
  "title": "New Task",
  "description": "Task description",
  "status": "todo",
  "due_date": "2025-01-15"
}
```

**Response (201):**
```json
{
  "message": "Task created successfully",
  "data": {
    "id": 2,
    "title": "New Task",
    "description": "Task description",
    "project_id": 1,
    "status": "todo",
    "due_date": "2025-01-15",
    "completed_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  },
  "status": 201
}
```

### GET `/v1/tasks/{task}`
Get task details.

**Authentication**: Session-based

**Response (200):**
```json
{
  "message": "Task retrieved successfully",
  "data": {
    "id": 1,
    "title": "Set up authentication",
    "description": "Implement Laravel Sanctum for API authentication",
    "project_id": 1,
    "status": "in_progress",
    "due_date": null,
    "completed_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "project": {
      "id": 1,
      "name": "Task Manager App",
      "workspace": {
        "id": 1,
        "name": "Personal Projects"
      }
    }
  },
  "status": 200
}
```

### PUT `/v1/tasks/{task}`
Update task.

**Authentication**: Session-based

**Request Body:**
```json
{
  "title": "Updated Task Title",
  "description": "Updated description",
  "status": "done",
  "due_date": "2025-02-01"
}
```

**Response (200):**
```json
{
  "message": "Task updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Task Title",
    "description": "Updated description",
    "project_id": 1,
    "status": "done",
    "due_date": "2025-02-01",
    "completed_at": "2025-01-01T00:01:00.000000Z",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  },
  "status": 200
}
```

### PATCH `/v1/tasks/{task}/status`
Update only task status (convenience endpoint).

**Authentication**: Session-based

**Request Body:**
```json
{
  "status": "done"
}
```

**Response (200):**
```json
{
  "message": "Task status updated successfully",
  "data": {
    "id": 1,
    "title": "Set up authentication",
    "description": "Implement Laravel Sanctum for API authentication",
    "project_id": 1,
    "status": "done",
    "due_date": null,
    "completed_at": "2025-01-01T00:01:00.000000Z",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  },
  "status": 200
}
```

### DELETE `/v1/tasks/{task}`
Delete task.

**Authentication**: Session-based

**Response (204):** No content

## Search 🔮 FUTURE FEATURE

### GET `/v1/search`
Global search across workspaces, projects, and tasks.

**Authentication**: Session-based

**Query Parameters:**
- `q` (string, required): Search query
- `type` (string): Filter by type (`workspaces`, `projects`, `tasks`)

**Response (200):**
```json
{
  "message": "Search results retrieved successfully",
  "data": {
    "workspaces": [
      {
        "id": 1,
        "name": "Personal Projects",
        "description": "My personal side projects",
        "type": "workspace"
      }
    ],
    "projects": [
      {
        "id": 1,
        "name": "Task Manager App",
        "description": "A simple task management application",
        "workspace_name": "Personal Projects",
        "type": "project"
      }
    ],
    "tasks": [
      {
        "id": 1,
        "title": "Set up authentication",
        "description": "Implement Laravel Sanctum",
        "project_name": "Task Manager App",
        "workspace_name": "Personal Projects",
        "type": "task"
      }
    ]
  },
  "status": 200
}
```

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email has already been taken."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized."
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Project] 123"
}
```

## Implementation Status & Architecture

### ✅ Completed Features
- **Authentication**: Session-based via Laravel Sanctum for SPA
- **Workspaces**: Full CRUD with ownership-based authorization
- **Testing**: Comprehensive test suite (34 tests passing)

### 📋 Pending Implementation
- **Projects**: CRUD operations (controller scaffold exists)
- **Tasks**: CRUD operations (controller scaffold exists)

### 🔧 Architecture Decisions

#### Authentication
- **Session-based SPA auth** via Laravel Sanctum (NOT bearer tokens)
- Frontend must call `/sanctum/csrf-cookie` before auth requests
- CORS configured for cross-subdomain requests (`project-pod.test` ↔ `api.project-pod.test`)

#### Routing Strategy
- **Read operations**: Can be top-level (`/v1/projects`) or nested (`/v1/workspaces/{workspace}/projects`)
- **Write operations**: MUST be nested under parent (`POST /v1/workspaces/{workspace}/projects`)

#### Response Format
- All responses use `ApiResponses` trait wrapper: `{message, data, status}`
- Laravel API Resources transform the `data` portion
- No server-side pagination for MVP (frontend handles filtering)

#### Data Model
- **Project**: Uses `archived` boolean (not status enum)
- **Task**: Uses `status` enum (`todo`, `in_progress`, `done`)
- **Workspace**: Optional `color` hex field

#### Authorization
- Ownership-based: Users can only access their own resources
- Policy classes for all models
- No sharing/collaboration features in MVP

### Laravel Implementation Notes

- **Form Request classes** for all validation
- **Route Model Binding** with authorization checks
- **Policy classes** for ownership-based access control
- **Eloquent relationships** with eager loading
- **Database factories** for test data generation
- **Feature tests** for all endpoints
- **PSR-4** code style with explicit return types
- **PHP 8.4** constructor promotion where applicable
