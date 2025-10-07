# n8n Clone - Advanced Workflow Automation Platform

A comprehensive, production-ready n8n clone built with Laravel 11/12, featuring workflow automation, scheduled tasks, webhook endpoints, and credential management.

## 📚 Documentation

Complete documentation is available in the [docs](docs/) folder:

- [Documentation Index](docs/README.md) - Start here for complete documentation
- [API Documentation](docs/api/API_DOCUMENTATION.md) - Complete API reference
- [Installation Guide](docs/guides/SETUP_INSTALLATION.md) - Setup and installation
- [Environment Configuration](docs/configuration/ENVIRONMENT_CONFIGURATION.md) - Environment setup
- [Workflow Node Documentation](docs/nodes/WORKFLOW_NODES.md) - Node system reference
- [Postman Collection](docs/tools/n8n-clone-postman-collection.json) - Complete API collection for Postman
- [Queue Worker Script](docs/tools/start-queue-workers.sh) - Shell script to start queue workers

## 🚀 Features

- **Workflow Management**: Create, edit, execute, and monitor complex workflows
- **Node System**: Extensible node architecture with triggers, actions, logic, and transform nodes
- **Execution Engine**: Asynchronous workflow execution with queue support
- **API Authentication**: Laravel Passport OAuth2 authentication
- **Webhook Endpoints**: Public webhook endpoints for external integrations
- **Credential Management**: Secure credential storage with encryption
- **Version Control**: Workflow versioning and rollback capabilities
- **Scheduling**: Cron-based workflow scheduling
- **Wait Nodes**: Time-based and event-based workflow pausing
- **Expression Engine**: Support for expressions like `{{$json.field}}`

## 📦 Technology Stack

- **Backend**: Laravel 12
- **Authentication**: Laravel Passport (OAuth2)
- **Queue System**: Database/Redis queue
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **API**: RESTful API with JSON responses

## 🏗️ Architecture

```
app/
├── Models/                 # Eloquent models
│   ├── Workflow.php        # Core workflow model
│   ├── Execution.php       # Execution tracking
│   ├── Credential.php      # Credential management
│   └── ...
├── Services/              # Business logic services
│   ├── Execution/         # Execution services
│   ├── Node/              # Node registry
│   └── ...
├── Nodes/                 # Node implementation
│   ├── Actions/           # Action nodes (HTTP, Email, etc.)
│   ├── Triggers/          # Trigger nodes (Webhook, Schedule, etc.)
│   ├── Logic/             # Logic nodes (If, Switch, etc.)
│   └── ...
├── Http/                  # Controllers, Resources, Requests
├── Jobs/                  # Queue jobs for workflow execution
├── Events/                # Event system
└── Console/               # Artisan commands
```

## 🏷️ Key Endpoints

- `POST /api/v1/workflows` - Create workflows
- `GET /api/v1/workflows` - List workflows
- `POST /api/v1/workflows/{id}/execute` - Execute workflows
- `GET /api/v1/executions` - View execution history
- `POST /webhook/{id}` - Public webhook endpoints

## 🔐 Authentication

The API uses Laravel Passport for OAuth2 authentication. All endpoints except webhook endpoints require authentication using a Bearer token.

```bash
Authorization: Bearer {your-access-token}
Accept: application/json
Content-Type: application/json
```

## 🛠️ Tools & Utilities

- **Postman Collection**: Complete API collection for easy testing
- **Queue Worker Script**: Shell script to start workflow queue workers

## 📄 License

MIT License - Feel free to use and modify for your projects.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request