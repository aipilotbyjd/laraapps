# n8n Clone - Quick Start Guide

This guide will help you get up and running with the n8n Clone workflow automation platform quickly.

## 🚀 Quick Installation

### Prerequisites
- PHP 8.2+
- Composer
- SQLite (or MySQL/PostgreSQL)

### 1. Clone and Install
```bash
# Clone the repository
git clone <your-repository-url>
cd n8n-clone

# Install PHP dependencies
composer install
```

### 2. Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup
```bash
# For SQLite (default)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 4. Setup Authentication
```bash
# Install Laravel Passport
php artisan passport:install
```

### 5. Start the Application
```bash
# Start development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work --queue=workflows
```

## 🎯 First Workflow

### 1. Create Authentication Token
```bash
# Use Postman or curl to create a personal access token
# POST /api/tokens with your credentials
```

### 2. Create Your First Workflow
```json
{
  "name": "Hello World Workflow",
  "active": true,
  "nodes": [
    {
      "id": "1",
      "name": "Webhook Trigger",
      "type": "webhook_trigger",
      "parameters": {
        "method": "POST"
      }
    },
    {
      "id": "2", 
      "name": "HTTP Request",
      "type": "http_request",
      "parameters": {
        "method": "GET",
        "url": "https://httpbin.org/get"
      }
    }
  ],
  "connections": [
    {
      "source": "1",
      "target": "2"
    }
  ]
}
```

### 3. Execute the Workflow
```bash
# POST to /api/v1/workflows/{id}/execute with your token
curl -X POST http://localhost:8000/api/v1/workflows/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"input_data": {}}'
```

## 🏃‍♂️ Running in Production

### Queue Workers
```bash
# Start queue workers for workflow processing
php artisan queue:work --queue=workflows --timeout=300 --tries=3 &
```

### Scheduler
Add to your crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testing the Installation

Verify your installation with these endpoints:

```bash
# Check system health
curl http://localhost:8000/api/health

# List workflows (should be empty initially)
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/v1/workflows
```

## 🚨 Troubleshooting

### Common Issues:
1. **Queue not processing**: Make sure queue workers are running
2. **Database errors**: Verify your database configuration
3. **Authentication errors**: Check Passport installation
4. **Migration errors**: Ensure database permissions are correct

### Quick Fixes:
```bash
# Recreate application key
php artisan key:generate

# Republish Passport assets
php artisan passport:install --force

# Clear caches
php artisan cache:clear
php artisan config:clear
```

Your n8n Clone installation is now ready! 🎉