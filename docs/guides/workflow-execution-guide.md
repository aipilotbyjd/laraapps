# 🚀 n8n Clone - Complete Workflow Execution Guide

## 📋 Overview

This guide provides a comprehensive walkthrough of how to properly run workflows in your n8n clone application, covering the complete flow from authentication to execution and monitoring.

## 🔐 Authentication Flow

### 1. Register a New User
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "subscription_level": "free",
    "created_at": "2025-10-08T17:00:00.000000Z",
    "updated_at": "2025-10-08T17:00:00.000000Z"
  },
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_at": "2025-10-08T18:00:00.000000Z"
}
```

### 2. Login (Alternative to Registration)
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 3. Store Access Token
Save the `access_token` for subsequent API requests:
```bash
ACCESS_TOKEN="your-access-token-here"
```

## 🏗️ Workflow Creation Flow

### 1. Create a Simple HTTP Request Workflow
```bash
curl -X POST http://localhost:8000/api/v1/workflows \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sample HTTP Request Workflow",
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
  }'
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Sample HTTP Request Workflow",
    "description": "A workflow that makes HTTP requests",
    "active": true,
    "nodes": [...],
    "connections": [...],
    "settings": {...},
    "user_id": 1,
    "created_at": "2025-10-08T17:00:00.000000Z",
    "updated_at": "2025-10-08T17:00:00.000000Z"
  }
}
```

### 2. Store Workflow ID
```bash
WORKFLOW_ID=1
```

## ▶️ Workflow Execution Flow

### 1. Execute Workflow Manually
```bash
curl -X POST http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/execute \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "input_data": {
      "message": "Hello from manual execution!",
      "timestamp": "2025-10-08T17:00:00Z"
    }
  }'
```

**Response:**
```json
{
  "execution_id": 1,
  "status": "running"
}
```

### 2. Store Execution ID
```bash
EXECUTION_ID=1
```

## 🔍 Monitoring Execution Flow

### 1. Check Execution Status
```bash
curl -X GET http://localhost:8000/api/v1/executions/$EXECUTION_ID \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "workflow_id": 1,
    "status": "success",
    "mode": "manual",
    "input_data": {
      "message": "Hello from manual execution!",
      "timestamp": "2025-10-08T17:00:00Z"
    },
    "output_data": {
      "status": 200,
      "body": {
        "args": {},
        "headers": {
          "Accept": "*/*",
          "Host": "httpbin.org"
        }
      }
    },
    "started_at": "2025-10-08T17:00:00.000000Z",
    "finished_at": "2025-10-08T17:00:01.000000Z",
    "duration_ms": 1000
  }
}
```

### 2. View Execution Details
```bash
curl -X GET http://localhost:8000/api/v1/executions/$EXECUTION_ID \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 3. Get Execution Statistics
```bash
curl -X GET http://localhost:8000/api/v1/executions/statistics \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## 🌐 Webhook Execution Flow

### 1. Get Webhook URL
After creating a workflow with a webhook trigger, get the webhook URL:
```bash
curl -X GET http://localhost:8000/api/v1/workflows/$WORKFLOW_ID \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 2. Trigger Workflow via Webhook
```bash
curl -X POST http://localhost:8000/api/webhook/YOUR-WEBHOOK-ID \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_data": "This will trigger the workflow",
    "timestamp": "2025-10-08T17:00:00Z",
    "payload": {
      "user_id": 123,
      "action": "created"
    }
  }'
```

## 📊 Complete Workflow Lifecycle

### 1. List All Workflows
```bash
curl -X GET "http://localhost:8000/api/v1/workflows?page=1&per_page=15" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 2. Update Workflow
```bash
curl -X PUT http://localhost:8000/api/v1/workflows/$WORKFLOW_ID \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated HTTP Request Workflow",
    "description": "An updated workflow with enhanced functionality",
    "active": true,
    "nodes": [...],
    "connections": [...],
    "settings": {...}
  }'
