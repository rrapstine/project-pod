# Project Pod API Specification

## Overview

- **Base URL**: `https://api.projectpod.test/v1` (dev), `https://api.yourdomain.com/v1` (prod)
- **Authentication**: Laravel Sanctum Bearer Token
- **Content Type**: `application/json`
- **Framework**: Laravel 12 with Eloquent ORM
- **Response Format**: Laravel API Resources

## Authentication

### POST `/v1/auth/register`
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
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": null,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123..."
  }
}
```

### POST `/v1/auth/login`
Authenticate user and return access token.

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
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": null,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123..."
  }
}
```

### POST `/v1/auth/logout`
Revoke current access token.

**Headers:** `Authorization: Bearer {token}`

**Response (204):** No content

### GET `/v1/auth/user`
Get authenticated user information.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

## Workspaces

### GET `/v1/workspaces`
List all workspaces for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (default: 15, max: 100)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Personal Projects",
      "description": "My personal side projects",
      "color": "#3b82f6",
      "projects_count": 5,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "links": {
    "first": "https://api.projectpod.test/v1/workspaces?page=1",
    "last": "https://api.projectpod.test/v1/workspaces?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "https://api.projectpod.test/v1/workspaces",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

### POST `/v1/workspaces`
Create a new workspace.

**Headers:** `Authorization: Bearer {token}`

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
  "data": {
    "id": 2,
    "name": "Work Projects",
    "description": "Professional work projects",
    "color": "#ef4444",
    "projects_count": 0,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### GET `/v1/workspaces/{workspace}`
Get workspace details with projects.

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Personal Projects",
    "description": "My personal side projects",
    "color": "#3b82f6",
    "projects_count": 5,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "projects": [
      {
        "id": 1,
        "name": "Task Manager App",
        "description": "A simple task management application",
        "status": "active",
        "due_date": null,
        "tasks_count": 12,
        "completed_tasks_count": 4,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

### PUT `/v1/workspaces/{workspace}`
Update workspace.

**Headers:** `Authorization: Bearer {token}`

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
  "data": {
    "id": 1,
    "name": "Updated Workspace Name",
    "description": "Updated description",
    "color": "#10b981",
    "projects_count": 5,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  }
}
```

### DELETE `/v1/workspaces/{workspace}`
Delete workspace and all associated projects/tasks.

**Headers:** `Authorization: Bearer {token}`

**Response (204):** No content

## Projects

### GET `/v1/projects`
List all projects across all workspaces for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page
- `status` (string): Filter by status (`active`, `completed`, `archived`)
- `workspace_id` (integer): Filter by workspace
- `has_tasks_due_before` (date): Projects with tasks due before date (Y-m-d)
- `has_tasks_due_this_week` (boolean): Projects with tasks due this week
- `include` (string): Comma-separated relations to include (`workspace`, `tasks`)

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Task Manager App",
      "description": "A simple task management application",
      "workspace_id": 1,
      "status": "active",
      "due_date": null,
      "tasks_count": 12,
      "completed_tasks_count": 4,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "workspace": {
        "id": 1,
        "name": "Personal Projects",
        "color": "#3b82f6"
      }
    }
  ],
  "links": {
    "first": "https://api.projectpod.test/v1/projects?page=1",
    "last": "https://api.projectpod.test/v1/projects?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "path": "https://api.projectpod.test/v1/projects",
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

### GET `/v1/workspaces/{workspace}/projects`
List projects within specific workspace.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page`, `per_page`, `status`, `include` (same as above)

**Response (200):** Same format as above, filtered to workspace

### POST `/v1/workspaces/{workspace}/projects`
Create project in specific workspace.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Project description",
  "status": "active",
  "due_date": "2025-03-01"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 2,
    "name": "New Project",
    "description": "Project description",
    "workspace_id": 1,
    "status": "active",
    "due_date": "2025-03-01",
    "tasks_count": 0,
    "completed_tasks_count": 0,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### GET `/v1/projects/{project}`
Get project details.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `include` (string): Relations to include (`workspace`, `tasks`)

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Task Manager App",
    "description": "A simple task management application",
    "workspace_id": 1,
    "status": "active",
    "due_date": null,
    "tasks_count": 12,
    "completed_tasks_count": 4,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "workspace": {
      "id": 1,
      "name": "Personal Projects",
      "color": "#3b82f6"
    },
    "tasks": [
      {
        "id": 1,
        "title": "Set up authentication",
        "description": "Implement Laravel Sanctum",
        "status": "in_progress",
        "priority": "high",
        "due_date": null,
        "completed_at": null,
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

### PUT `/v1/projects/{project}`
Update project.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "description": "Updated description",
  "status": "completed",
  "due_date": "2025-04-01"
}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Updated Project Name",
    "description": "Updated description",
    "workspace_id": 1,
    "status": "completed",
    "due_date": "2025-04-01",
    "tasks_count": 12,
    "completed_tasks_count": 4,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  }
}
```

### DELETE `/v1/projects/{project}`
Delete project and all associated tasks.

**Headers:** `Authorization: Bearer {token}`

**Response (204):** No content

## Tasks

### GET `/v1/tasks` (Optional Future Feature)
List all tasks across all projects for authenticated user.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page`, `per_page` (pagination)
- `status` (string): Filter by status (`todo`, `in_progress`, `done`)
- `priority` (string): Filter by priority (`low`, `medium`, `high`)
- `project_id` (integer): Filter by project
- `due_before` (date): Tasks due before date (Y-m-d)
- `due_this_week` (boolean): Tasks due this week
- `include` (string): Relations to include (`project`, `project.workspace`)

### GET `/v1/projects/{project}/tasks`
List tasks within specific project.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page`, `per_page`, `status`, `priority`, `include`

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Set up authentication",
      "description": "Implement Laravel Sanctum for API authentication",
      "project_id": 1,
      "status": "in_progress",
      "priority": "high",
      "due_date": null,
      "completed_at": null,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### POST `/v1/projects/{project}/tasks`
Create task in specific project.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "title": "New Task",
  "description": "Task description",
  "status": "todo",
  "priority": "medium",
  "due_date": "2025-01-15"
}
```

**Response (201):**
```json
{
  "data": {
    "id": 2,
    "title": "New Task",
    "description": "Task description",
    "project_id": 1,
    "status": "todo",
    "priority": "medium",
    "due_date": "2025-01-15",
    "completed_at": null,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z"
  }
}
```

### GET `/v1/tasks/{task}`
Get task details.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `include` (string): Relations to include (`project`, `project.workspace`)

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Set up authentication",
    "description": "Implement Laravel Sanctum for API authentication",
    "project_id": 1,
    "status": "in_progress",
    "priority": "high",
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
  }
}
```

### PUT `/v1/tasks/{task}`
Update task.

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "title": "Updated Task Title",
  "description": "Updated description",
  "status": "done",
  "priority": "low",
  "due_date": "2025-02-01"
}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Updated Task Title",
    "description": "Updated description",
    "project_id": 1,
    "status": "done",
    "priority": "low",
    "due_date": "2025-02-01",
    "completed_at": "2025-01-01T00:01:00.000000Z",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  }
}
```

### PATCH `/v1/tasks/{task}/status`
Update only task status (convenience endpoint).

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "done"
}
```

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "title": "Set up authentication",
    "description": "Implement Laravel Sanctum for API authentication",
    "project_id": 1,
    "status": "done",
    "priority": "high",
    "due_date": null,
    "completed_at": "2025-01-01T00:01:00.000000Z",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:01:00.000000Z"
  }
}
```

### DELETE `/v1/tasks/{task}`
Delete task.

**Headers:** `Authorization: Bearer {token}`

**Response (204):** No content

## Search

### GET `/v1/search`
Global search across workspaces, projects, and tasks.

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `q` (string, required): Search query
- `type` (string): Filter by type (`workspaces`, `projects`, `tasks`)
- `page`, `per_page` (pagination)

**Response (200):**
```json
{
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
  }
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

## Laravel Implementation Notes

- Use **Laravel API Resources** for consistent response formatting
- Implement **Form Request classes** for validation (not inline validation)
- Use **Route Model Binding** for automatic model resolution
- Apply **Policy classes** for authorization
- Use **Eloquent relationships** with proper eager loading
- Implement **database indexes** on frequently queried fields
- Use **Laravel pagination** for list endpoints
- Apply **rate limiting** middleware for API protection
