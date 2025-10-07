# n8n Clone API Documentation

## 📋 Overview

This document provides comprehensive documentation for the n8n Clone API. All endpoints are RESTful and return JSON responses.

## 🔐 Authentication

All API endpoints require Bearer Token authentication via Laravel Passport:

```
Authorization: Bearer {your-access-token}
Accept: application/json
Content-Type: application/json
```

## 🌐 Base URL

```
https://your-domain.com/api/v1/
```

## 📡 API Endpoints

### Health & System

#### Get System Health
```http
GET /api/health
```

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2023-01-01T12:00:00Z",
  "version": "1.0.0",
  "services": {
    "database": "ok",
    "queue": "ok",
    "cache": "ok"
  }
}
```

#### Get Authenticated User
```http
GET /api/user
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "email_verified_at": "2023-01-01T10:00:00Z",
  "created_at": "2023-01-01T10:00:00Z",
  "updated_at": "2023-01-01T10:00:00Z"
}
```

### Workflows

#### List Workflows
```http
GET /api/v1/workflows
```

**Query Parameters:**
- `page` (int) - Page number (default: 1)
- `per_page` (int) - Items per page (default: 15, max: 100)
- `active` (boolean) - Filter by active status
- `search` (string) - Search by name
- `tags` (array) - Filter by tag IDs

**Response:**
```json
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
```http
POST /api/v1/workflows
```

**Request Body:**
```json
{
  "name": "string, required",
  "description": "string, optional",
  "active": "boolean, optional",
  "nodes": "array, required",
  "connections": "array, required",
  "settings": "object, optional",
  "tags": "array of tag IDs, optional"
}
```

**Example Request Body:**
```json
{
  "name": "HTTP Request Workflow",
  "description": "A workflow that makes HTTP requests",
  "active": true,
  "nodes": [
    {
      "id": "1",
      "name": "Webhook Trigger",
      "type": "webhook_trigger",
      "parameters": {
        "method": "POST"
      },
      "position": {"x": 100, "y": 100}
    },
    {
      "id": "2",
      "name": "HTTP Request",
      "type": "http_request",
      "parameters": {
        "method": "GET",
        "url": "https://httpbin.org/get",
        "headers": [],
        "authentication": "none"
      },
      "position": {"x": 300, "y": 100}
    }
  ],
  "connections": [
    {
      "source": "1",
      "target": "2",
      "sourceOutput": "main",
      "targetInput": "main"
    }
  ],
  "settings": {
    "saveExecutionProgress": true,
    "saveManualExecutions": true
  }
}
```

#### Get Workflow
```http
GET /api/v1/workflows/{id}
```

#### Update Workflow
```http
PUT /api/v1/workflows/{id}
```

#### Delete Workflow
```http
DELETE /api/v1/workflows/{id}
```

#### Execute Workflow
```http
POST /api/v1/workflows/{id}/execute
```

**Request Body:**
```json
{
  "input_data": {
    "any": "data to pass to workflow"
  }
}
```

#### Duplicate Workflow
```http
POST /api/v1/workflows/{id}/duplicate
```

#### Activate Workflow
```http
POST /api/v1/workflows/{id}/activate
```

#### Deactivate Workflow
```http
POST /api/v1/workflows/{id}/deactivate
```

#### Get Workflow Versions
```http
GET /api/v1/workflows/{id}/versions
```

#### Restore Workflow Version
```http
POST /api/v1/workflows/{id}/versions/{version}/restore
```

### Executions

#### List Executions
```http
GET /api/v1/executions
```

**Query Parameters:**
- `page` (int) - Page number
- `per_page` (int) - Items per page
- `workflow_id` (int) - Filter by workflow
- `status` (string) - Filter by status
- `mode` (string) - Filter by mode
- `from` (date) - Date range start
- `to` (date) - Date range end

#### Get Execution
```http
GET /api/v1/executions/{id}
```

#### Retry Execution
```http
POST /api/v1/executions/{id}/retry
```

#### Cancel Execution
```http
POST /api/v1/executions/{id}/cancel
```

