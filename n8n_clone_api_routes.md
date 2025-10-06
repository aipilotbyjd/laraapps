# Complete n8n Clone - API Routes Documentation

## 🔄 API Routes Overview

All API routes are prefixed with `/api/v1/` and require authentication via Laravel Sanctum.

### Authentication Headers
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

## 📋 Complete API Routes

### 1. Workflow Routes
```
GET    /api/v1/workflows                           # List workflows
POST   /api/v1/workflows                           # Create workflow
GET    /api/v1/workflows/{id}                     # Get workflow
PUT    /api/v1/workflows/{id}                     # Update workflow
DELETE /api/v1/workflows/{id}                     # Delete workflow
POST   /api/v1/workflows/{id}/execute             # Execute workflow manually
POST   /api/v1/workflows/{id}/duplicate           # Duplicate workflow
POST   /api/v1/workflows/{id}/activate            # Activate workflow
POST   /api/v1/workflows/{id}/deactivate          # Deactivate workflow
GET    /api/v1/workflows/{id}/versions            # Get workflow versions
POST   /api/v1/workflows/{id}/versions/{version}  # Restore workflow version
```

### 2. Execution Routes
```
GET    /api/v1/executions                         # List executions
GET    /api/v1/executions/{id}                   # Get execution
POST   /api/v1/executions/{id}/retry             # Retry failed execution
POST   /api/v1/executions/{id}/cancel            # Cancel running execution
DELETE /api/v1/executions/{id}                   # Delete execution
GET    /api/v1/executions/statistics             # Get execution statistics
```

### 3. Webhook Routes
```
POST   /api/v1/webhooks/{id}/test               # Test webhook
GET    /webhook/{webhookId}                      # Public webhook endpoint (GET)
POST   /webhook/{webhookId}                      # Public webhook endpoint (POST)
PUT    /webhook/{webhookId}                      # Public webhook endpoint (PUT)
PATCH  /webhook/{webhookId}                      # Public webhook endpoint (PATCH)
DELETE /webhook/{webhookId}                      # Public webhook endpoint (DELETE)
```

### 4. Credential Routes
```
GET    /api/v1/credentials                        # List credentials
POST   /api/v1/credentials                        # Create credential
GET    /api/v1/credentials/{id}                 # Get credential
PUT    /api/v1/credentials/{id}                 # Update credential
DELETE /api/v1/credentials/{id}                 # Delete credential
POST   /api/v1/credentials/{id}/test            # Test credential
```

### 5. Node Routes
```
GET    /api/v1/nodes                             # List all nodes
GET    /api/v1/nodes/by-group                    # List nodes grouped by category
GET    /api/v1/nodes/{type}                      # Get specific node definition
```

### 6. Tag Routes
```
GET    /api/v1/tags                              # List tags
POST   /api/v1/tags                              # Create tag
GET    /api/v1/tags/{id}                        # Get tag
PUT    /api/v1/tags/{id}                        # Update tag
DELETE /api/v1/tags/{id}                        # Delete tag
```

### 7. Schedule Routes
```
GET    /api/v1/schedules                         # List schedules
POST   /api/v1/schedules                        # Create schedule
GET    /api/v1/schedules/{id}                   # Get schedule
PUT    /api/v1/schedules/{id}                  # Update schedule
DELETE /api/v1/schedules/{id}                  # Delete schedule
```

## 📡 Detailed Route Specifications

### Workflows

#### List Workflows
```
GET /api/v1/workflows

Query Parameters:
- page: int (default: 1)
- per_page: int (default: 20, max: 100)
- active: boolean (filter by active status)
- search: string (search by name)
- tags: array (filter by tag names)

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Sample Workflow",
      "description": "A sample workflow",
      "active": true,
      "nodes": [...],
      "connections": [...],
      "settings": {...},
      "execution_count": 5,
      "last_executed_at": "2023-01-01T12:00:00Z",
      "tags": [...],
      "webhooks": [...],
      "schedules": [...],
      "created_at": "2023-01-01T10:00:00Z",
      "updated_at": "2023-01-01T11:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

#### Create Workflow
```
POST /api/v1/workflows

Request Body:
{
  "name": "string, required",
  "description": "string, optional",
  "active": "boolean, optional",
  "nodes": "array, required",
  "connections": "array, required",
  "settings": "object, optional",
  "tags": "array of tag IDs, optional"
}

Response:
{
  "id": 1,
  "name": "Sample Workflow",
  "description": "A sample workflow",
  "active": true,
  "nodes": [...],
  "connections": [...],
  "settings": {...},
  "execution_count": 0,
  "last_executed_at": null,
  "tags": [...],
  "webhooks": [...],
  "schedules": [...],
  "created_at": "2023-01-01T10:00:00Z",
  "updated_at": "2023-01-01T10:00:00Z"
}
```

#### Execute Workflow
```
POST /api/v1/workflows/{id}/execute

