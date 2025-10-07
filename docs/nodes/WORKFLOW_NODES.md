# n8n Clone - Workflow Node Documentation

This document provides comprehensive information about all available workflow nodes in the n8n Clone platform.

## 🏗️ Node System Overview

The n8n Clone uses an extensible node system that allows you to build complex workflows by connecting different node types. Each node performs a specific function and can be connected to other nodes to create sophisticated automation.

### Node Categories
- **Triggers**: Start workflow execution based on events or schedules
- **Actions**: Perform specific operations (HTTP requests, database operations, etc.)
- **Logic**: Control workflow flow (conditionals, loops, etc.)
- **Transform**: Manipulate data between nodes

## 🎣 Trigger Nodes

### Webhook Trigger
Starts a workflow when it receives an HTTP request at a public endpoint.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Webhook Trigger",
  "type": "webhook_trigger",
  "parameters": {
    "method": "POST",          // HTTP method: GET, POST, PUT, PATCH, DELETE
    "response_mode": "last_node" // Response mode: immediate, last_node, wait_for_webhook
  }
}
```

**Available Parameters:**
- `method`: HTTP method to accept (default: POST)
- `response_mode`: How to respond to webhook calls

**Response Modes:**
- `immediate`: Respond immediately
- `last_node`: Respond with output from the last node
- `wait_for_webhook`: Wait for another webhook before responding

### Schedule Trigger
Starts a workflow based on a cron schedule.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Schedule Trigger",
  "type": "schedule_trigger",
  "parameters": {
    "cron_expression": "0 * * * *"  // Every hour
  }
}
```

**Available Parameters:**
- `cron_expression`: Cron expression for scheduling

### Manual Trigger
Starts a workflow manually through the API.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Manual Trigger",
  "type": "manual_trigger",
  "parameters": {}
}
```

## ⚡ Action Nodes

### HTTP Request
Makes HTTP requests to external services.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "HTTP Request",
  "type": "http_request",
  "parameters": {
    "method": "GET",
    "url": "https://api.example.com/endpoint",
    "headers": [
      {
        "name": "Content-Type",
        "value": "application/json"
      }
    ],
    "query_parameters": [
      {
        "name": "param1",
        "value": "value1"
      }
    ],
    "body": {
      "key": "value"
    },
    "authentication": "none",    // none, basic_auth, bearer_token, api_key
    "timeout": 30                // Request timeout in seconds
  },
  "credentials": 1               // Reference to stored credentials
}
```

**Available Parameters:**
- `method`: HTTP method (GET, POST, PUT, PATCH, DELETE)
- `url`: Target URL (supports expressions like `{{$json.url}}`)
- `headers`: Array of header objects
- `query_parameters`: Array of query parameter objects
- `body`: Request body (for POST/PUT/PATCH)
- `authentication`: Authentication type
- `timeout`: Request timeout in seconds

**Authentication Options:**
- `none`: No authentication
- `basic_auth`: Basic authentication
- `bearer_token`: Bearer token
- `api_key`: API key in header

### Wait Node
Pauses workflow execution for a specified duration or until a condition is met.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Wait",
  "type": "wait",
  "parameters": {
    "wait_type": "time",         // time, webhook, condition
    "amount": 1,                 // For time-based waits
    "unit": "hours"              // seconds, minutes, hours, days (for time-based)
  }
}
```

**Available Parameters:**
- `wait_type`: Type of wait (time, webhook, condition)
- `amount`: Amount of time to wait (for time-based waits)
- `unit`: Time unit (seconds, minutes, hours, days)

### Email Action
Sends email messages.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Email",
  "type": "email",
  "parameters": {
    "to": "recipient@example.com",
    "subject": "Email Subject",
    "body": "Email body content",
    "cc": ["cc@example.com"],
    "bcc": ["bcc@example.com"],
    "attachments": []
  }
}
```

**Available Parameters:**
- `to`: Recipient email address
- `subject`: Email subject
- `body`: Email body content
- `cc`: CC recipients
- `bcc`: BCC recipients
- `attachments`: Array of file attachments

## 🧠 Logic Nodes