#### Delete Execution
```http
DELETE /api/v1/executions/{id}
```

#### Get Execution Statistics
```http
GET /api/v1/executions/statistics
```

### Credentials

#### List Credentials
```http
GET /api/v1/credentials
```

#### Create Credential
```http
POST /api/v1/credentials
```

**Request Body:**
```json
{
  "name": "string, required",
  "type": "string, required (api_key, oauth2, basic_auth, bearer_token)",
  "data": "object, required (credential-specific data)"
}
```

#### Get Credential
```http
GET /api/v1/credentials/{id}
```

#### Update Credential
```http
PUT /api/v1/credentials/{id}
```

#### Delete Credential
```http
DELETE /api/v1/credentials/{id}
```

#### Test Credential
```http
POST /api/v1/credentials/{id}/test
```

### Nodes

#### List All Nodes
```http
GET /api/v1/nodes
```

#### List Nodes by Group
```http
GET /api/v1/nodes/by-group
```

#### Get Node Definition
```http
GET /api/v1/nodes/{type}
```

### Tags

#### List Tags
```http
GET /api/v1/tags
```

#### Create Tag
```http
POST /api/v1/tags
```

#### Get Tag
```http
GET /api/v1/tags/{id}
```

#### Update Tag
```http
PUT /api/v1/tags/{id}
```

#### Delete Tag
```http
DELETE /api/v1/tags/{id}
```

### Schedules

#### List Schedules
```http
GET /api/v1/schedules
```

#### Create Schedule
```http
POST /api/v1/schedules
```

#### Get Schedule
```http
GET /api/v1/schedules/{id}
```

#### Update Schedule
```http
PUT /api/v1/schedules/{id}
```

#### Delete Schedule
```http
DELETE /api/v1/schedules/{id}
```

### Webhooks

#### Public Webhook Endpoint
```http
POST /webhook/{webhookId}
GET /webhook/{webhookId}
PUT /webhook/{webhookId}
PATCH /webhook/{webhookId}
DELETE /webhook/{webhookId}
```

**Example Webhook Payload:**
```json
{
  "event": "user.created",
  "data": {
    "user_id": 123,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2023-01-01T12:00:00Z"
}
```

## 📤 Webhook Payloads

Webhooks accept any content type and pass the data directly to the workflow. Common examples:

### JSON Webhook
```json
{
  "event": "user.created",
  "data": {
    "user_id": 123,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "timestamp": "2023-01-01T12:00:00Z"
}
```

### Form Data Webhook
```
event=user.created&user_id=123&name=John+Doe&email=john%40example.com
```

## 📥 Response Formats

### Success Responses
```json
{
  "data": {...}
}
```

### Error Responses
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for field"]
  }
}
```

### Pagination Responses
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
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

## 📊 HTTP Status Codes

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

## 🚦 Rate Limiting

API endpoints are rate-limited:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated requests
- 1000 requests per hour for webhook endpoints

## 🛠️ SDK Examples

### JavaScript (Fetch API)
```javascript
// Get workflows
const response = await fetch('/api/v1/workflows', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

const workflows = await response.json();

// Execute workflow
await fetch('/api/v1/workflows/1/execute', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    input_data: { message: 'Hello World' }
  })
});
```

### Python (Requests)
```python
import requests

headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
}

# Get workflows
response = requests.get('/api/v1/workflows', headers=headers)
workflows = response.json()

# Execute workflow
response = requests.post('/api/v1/workflows/1/execute', 
                        headers=headers,
                        json={'input_data': {'message': 'Hello World'}})
```

## 🎯 Best Practices

1. **Always use HTTPS** in production
2. **Handle rate limiting** gracefully in your applications
3. **Cache node definitions** when possible
4. **Use webhooks** for real-time integrations
5. **Monitor execution statistics** for performance insights
6. **Test credentials** before using in production workflows
7. **Use workflow versions** for change management
8. **Apply tags** for better organization

This documentation provides a complete reference for integrating with the n8n Clone API. All endpoints follow REST conventions and return standardized JSON responses.