```

### 3. Duplicate Workflow
```bash
curl -X POST http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/duplicate \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 4. Activate/Deactivate Workflow
```bash
# Activate
curl -X POST http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/activate \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"

# Deactivate
curl -X POST http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/deactivate \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 5. Delete Workflow
```bash
curl -X DELETE http://localhost:8000/api/v1/workflows/$WORKFLOW_ID \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## 🛠️ Advanced Features

### 1. Workflow Versioning
```bash
# Get workflow versions
curl -X GET http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/versions \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"

# Restore specific version
curl -X POST http://localhost:8000/api/v1/workflows/$WORKFLOW_ID/versions/1/restore \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 2. Tag Management
```bash
# Create tag
curl -X POST http://localhost:8000/api/v1/tags \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Production",
    "color": "#ff0000"
  }'

# Assign tag to workflow (requires updating workflow with tag)
```

### 3. Credential Management
```bash
# Create credential
curl -X POST http://localhost:8000/api/v1/credentials \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "API Key for Service",
    "type": "api_key",
    "data": {
      "api_key": "your-api-key-here",
      "header_name": "X-API-Key"
    }
  }'

# Test credential
curl -X POST http://localhost:8000/api/v1/credentials/1/test \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## 📈 Monitoring and Debugging

### 1. View Execution History
```bash
curl -X GET "http://localhost:8000/api/v1/executions?workflow_id=$WORKFLOW_ID" \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 2. Retry Failed Execution
```bash
curl -X POST http://localhost:8000/api/v1/executions/$EXECUTION_ID/retry \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### 3. Cancel Running Execution
```bash
curl -X POST http://localhost:8000/api/v1/executions/$EXECUTION_ID/cancel \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Accept: application/json"
```

## 🔒 Subscription-Based Permissions

### Available Subscription Levels:
1. **Free** - Basic workflow automation (5 workflows, 100 executions/month)
2. **Pro** - Advanced features (50 workflows, 1000 executions/month)
3. **Enterprise** - Unlimited everything + team collaboration

### Permission Checks:
- Users can only access workflows they own
- Subscription limits enforce workflow and execution quotas
- Advanced features are locked behind appropriate subscription levels

## 🎯 Best Practices

### 1. Error Handling
Always check HTTP status codes:
- `200` - Success
- `201` - Created
- `401` - Unauthorized (check token)
- `403` - Forbidden (check permissions)
- `404` - Not found
- `422` - Validation errors
- `500` - Server errors

### 2. Workflow Design
- Use descriptive names for nodes
- Add proper error handling in workflows
- Test workflows with sample data before production use
- Use version control for important workflow changes

### 3. Security
- Never expose API tokens in client-side code
- Rotate credentials regularly
- Use HTTPS in production
- Validate all input data

### 4. Performance
- Monitor execution statistics
- Optimize workflows with too many nodes
- Use appropriate timeouts for HTTP requests
- Cache frequently accessed data

## 🚨 Troubleshooting

### Common Issues:

1. **Authentication Errors**
   - Ensure access token is valid and not expired
   - Check that `Authorization: Bearer {token}` header is included

2. **Permission Denied**
   - Verify user owns the workflow
   - Check subscription level limits
   - Ensure user has appropriate role/permissions

3. **Workflow Execution Failures**
   - Check execution logs for error details
   - Verify all required nodes are properly configured
   - Ensure credentials are valid if used

4. **Rate Limiting**
   - Implement exponential backoff for repeated requests
   - Monitor API usage against subscription limits

## 📞 Support Channels

For issues not covered in this guide:
1. Check application logs in `storage/logs/`
2. Review API documentation in `docs/api/API_DOCUMENTATION.md`
3. Use Postman collection in `docs/tools/n8n-clone-postman-collection.json`
4. Contact system administrator for permission-related issues

---

🎉 **Congratulations!** You now have a complete understanding of how to run workflows in your n8n clone application. The system provides robust workflow automation capabilities with proper authentication, authorization, and subscription-based feature access.