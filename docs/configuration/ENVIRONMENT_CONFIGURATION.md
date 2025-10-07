# n8n Clone - Environment Configuration Guide

This guide explains all environment variables and configuration options for the n8n Clone workflow automation platform.

## 🏗️ Application Configuration

### Basic Application Settings
```env
APP_NAME="n8n Clone"                    # Application name
APP_ENV=local                          # Environment: local, staging, production
APP_KEY=                               # Application key (generate with php artisan key:generate)
APP_DEBUG=true                         # Enable/disable debug mode
APP_URL=http://localhost:8000         # Application URL
APP_LOCALE=en                          # Default locale
APP_FALLBACK_LOCALE=en                 # Fallback locale
```

### Maintenance Mode
```env
APP_MAINTENANCE_DRIVER=file            # Maintenance mode driver
APP_MAINTENANCE_STORE=database         # Storage for maintenance mode
```

## 🗄️ Database Configuration

### Database Connection
```env
DB_CONNECTION=sqlite                  # Database driver: sqlite, mysql, pgsql, sqlsrv
# For MySQL
DB_HOST=127.0.0.1                    # Database host
DB_PORT=3306                         # Database port
DB_DATABASE=laravel                  # Database name
DB_USERNAME=root                     # Database username
DB_PASSWORD=                         # Database password

# For PostgreSQL
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=postgres
DB_PASSWORD=
```

### Database Queue Configuration
```env
DB_QUEUE_CONNECTION=                  # Queue database connection
DB_QUEUE_TABLE=jobs                   # Queue table name
DB_QUEUE=                            # Queue name
DB_QUEUE_RETRY_AFTER=90              # Queue retry time in seconds
```

## 🚦 Session Configuration

```env
SESSION_DRIVER=database              # Session driver: file, cookie, database, redis, memcached
SESSION_LIFETIME=120                 # Session lifetime in minutes
SESSION_ENCRYPT=false                # Encrypt session data
SESSION_PATH=/                       # Session cookie path
SESSION_DOMAIN=                      # Session cookie domain
SESSION_SECURE=false                 # Use secure session cookies
SESSION_HTTPONLY=true                # HTTP only session cookies
SESSION_SAME_SITE=lax                # Same site session cookies
```

## 🌐 Cache Configuration

```env
CACHE_STORE=database                 # Cache store: database, redis, memcached, file
CACHE_DEFAULT=database               # Default cache store
CACHE_PREFIX=                        # Cache key prefix
```

## 📮 Queue Configuration

### Queue Connection
```env
QUEUE_CONNECTION=database            # Queue connection: sync, database, redis, beanstalkd, sqs
```

### Redis Queue Configuration (if using Redis)
```env
REDIS_QUEUE_CONNECTION=default       # Redis queue connection
REDIS_QUEUE=default                  # Redis queue name
REDIS_QUEUE_RETRY_AFTER=90           # Redis queue retry time in seconds
REDIS_CLIENT=phpredis                # Redis client: phpredis, predis
REDIS_HOST=127.0.0.1                 # Redis host
REDIS_PASSWORD=null                  # Redis password
REDIS_PORT=6379                      # Redis port
```

## 📡 Broadcast Configuration

```env
BROADCAST_CONNECTION=log             # Broadcast connection: pusher, redis, log, null
```

## 📧 Mail Configuration

```env
MAIL_MAILER=log                      # Mail driver: smtp, mailgun, ses, postmark, log, array
MAIL_HOST=smtp.mailtrap.io          # Mail server host
MAIL_PORT=2525                       # Mail server port
MAIL_USERNAME=                       # Mail server username
MAIL_PASSWORD=                       # Mail server password
MAIL_ENCRYPTION=tls                  # Mail encryption
MAIL_FROM_ADDRESS="hello@example.com" # From email address
MAIL_FROM_NAME="${APP_NAME}"         # From name
```

## 🔐 Passport OAuth Configuration

```env
PASSPORT_CLIENT_ID=                  # Passport client ID
PASSPORT_CLIENT_SECRET=              # Passport client secret
PASSPORT_PRIVATE_KEY=                # Passport private key
PASSPORT_PUBLIC_KEY=                 # Passport public key
PASSPORT_CONNECTION=                 # Passport database connection
PASSPORT_GUARD=api                   # Passport authentication guard
```

## ⚙️ Workflow Configuration

### Workflow Execution Settings
```env
WORKFLOW_EXECUTION_TIMEOUT=300       # Workflow execution timeout in seconds
WORKFLOW_MAX_RETRIES=3               # Maximum retry attempts for failed executions
WORKFLOW_RETENTION_DAYS=30           # Days to retain execution data
WORKFLOW_LOG_CHANNEL=stack           # Logging channel for workflow logs
WORKFLOW_LOG_LEVEL=info              # Logging level for workflow logs
```

### Workflow Queue Configuration
```env
WORKFLOW_QUEUE_CONNECTION=database   # Workflow queue connection
WORKFLOW_QUEUE_NAME=workflows        # Workflow queue name
```

### Workflow Node Settings
```env
WORKFLOW_CACHE_NODES=true            # Enable/disable node caching
WORKFLOW_ALLOW_CODE=false            # Allow JavaScript code execution in Code nodes
WORKFLOW_CACHE_TTL=3600              # Node cache TTL in seconds
```