Request Body:
{
  "data": {
    "any": "data to pass to workflow"
  }
}

Response:
{
  "execution_id": 1,
  "status": "success",
  "output": {
    "result": "workflow output"
  }
}
```

### Executions

#### List Executions
```
GET /api/v1/executions

Query Parameters:
- page: int (default: 1)
- per_page: int (default: 50, max: 100)
- workflow_id: int (filter by workflow)
- status: string (filter by status: waiting, running, success, failed, cancelled)
- mode: string (filter by mode: manual, trigger, webhook, schedule, retry)
- from: date (filter by date range start)
- to: date (filter by date range end)

Response:
{
  "data": [
    {
      "id": 1,
      "workflow_id": 1,
      "workflow_name": "Sample Workflow",
      "status": "success",
      "mode": "manual",
      "input_data": {...},
      "output_data": {...},
      "error_message": null,
      "retry_count": 0,
      "started_at": "2023-01-01T12:00:00Z",
      "finished_at": "2023-01-01T12:00:05Z",
      "duration_ms": 5000,
      "execution_data": [...],
      "created_at": "2023-01-01T12:00:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Webhooks

#### Public Webhook Endpoint
```
POST /webhook/{webhookId}

Request Body:
{
  "any": "data sent to webhook"
}

Response:
{
  "message": "Workflow triggered successfully"
}
```

### Credentials

#### Create Credential
```
POST /api/v1/credentials

Request Body:
{
  "name": "string, required",
  "type": "string, required (api_key, oauth2, basic_auth, bearer_token)",
  "data": "object, required (credential-specific data)"
}

Response:
{
  "id": 1,
  "name": "API Key Credential",
  "type": "api_key",
  "last_used_at": null,
  "created_at": "2023-01-01T10:00:00Z"
}
```

### Nodes

#### List All Nodes
```
GET /api/v1/nodes

Response:
[
  {
    "name": "HTTP Request",
    "type": "http_request",
    "group": "action",
    "version": 1,
    "description": "Make HTTP requests to any URL",
    "inputs": ["main"],
    "outputs": ["main"],
    "properties": [...],
    "credentials": [...]
  }
]
```

## 🔐 Authentication & Authorization

### Authentication
All API routes (except public webhooks) require Bearer Token authentication:
```
Authorization: Bearer {your-api-token}
```

### Authorization Levels
- **Owner**: Full access to workflow (create, read, update, delete, execute)
- **Editor**: Read, update, execute access
- **Viewer**: Read-only access
- **Executor**: Execute-only access

## 📊 Rate Limiting

API endpoints are rate-limited to:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated requests (public endpoints)
- 1000 requests per hour for webhook endpoints

## 🛠️ Error Responses

### Common HTTP Status Codes
- `200` OK - Successful GET, PUT requests
- `201` Created - Successful POST requests
- `204` No Content - Successful DELETE requests
- `400` Bad Request - Invalid request data
- `401` Unauthorized - Missing or invalid authentication
- `403` Forbidden - Insufficient permissions
- `404` Not Found - Resource doesn't exist
- `422` Unprocessable Entity - Validation errors
- `429` Too Many Requests - Rate limit exceeded
- `500` Internal Server Error - Server-side errors

### Error Response Format
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for field"]
  }
}
```

## 🧪 Webhook Testing

### Test Webhook Endpoint
```
POST /api/v1/webhooks/{id}/test

Request Body:
{
  "test": "data to simulate webhook payload"
}

Response:
{
  "execution_id": 1,
  "status": "success",
  "output": {
    "result": "simulated execution output"
  }
}
```

## 📈 Statistics Endpoint

### Execution Statistics
```
GET /api/v1/executions/statistics

Response:
{
  "total_executions": 1000,
  "successful": 850,
  "failed": 100,
  "running": 25,
  "cancelled": 25,
  "avg_duration": 2500,
  "executions_by_day": [
    {
      "date": "2023-01-01",
      "count": 45
    }
  ]
}
```

## 🎯 CORS Configuration

The API supports CORS with the following configuration:
- Allowed Origins: `*` (configurable)
- Allowed Methods: `GET, POST, PUT, PATCH, DELETE, OPTIONS`
- Allowed Headers: `Authorization, Content-Type, Accept, X-Requested-With`
- Exposed Headers: `*`
- Max Age: `86400` (24 hours)

## 📦 Request/Response Formats

### Content Types
- Request: `application/json`
- Response: `application/json`
- Webhook payloads: Any content type (handled by individual nodes)

### Pagination
All list endpoints support pagination:
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "...",
    "per_page": 20,
    "to": 20,
    "total": 200
  }
}
```

## 🚀 WebSocket Support (Optional)

Real-time updates can be enabled via WebSocket connections:
- Endpoint: `/ws`
- Events: `execution.created`, `execution.updated`, `execution.completed`
- Authentication: Bearer token in connection headers

This completes the comprehensive API routes documentation for your n8n clone implementation.