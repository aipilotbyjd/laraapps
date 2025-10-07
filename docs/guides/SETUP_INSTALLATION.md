# n8n Clone - Setup and Installation Guide

This guide will help you set up the n8n Clone workflow automation platform on your local machine or server.

## 📋 Prerequisites

Before installing, ensure your system meets the following requirements:

### Server Requirements
- **Operating System**: Linux, macOS, or Windows
- **PHP**: 8.2 or higher
- **Composer**: Latest version
- **Database**: SQLite (default), MySQL 8.0+ or PostgreSQL 12+
- **Web Server**: Apache, Nginx, or built-in PHP server
- **Node.js**: 18+ (for frontend assets if applicable)

### PHP Extensions
```bash
# Required extensions
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
```

## 🚀 Installation Steps

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd n8n-clone
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies (if applicable)

```bash
npm install
# or
yarn install
```

### 4. Configure Environment

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### For SQLite (default)
```bash
# Create the database file
touch database/database.sqlite
```

#### For MySQL/PostgreSQL
Update your `.env` file with database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Run Database Migrations

```bash
php artisan migrate --force
```

### 8. Install and Configure Passport

```bash
# Install Passport
php artisan passport:install

# Update the .env file with the generated keys
PASSPORT_CLIENT_ID=your_client_id
PASSPORT_CLIENT_SECRET=your_client_secret
```

### 9. Queue Configuration

The application uses queues for workflow execution. You can use database queues or Redis.

#### For Database Queues (default):
```bash
# Queue table is created during migration
php artisan queue:table
php artisan migrate
```

#### For Redis:
Update your `.env` file:
```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 10. Storage Link (for file uploads)

```bash
php artisan storage:link
```

## ⚙️ Environment Configuration

Here's a complete `.env` file setup for the n8n clone application:

```env
APP_NAME="n8n Clone"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

DB_CONNECTION=sqlite
# For MySQL:
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=n8n_clone
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_CONNECTION=log
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Redis (if using Redis for queue/cache)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail settings
MAIL_MAILER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Passport OAuth
PASSPORT_CLIENT_ID=
PASSPORT_CLIENT_SECRET=
PASSPORT_PRIVATE_KEY=
PASSPORT_PUBLIC_KEY=

# Workflow-specific settings
WORKFLOW_QUEUE_CONNECTION=database
WORKFLOW_QUEUE_NAME=workflows
WORKFLOW_EXECUTION_TIMEOUT=300
WORKFLOW_MAX_RETRIES=3
WORKFLOW_RETENTION_DAYS=30
WORKFLOW_CACHE_NODES=true
WORKFLOW_ALLOW_CODE=false
WORKFLOW_LOG_CHANNEL=stack
WORKFLOW_WEBHOOK_PREFIX=webhook
WORKFLOW_WEBHOOK_RATE_LIMIT=60
```

## 🏃‍♂️ Running the Application

### Development Server

```bash
# Start the development server
php artisan serve

# In another terminal, start the queue worker
php artisan queue:work --queue=workflows

# For development with auto-reload
php artisan serve --host=0.0.0.0 --port=8000
```

### Production Deployment

1. **Optimize for Production**
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear caches if needed
php artisan cache:clear
```

2. **Set Proper File Permissions**
```bash
# Make sure the storage and bootstrap/cache directories are writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

3. **Setup Queue Workers (Production)**
```bash
# Use a process monitor like Supervisor to keep queue workers running

# Example supervisor configuration file: /etc/supervisor/conf.d/n8n-clone.conf
[program:n8n-clone-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --queue=workflows --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
```

### 4. Setup Scheduler (Production)

Add this to your crontab (`crontab -e`):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## 🔧 Additional Configuration

### Workflow Execution Timeout

You can configure how long workflow executions can run before timing out:
```env
WORKFLOW_EXECUTION_TIMEOUT=600  # 10 minutes in seconds
```

### Credential Encryption

Credentials are encrypted by default. You can verify encryption is working:
```bash
php artisan tinker
>>> Crypt::encrypt('test')
```

### Queue Workers for High Traffic

For production environments with high load, consider running multiple queue workers:
```bash
# Start multiple workers
php artisan queue:work --queue=workflows --timeout=300 --tries=3 &
php artisan queue:work --queue=workflows --timeout=300 --tries=3 &
php artisan queue:work --queue=workflows --timeout=300 --tries=3 &
```

## 🔄 Updates

To update the application:

```bash
# Pull latest changes
git pull origin main

# Update PHP dependencies
composer install --no-dev

# Update database if needed
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 💡 Troubleshooting

### Common Issues:

1. **Class not found errors**: Run `composer dump-autoload`
2. **Queue not processing**: Make sure queue workers are running
3. **Database connection errors**: Check your `.env` database settings
4. **Permission errors**: Ensure `storage`, `bootstrap/cache` directories are writable
5. **Passport not working**: Run `php artisan passport:install` again

### Performance Optimization:

1. **Cache Configuration**: Run `php artisan config:cache` in production
2. **Queue Processing**: Use Redis for better queue performance
3. **Database Indexes**: Ensure proper database indexing
4. **Monitor Queue Workers**: Keep queue workers running and monitored

## 🚀 Next Steps

After successful installation:

1. Create your first workflow using the API
2. Set up authentication with Passport
3. Start queue workers for workflow execution
4. Test webhook endpoints
5. Monitor execution statistics

Your n8n Clone installation is now ready for use! 🎉