### If Node
Branches workflow execution based on conditions.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "If",
  "type": "if",
  "parameters": {
    "conditions": [
      {
        "value1": "{{$json.amount}}",
        "operation": "greater_than",
        "value2": "100"
      }
    ],
    "combine_operation": "and"    // and, or
  }
}
```

**Available Parameters:**
- `conditions`: Array of condition objects
- `combine_operation`: How to combine multiple conditions (and, or)

**Available Operations:**
- `equals`: Value equality
- `not_equals`: Value inequality
- `contains`: String contains
- `not_contains`: String does not contain
- `starts_with`: String starts with
- `ends_with`: String ends with
- `greater_than`: Greater than comparison
- `less_than`: Less than comparison
- `greater_or_equal`: Greater than or equal
- `less_or_equal`: Less than or equal
- `is_empty`: Value is empty
- `is_not_empty`: Value is not empty
- `regex_match`: Regular expression match

### Switch Node
Routes workflow execution based on matching values.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Switch",
  "type": "switch",
  "parameters": {
    "rules": [
      {
        "conditions": [
          {
            "value1": "{{$json.status}}",
            "operation": "equals",
            "value2": "active"
          }
        ],
        "outputIndex": 0
      }
    ]
  }
}
```

### Merge Node
Combines data from multiple input branches into a single output.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Merge",
  "type": "merge",
  "parameters": {
    "mode": "append"           // append, combine
  }
}
```

## 🔄 Transform Nodes

### Code Node
Executes custom JavaScript code to transform data.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Code",
  "type": "code",
  "parameters": {
    "mode": "run_once",        // run_once, each_item
    "code": "// Your JavaScript code here\nreturn items;"
  }
}
```

**Available Parameters:**
- `mode`: Execution mode (run_once, each_item)
- `code`: JavaScript code to execute

**Code Context:**
- `items`: Input data array
- `item`: Individual item (in each_item mode)

### Set Node
Sets or modifies properties in the workflow data.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "Set",
  "type": "set",
  "parameters": {
    "values": [
      {
        "name": "processedAt",
        "value": "{{$now}}"
      },
      {
        "name": "status",
        "value": "completed"
      }
    ]
  }
}
```

### DateTime Node
Performs date and time operations.

**Configuration:**
```json
{
  "id": "node_id",
  "name": "DateTime",
  "type": "datetime",
  "parameters": {
    "operation": "add",         // add, subtract, format, compare
    "input": "{{$json.date}}",
    "unit": "days",
    "amount": 7
  }
}
```

## 📊 Node Connection Pattern

Nodes are connected using the following pattern:
```json
{
  "connections": [
    {
      "source": "node1_id",
      "target": "node2_id",
      "sourceOutput": "main",     // Output identifier
      "targetInput": "main"       // Input identifier
    }
  ]
}
```

## 🧩 Expression Support

All node parameters support expressions that can reference:
- Workflow input data: `{{$json.field_name}}`
- Previous node data: `{{$node["NodeName"].json.field_name}}`
- Built-in functions: `{{$now}}`, `{{$today}}`, `{{$uuid}}`

## 🔐 Credential Support

Many nodes can use stored credentials for authentication:

```json
{
  "id": "node_id",
  "name": "HTTP Request",
  "type": "http_request",
  "parameters": {
    // ... other parameters
  },
  "credentials": 123             // Reference to credential ID
}
```

## 🔧 Node Development

To create custom nodes, extend the appropriate base class:

```php
namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;

class CustomAction extends BaseNode
{
    protected string $name = 'Custom Action';
    protected string $type = 'custom_action';
    protected string $group = 'action';

    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        // Your custom logic here
        return ['result' => 'success'];
    }

    protected function getDescription(): string
    {
        return 'A custom action node';
    }

    protected function getProperties(): array
    {
        return [
            // Define input properties
        ];
    }
}
```

## 📝 Best Practices

1. **Error Handling**: Always implement proper error handling in custom nodes
2. **Validation**: Validate input parameters before processing
3. **Performance**: Be mindful of execution time and resource usage
4. **Security**: Validate and sanitize all input data
5. **Documentation**: Include clear descriptions and examples

## 🚨 Troubleshooting

### Common Node Issues:
- **Timeout errors**: Increase timeout values in node parameters
- **Authentication failures**: Verify credential settings
- **Expression errors**: Check expression syntax
- **Connection issues**: Verify network connectivity for HTTP requests

### Performance Tips:
- Use caching for expensive operations
- Optimize database queries in custom nodes
- Implement proper batching for large datasets
- Use asynchronous processing where appropriate