### Workflow Webhook Settings
```env
WORKFLOW_WEBHOOK_PREFIX=webhook      # Webhook URL prefix
WORKFLOW_WEBHOOK_RATE_LIMIT=60       # Webhook rate limit (requests per minute)
```

### Workflow Security Settings
```env
WORKFLOW_ENCRYPT_CREDENTIALS=true    # Encrypt stored credentials
```

## 🔍 Logging Configuration

```env
LOG_CHANNEL=stack                    # Log channel: stack, single, daily, errorlog, syslog, null
LOG_STACK=single                     # Stack of log channels
LOG_DEPRECATIONS_CHANNEL=null        # Deprecations log channel
LOG_LEVEL=debug                      # Log level: debug, info, notice, warning, error, critical, alert, emergency
```

## 💾 Filesystem Configuration

```env
FILESYSTEM_DISK=local                # Default filesystem disk
```

## 🏞️ AWS Configuration (if using AWS services)

```env
AWS_ACCESS_KEY_ID=                   # AWS Access Key ID
AWS_SECRET_ACCESS_KEY=               # AWS Secret Access Key
AWS_DEFAULT_REGION=us-east-1         # AWS Default Region
AWS_BUCKET=                          # AWS Bucket name
AWS_USE_PATH_STYLE_ENDPOINT=false    # Use path style endpoint
```

## 📊 Custom Application Environment Variables

### Advanced Workflow Settings
```env
WORKFLOW_MAX_CONCURRENT_EXECUTIONS=10 # Maximum concurrent workflow executions
WORKFLOW_NODE_TIMEOUT=300            # Node execution timeout in seconds
WORKFLOW_MAX_PAYLOAD_SIZE=10485760   # Maximum payload size (10MB)
WORKFLOW_MAX_WORKFLOW_NODES=100      # Maximum nodes per workflow
WORKFLOW_MAX_EXECUTION_TIME=3600     # Maximum execution time in seconds
```

### Performance Settings
```env
WORKFLOW_WORKER_MEMORY_LIMIT=512     # Queue worker memory limit in MB
WORKFLOW_WORKER_TIMEOUT=300          # Queue worker timeout in seconds
WORKFLOW_BATCH_SIZE=50               # Batch processing size
```

### Security Settings
```env
WORKFLOW_API_RATE_LIMIT=60           # API rate limit per minute
WORKFLOW_WEBHOOK_TIMEOUT=30          # Webhook request timeout in seconds
WORKFLOW_DISABLE_UNSAFE_OPERATIONS=false # Disable potentially unsafe operations
```

## 🚀 Production Environment Variables

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password

# Cache
CACHE_STORE=redis

# Queue
QUEUE_CONNECTION=redis

# Security
APP_KEY=your_generated_app_key

# Workflow
WORKFLOW_EXECUTION_TIMEOUT=600
WORKFLOW_MAX_RETRIES=5
WORKFLOW_RETENTION_DAYS=90
WORKFLOW_QUEUE_CONNECTION=redis
WORKFLOW_ALLOW_CODE=false

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info
```

## 🧪 Development Environment Variables

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite

# Queue
QUEUE_CONNECTION=database

# Caching
CACHE_STORE=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Workflow
WORKFLOW_EXECUTION_TIMEOUT=300
WORKFLOW_MAX_RETRIES=3
WORKFLOW_RETENTION_DAYS=30
WORKFLOW_QUEUE_CONNECTION=database
WORKFLOW_ALLOW_CODE=true
```

## 🔁 Staging Environment Variables

```env
# Application
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=staging-db-host

# Queue
QUEUE_CONNECTION=redis

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=info

# Workflow
WORKFLOW_EXECUTION_TIMEOUT=450
WORKFLOW_MAX_RETRIES=3
WORKFLOW_RETENTION_DAYS=14
WORKFLOW_QUEUE_CONNECTION=redis
WORKFLOW_ALLOW_CODE=true
```

## 📋 Environment Configuration Best Practices

### 1. Security
- Never commit sensitive information to version control
- Use strong, unique passwords for database and other services
- Set `APP_DEBUG=false` in production
- Generate a new `APP_KEY` for each environment

### 2. Performance
- Use Redis for caching and queue in production environments
- Adjust timeout values based on your infrastructure
- Set appropriate memory limits for queue workers

### 3. Monitoring
- Configure proper logging channels for different environments
- Set up log rotation for production environments
- Monitor queue worker performance

### 4. Workflow Settings
- Be conservative with `WORKFLOW_ALLOW_CODE` in production
- Adjust timeout values based on typical workflow execution time
- Set retention days based on your storage capabilities and requirements

## 🛠️ Common Configuration Scenarios

### High Availability Setup
```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
BROADCAST_CONNECTION=redis
```

### Resource Constrained Environment
```env
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=file
WORKFLOW_EXECUTION_TIMEOUT=120
WORKFLOW_MAX_RETRIES=2
```

### Development with External Services
```env
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
AWS_ACCESS_KEY_ID=your-dev-key
AWS_SECRET_ACCESS_KEY=your-dev-secret
```

Remember to always restart your application services after changing environment variables that affect runtime behavior.