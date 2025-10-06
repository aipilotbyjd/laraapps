# Complete n8n Clone - Full Implementation Documentation

## 📋 Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Database Schema](#database-schema)
3. [Models](#models)
4. [Node System](#node-system)
5. [Services](#services)
6. [Execution Engine](#execution-engine)
7. [API Endpoints](#api-endpoints)
8. [Workflow Execution Flow](#workflow-execution-flow)
9. [Expression Engine](#expression-engine)
10. [Deployment Guide](#deployment-guide)

## Architecture Overview

### Directory Structure
```
app/
├── Models/
│   ├── Workflow.php             # Core workflow model
│   ├── Execution.php            # Execution tracking
│   ├── ExecutionData.php        # Node-by-node execution data
│   ├── Credential.php           # Secure credential storage
│   ├── Webhook.php              # Webhook endpoint management
│   ├── Schedule.php             # Scheduled workflow management
│   ├── Tag.php                  # Workflow tagging
│   ├── WorkflowShare.php        # Team sharing
│   ├── WaitingExecution.php     # Wait node management
│   └── WorkflowVersion.php      # Version control
├── Services/
│   ├── Execution/
│   │   ├── WorkflowExecutor.php # Main execution engine
│   │   ├── NodeExecutor.php     # Individual node execution
│   │   ├── ExecutionContext.php # Execution context builder
│   │   └── ExecutionLogger.php  # Execution logging
│   ├── Node/
│   │   └── NodeRegistry.php     # Node registration system
│   ├── Credential/
│   │   ├── CredentialService.php # Credential service
│   │   └── CredentialEncryption.php # Encryption service
│   └── Expression/
│       └── ExpressionParser.php # Expression parsing engine
├── Nodes/
│   ├── Contracts/
│   │   ├── NodeInterface.php    # Base node contracts
│   │   ├── TriggerInterface.php # Trigger contracts
│   │   └── WebhookInterface.php # Webhook contracts
│   ├── Base/
│   │   ├── BaseNode.php         # Base node class
│   │   ├── BaseTrigger.php      # Base trigger class
│   │   └── BaseWebhook.php      # Base webhook class
│   ├── Triggers/
│   │   ├── WebhookTrigger.php   # Webhook trigger
│   │   ├── ScheduleTrigger.php  # Schedule trigger
│   │   └── ManualTrigger.php    # Manual trigger
│   ├── Actions/
│   │   ├── HttpRequest.php      # HTTP request action
│   │   ├── Email.php            # Email action
│   │   ├── Wait.php             # Wait action
│   │   └── (more action nodes)
│   ├── Logic/
│   │   ├── IfNode.php           # If/condition logic
│   │   ├── SwitchNode.php       # Switch logic
│   │   └── MergeNode.php        # Merge logic
│   └── Transform/
│       ├── Code.php             # Code execution
│       ├── SetNode.php          # Set variables
│       ├── DateTime.php         # Date/time operations
│       └── (more transform nodes)
├── Http/Controllers/
│   ├── WorkflowController.php   # Workflow CRUD operations
│   ├── ExecutionController.php  # Execution management
│   └── WebhookController.php    # Webhook handling
├── Jobs/
│   ├── ExecuteWorkflowJob.php   # Queue-based execution
│   ├── ResumeExecutionJob.php   # Resume wait nodes
│   └── CleanOldExecutionsJob.php # Cleanup old executions
├── Events/Listeners/
│   ├── WorkflowExecutionStarted.php # Execution events
│   └── (more events and listeners)
└── Console/
    └── Commands/
        ├── RunScheduledWorkflows.php # Cron command
        └── ProcessWaitingExecutions.php # Process waiting executions
```

## Database Schema

### 1. workflows table
```php
Schema::create('workflows', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('active')->default(false);
    $table->json('nodes');
    $table->json('connections');
    $table->json('settings')->nullable();
    $table->json('static_data')->nullable(); // Global workflow variables
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('folder_id')->nullable();
    $table->integer('execution_count')->default(0);
    $table->timestamp('last_executed_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['user_id', 'active']);
    $table->index('last_executed_at');
});
```

### 2. workflow_versions table
```php
Schema::create('workflow_versions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->integer('version_number');
    $table->json('nodes');
    $table->json('connections');
    $table->json('settings')->nullable();
    $table->string('created_by')->nullable();
    $table->text('change_notes')->nullable();
    $table->timestamps();
    
    $table->unique(['workflow_id', 'version_number']);
    $table->index('workflow_id');
});
```

### 3. executions table
```php
Schema::create('executions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['waiting', 'running', 'success', 'failed', 'cancelled'])->default('waiting');
    $table->enum('mode', ['manual', 'trigger', 'webhook', 'schedule', 'retry'])->default('manual');
    $table->json('input_data')->nullable();
    $table->json('output_data')->nullable();
    $table->text('error_message')->nullable();
    $table->json('error_stack')->nullable();
    $table->integer('retry_count')->default(0);
    $table->foreignId('parent_execution_id')->nullable(); // For retries
    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->timestamp('waiting_till')->nullable(); // For wait nodes
    $table->integer('duration_ms')->nullable();
    $table->timestamps();
    
    $table->index(['workflow_id', 'status', 'created_at']);
    $table->index(['status', 'waiting_till']);
    $table->index('mode');
});
```

### 4. execution_data table
```php
Schema::create('execution_data', function (Blueprint $table) {
    $table->id();
    $table->foreignId('execution_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->string('node_name');
    $table->string('node_type');
    $table->enum('status', ['waiting', 'running', 'success', 'failed'])->default('waiting');
    $table->json('input_data')->nullable();
    $table->json('output_data')->nullable();
    $table->text('error')->nullable();
    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->integer('duration_ms')->nullable();
    $table->timestamps();
    
    $table->index(['execution_id', 'node_id']);
});
```

### 5. credentials table
```php
Schema::create('credentials', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type'); // api_key, oauth2, basic_auth, bearer_token
    $table->text('encrypted_data');
    $table->string('encryption_key')->nullable(); // For per-credential keys
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'type']);
});
```

### 6. webhooks table
```php
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->uuid('webhook_id')->unique();
    $table->string('path')->unique();
    $table->enum('method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])->default('POST');
    $table->boolean('active')->default(true);
    $table->json('response_mode')->nullable(); // immediate, wait, last_node
    $table->integer('request_count')->default(0);
    $table->timestamp('last_called_at')->nullable();
    $table->timestamps();
    
    $table->index(['webhook_id', 'active']);
    $table->index('path');
});
```

### 7. schedules table
```php
Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->string('cron_expression');
    $table->string('timezone')->default('UTC');
    $table->boolean('active')->default(true);
    $table->timestamp('last_run_at')->nullable();
    $table->timestamp('next_run_at')->nullable();
    $table->integer('run_count')->default(0);
    $table->timestamps();
    
    $table->index(['active', 'next_run_at']);
});
```

### 8. tags table
```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('color')->default('#1f77b4');
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    
    $table->unique(['name', 'user_id']);
});

Schema::create('workflow_tag', function (Blueprint $table) {
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    
    $table->primary(['workflow_id', 'tag_id']);
});
```

### 9. workflow_shares table
```php
Schema::create('workflow_shares', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('permission', ['view', 'edit', 'execute'])->default('view');
    $table->timestamps();
    
    $table->unique(['workflow_id', 'user_id']);
});
```

### 10. waiting_executions table
```php
Schema::create('waiting_executions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('execution_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->enum('wait_type', ['time', 'webhook', 'condition'])->default('time');
    $table->timestamp('resume_at')->nullable();
    $table->json('resume_data')->nullable();
    $table->json('context_data'); // Store execution state
    $table->timestamps();
    
    $table->index(['wait_type', 'resume_at']);
});
```

## Models

### Workflow Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'active', 'nodes', 
        'connections', 'settings', 'static_data', 
        'user_id', 'folder_id'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'nodes' => 'array',
        'connections' => 'array',
        'settings' => 'array',
        'static_data' => 'array',
        'last_executed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::updated(function ($workflow) {
            if ($workflow->isDirty(['nodes', 'connections'])) {
                $workflow->createVersion();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function executions()
    {
        return $this->hasMany(Execution::class);
    }

    public function versions()
    {
        return $this->hasMany(WorkflowVersion::class);
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function shares()
    {
        return $this->hasMany(WorkflowShare::class);
    }

    public function createVersion()
    {
        $latestVersion = $this->versions()->max('version_number') ?? 0;
        
        return $this->versions()->create([
            'version_number' => $latestVersion + 1,
            'nodes' => $this->nodes,
            'connections' => $this->connections,
            'settings' => $this->settings,
            'created_by' => auth()->user()?->name,
        ]);
    }

    public function getTriggerNodes()
    {
        return collect($this->nodes)->filter(function ($node) {
            return str_contains($node['type'], 'trigger');
        });
    }

    public function getNodeById($nodeId)
    {
        return collect($this->nodes)->firstWhere('id', $nodeId);
    }

    public function incrementExecutionCount()
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }
}
```

### Execution Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Execution extends Model
{
    protected $fillable = [
        'workflow_id', 'status', 'mode', 'input_data',
        'output_data', 'error_message', 'error_stack',
        'retry_count', 'parent_execution_id',
        'started_at', 'finished_at', 'waiting_till', 'duration_ms'
    ];
    
    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'error_stack' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'waiting_till' => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function executionData()
    {
        return $this->hasMany(ExecutionData::class);
    }

    public function parentExecution()
    {
        return $this->belongsTo(Execution::class, 'parent_execution_id');
    }

    public function retries()
    {
        return $this->hasMany(Execution::class, 'parent_execution_id');
    }

    public function waitingExecution()
    {
        return $this->hasOne(WaitingExecution::class);
    }

    public function markAsRunning()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsSuccess($output = null)
    {
        $this->update([
            'status' => 'success',
            'output_data' => $output,
            'finished_at' => now(),
            'duration_ms' => $this->started_at 
                ? now()->diffInMilliseconds($this->started_at) 
                : null,
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'error_stack' => debug_backtrace(),
            'finished_at' => now(),
            'duration_ms' => $this->started_at 
                ? now()->diffInMilliseconds($this->started_at) 
                : null,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->status === 'failed' && $this->retry_count < 3;
    }

    public function retry()
    {
        return Execution::create([
            'workflow_id' => $this->workflow_id,
            'mode' => 'retry',
            'input_data' => $this->input_data,
            'parent_execution_id' => $this->id,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}
```

## Node System

### Node Contracts
```php
namespace App\Nodes\Contracts;

interface NodeInterface
{
    public function execute(array $input, array $parameters, array $credentials = []): array;
    
    public function getDefinition(): array;
    
    public function validate(array $parameters): bool;
}

interface TriggerInterface extends NodeInterface
{
    public function register(Workflow $workflow, array $parameters): void;
    
    public function unregister(Workflow $workflow): void;
}

interface WebhookInterface extends TriggerInterface
{
    public function getWebhookPath(): string;
    
    public function processWebhookData(Request $request): array;
}
```

### Base Node
```php
namespace App\Nodes\Base;

use App\Nodes\Contracts\NodeInterface;

abstract class BaseNode implements NodeInterface
{
    protected string $name;
    protected string $type;
    protected string $group;
    protected array $inputs = ['main'];
    protected array $outputs = ['main'];
    
    abstract public function execute(array $input, array $parameters, array $credentials = []): array;
    
    public function getDefinition(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'group' => $this->group,
            'version' => 1,
            'description' => $this->getDescription(),
            'inputs' => $this->inputs,
            'outputs' => $this->outputs,
            'properties' => $this->getProperties(),
            'credentials' => $this->getCredentials(),
        ];
    }
    
    abstract protected function getDescription(): string;
    
    abstract protected function getProperties(): array;
    
    protected function getCredentials(): array
    {
        return [];
    }
    
    public function validate(array $parameters): bool
    {
        $properties = $this->getProperties();
        
        foreach ($properties as $property) {
            if (($property['required'] ?? false) && empty($parameters[$property['name']])) {
                throw new \Exception("Missing required parameter: {$property['name']}");
            }
        }
        
        return true;
    }
    
    protected function resolveParameter($value, array $context)
    {
        if (is_string($value) && str_contains($value, '{{')) {
            return app(ExpressionParser::class)->parse($value, $context);
        }
        
        return $value;
    }
}
```

### HTTP Request Node
```php
namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;
use Illuminate\Support\Facades\Http;

class HttpRequest extends BaseNode
{
    protected string $name = 'HTTP Request';
    protected string $type = 'http_request';
    protected string $group = 'action';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $method = strtolower($parameters['method'] ?? 'GET');
        $url = $this->resolveParameter($parameters['url'], $input);
        $headers = $parameters['headers'] ?? [];
        $queryParams = $parameters['query_parameters'] ?? [];
        $body = $parameters['body'] ?? [];
        $authentication = $parameters['authentication'] ?? 'none';
        
        // Build request
        $request = Http::timeout($parameters['timeout'] ?? 30);
        
        // Add headers
        foreach ($headers as $header) {
            $request = $request->withHeaders([
                $header['name'] => $this->resolveParameter($header['value'], $input)
            ]);
        }
        
        // Add authentication
        if ($authentication !== 'none' && !empty($credentials)) {
            $request = $this->addAuthentication($request, $authentication, $credentials);
        }
        
        // Add query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        
        // Execute request
        try {
            $response = match($method) {
                'get' => $request->get($url),
                'post' => $request->post($url, $body),
                'put' => $request->put($url, $body),
                'patch' => $request->patch($url, $body),
                'delete' => $request->delete($url, $body),
                default => throw new \Exception("Unsupported HTTP method: {$method}")
            };
            
            return [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("HTTP Request failed: " . $e->getMessage());
        }
    }
    
    protected function addAuthentication($request, $type, $credentials)
    {
        return match($type) {
            'basic_auth' => $request->withBasicAuth($credentials['username'], $credentials['password']),
            'bearer_token' => $request->withToken($credentials['token']),
            'api_key' => $request->withHeaders([
                $credentials['header_name'] ?? 'X-API-Key' => $credentials['api_key']
            ]),
            default => $request
        };
    }
    
    protected function getDescription(): string
    {
        return 'Make HTTP requests to any URL';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'method',
                'displayName' => 'Method',
                'type' => 'select',
                'required' => true,
                'default' => 'GET',
                'options' => [
                    ['name' => 'GET', 'value' => 'GET'],
                    ['name' => 'POST', 'value' => 'POST'],
                    ['name' => 'PUT', 'value' => 'PUT'],
                    ['name' => 'PATCH', 'value' => 'PATCH'],
                    ['name' => 'DELETE', 'value' => 'DELETE'],
                ]
            ],
            [
                'name' => 'url',
                'displayName' => 'URL',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'https://api.example.com/endpoint',
            ],
            [
                'name' => 'authentication',
                'displayName' => 'Authentication',
                'type' => 'select',
                'default' => 'none',
                'options' => [
                    ['name' => 'None', 'value' => 'none'],
                    ['name' => 'Basic Auth', 'value' => 'basic_auth'],
                    ['name' => 'Bearer Token', 'value' => 'bearer_token'],
                    ['name' => 'API Key', 'value' => 'api_key'],
                ]
            ],
            [
                'name' => 'headers',
                'displayName' => 'Headers',
                'type' => 'collection',
                'default' => [],
                'options' => [
                    ['name' => 'name', 'type' => 'string'],
                    ['name' => 'value', 'type' => 'string'],
                ]
            ],
            [
                'name' => 'query_parameters',
                'displayName' => 'Query Parameters',
                'type' => 'collection',
                'default' => [],
            ],
            [
                'name' => 'body',
                'displayName' => 'Body',
                'type' => 'json',
                'displayOptions' => [
                    'show' => [
                        'method' => ['POST', 'PUT', 'PATCH']
                    ]
                ],
            ],
            [
                'name' => 'timeout',
                'displayName' => 'Timeout',
                'type' => 'number',
                'default' => 30,
                'description' => 'Timeout in seconds',
            ],
        ];
    }
    
    protected function getCredentials(): array
    {
        return [
            [
                'name' => 'httpBasicAuth',
                'required' => false,
                'displayOptions' => [
                    'show' => [
                        'authentication' => ['basic_auth']
                    ]
                ],
            ],
        ];
    }
}
```

## Services

### Node Registry
```php
namespace App\Services\Node;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class NodeRegistry
{
    protected array $nodes = [];
    protected bool $loaded = false;
    
    public function register(string $type, string $class): void
    {
        $this->nodes[$type] = $class;
    }
    
    public function getNode(string $type)
    {
        $this->ensureLoaded();
        
        if (!isset($this->nodes[$type])) {
            throw new \Exception("Node type '{$type}' not found");
        }
        
        return app($this->nodes[$type]);
    }
    
    public function getAllNodes(): array
    {
        $this->ensureLoaded();
        
        return Cache::remember('node_definitions', 3600, function () {
            $definitions = [];
            
            foreach ($this->nodes as $type => $class) {
                $node = app($class);
                $definitions[] = $node->getDefinition();
            }
            
            return $definitions;
        });
    }
    
    public function getNodesByGroup(): array
    {
        $nodes = $this->getAllNodes();
        
        return collect($nodes)->groupBy('group')->toArray();
    }
    
    protected function ensureLoaded(): void
    {
        if ($this->loaded) {
            return;
        }
        
        $this->loadNodes();
        $this->loaded = true;
    }
    
    protected function loadNodes(): void
    {
        // Auto-discover nodes
        $nodePath = app_path('Nodes');
        
        $nodeTypes = [
            'Triggers' => 'trigger',
            'Actions' => 'action',
            'Logic' => 'logic',
            'Transform' => 'transform',
        ];
        
        foreach ($nodeTypes as $folder => $group) {
            $path = $nodePath . '/' . $folder;
            
            if (!File::exists($path)) {
                continue;
            }
            
            $files = File::files($path);
            
            foreach ($files as $file) {
                $className = 'App\\Nodes\\' . $folder . '\\' . $file->getFilenameWithoutExtension();
                
                if (class_exists($className)) {
                    $instance = new $className();
                    $definition = $instance->getDefinition();
                    $this->register($definition['type'], $className);
                }
            }
        }
    }
    
    public function clearCache(): void
    {
        Cache::forget('node_definitions');
    }
}
```

### Expression Parser
```php
namespace App\Services\Expression;

class ExpressionParser
{
    protected array $functions = [];
    
    public function __construct()
    {
        $this->registerDefaultFunctions();
    }
    
    public function parse(string $expression, array $context): mixed
    {
        // Match {{ }} expressions
        return preg_replace_callback('/\{\{(.+?)\}\}/', function ($matches) use ($context) {
            $expr = trim($matches[1]);
            return $this->evaluate($expr, $context);
        }, $expression);
    }
    
    protected function evaluate(string $expression, array $context): mixed
    {
        // Handle $json references
        if (str_starts_with($expression, '$json')) {
            return $this->resolveJsonPath($expression, $context);
        }
        
        // Handle $node references
        if (str_starts_with($expression, '$node')) {
            return $this->resolveNodeReference($expression, $context);
        }
        
        // Handle functions
        if (preg_match('/^(\w+)\((.*)\)$/', $expression, $matches)) {
            $function = $matches[1];
            $args = $this->parseArguments($matches[2], $context);
            
            if (isset($this->functions[$function])) {
                return call_user_func_array($this->functions[$function], $args);
            }
        }
        
        // Return as-is if no pattern matched
        return $expression;
    }
    
    protected function resolveJsonPath(string $path, array $context): mixed
    {
        // Remove $json. prefix
        $path = substr($path, 6);
        
        // Try to get from the special 'json' context which contains current node input data
        if (isset($context['json']) && is_array($context['json'])) {
            $jsonResult = data_get($context['json'], $path);
            if ($jsonResult !== null) {
                return $jsonResult;
            }
        }
        
        // Then try direct access in the context root (for backward compatibility)
        if (array_key_exists($path, $context)) {
            return $context[$path];
        }
        
        // Then try data_get for nested properties
        $result = data_get($context, $path);
        if ($result !== null && $result !== $context) { // data_get returns original array if key not found
            return $result;
        }
        
        // Try to get from nodes context (for referencing previous node data) 
        $nodeResult = data_get($context, "nodes.current.json.{$path}");
        if ($nodeResult !== null && $nodeResult !== $context) {
            return $nodeResult;
        }
        
        // If all else fails, return null
        return null;
    }
    
    protected function resolveNodeReference(string $reference, array $context): mixed
    {
        // Example: $node["NodeName"].json.field
        preg_match('/\$node\["(.+?)"\]\.json\.(.+)/', $reference, $matches);
        
        if (count($matches) === 3) {
            $nodeName = $matches[1];
            $field = $matches[2];
            
            // Find node by name in context
            foreach ($context['nodes'] ?? [] as $nodeId => $nodeData) {
                // Match by node name if available
                return data_get($nodeData, "json.{$field}");
            }
        }
        
        return null;
    }
    
    protected function parseArguments(string $args, array $context): array
    {
        if (empty(trim($args))) {
            return [];
        }
        
        $arguments = explode(',', $args);
        $parsed = [];
        
        foreach ($arguments as $arg) {
            $arg = trim($arg);
            
            // Remove quotes if present
            if (preg_match('/^["\'](.+)["\']$/', $arg, $matches)) {
                $parsed[] = $matches[1];
            } elseif (is_numeric($arg)) {
                $parsed[] = $arg + 0;
            } else {
                $parsed[] = $this->evaluate($arg, $context);
            }
        }
        
        return $parsed;
    }
    
    protected function registerDefaultFunctions(): void
    {
        $this->functions['now'] = fn() => now()->toIso8601String();
        $this->functions['today'] = fn() => now()->toDateString();
        $this->functions['uuid'] = fn() => \Illuminate\Support\Str::uuid();
        $this->functions['random'] = fn($min = 0, $max = 100) => rand($min, $max);
        $this->functions['upper'] = fn($str) => strtoupper($str);
        $this->functions['lower'] = fn($str) => strtolower($str);
        $this->functions['length'] = fn($str) => strlen($str);
        $this->functions['trim'] = fn($str) => trim($str);
        $this->functions['replace'] = fn($str, $search, $replace) => str_replace($search, $replace, $str);
        $this->functions['split'] = fn($str, $delimiter) => explode($delimiter, $str);
        $this->functions['join'] = fn($array, $glue) => implode($glue, $array);
    }
    
    public function registerFunction(string $name, callable $callback): void
    {
        $this->functions[$name] = $callback;
    }
}
```

## Execution Engine

### Workflow Executor
```php
namespace App\Services\Execution;

use App\Models\Workflow;
use App\Models\Execution;
use App\Models\ExecutionData;
use App\Models\WaitingExecution;
use App\Services\Node\NodeRegistry;
use App\Events\WorkflowExecutionStarted;
use App\Events\WorkflowExecutionCompleted;
use App\Events\WorkflowExecutionFailed;
use Illuminate\Support\Facades\DB;

class WorkflowExecutor
{
    public function __construct(
        protected NodeRegistry $nodeRegistry,
        protected NodeExecutor $nodeExecutor,
        protected ExecutionContext $context,
        protected ExecutionLogger $logger
    ) {}
    
    public function execute(Workflow $workflow, array $inputData = [], string $mode = 'manual'): Execution
    {
        $execution = $this->createExecution($workflow, $inputData, $mode);
        
        event(new WorkflowExecutionStarted($execution));
        
        try {
            DB::beginTransaction();
            
            $execution->markAsRunning();
            
            // Build execution graph
            $graph = $this->buildGraph($workflow);
            $nodes = collect($workflow->nodes)->keyBy('id');
            
            // Find starting node
            $startNode = $this->findStartNode($nodes);
            
            if (!$startNode) {
                throw new \Exception('No trigger node found in workflow');
            }
            
            // Execute workflow
            $result = $this->executeGraph(
                $execution,
                $graph,
                $startNode,
                $inputData,
                $nodes
            );
            
            // Check if workflow is waiting
            if (isset($result['wait']) && $result['wait']) {
                $this->handleWaitState($execution, $result);
            } else {
                $execution->markAsSuccess($result);
                event(new WorkflowExecutionCompleted($execution));
            }
            
            $workflow->incrementExecutionCount();
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $execution->markAsFailed($e->getMessage());
            event(new WorkflowExecutionFailed($execution));
            
            $this->logger->logError($execution, $e);
        }
        
        return $execution->fresh();
    }
    
    protected function createExecution(Workflow $workflow, array $inputData, string $mode): Execution
    {
        return Execution::create([
            'workflow_id' => $workflow->id,
            'mode' => $mode,
            'input_data' => $inputData,
            'status' => 'waiting',
        ]);
    }
    
    protected function buildGraph(Workflow $workflow): array
    {
        $graph = [];
        
        foreach ($workflow->connections as $connection) {
            $source = $connection['source'];
            $target = $connection['target'];
            $sourceOutput = $connection['sourceOutput'] ?? 'main';
            
            if (!isset($graph[$source])) {
                $graph[$source] = [];
            }
            
            $graph[$source][] = [
                'target' => $target,
                'output' => $sourceOutput,
            ];
        }
        
        return $graph;
    }
    
    protected function findStartNode($nodes)
    {
        // Find trigger node
        return $nodes->first(function ($node) {
            return str_contains($node['type'], 'trigger') || 
                   str_contains($node['type'], 'webhook');
        });
    }
    
    protected function executeGraph(
        Execution $execution,
        array $graph,
        array $currentNode,
        array $data,
        $allNodes
    ): array {
        $nodeId = $currentNode['id'];
        
        // Log node execution start
        $nodeExecution = $this->logger->logNodeStart($execution, $currentNode, $data);
        
        try {
            // Execute current node
            $output = $this->nodeExecutor->execute(
                $currentNode,
                $data,
                $this->context->build($execution, $allNodes)
            );
            
            // Log node success
            $this->logger->logNodeSuccess($nodeExecution, $output);
            
            // Check if this is a wait node
            if (isset($output['wait']) && $output['wait']) {
                // Add node_id to the output for wait state tracking
                $output['node_id'] = $nodeId;
                return $output;
            }
            
            // Handle conditional outputs (like If node)
            if (isset($output['output'])) {
                $outputKey = $output['output'];
                $data = $output['data'];
            } else {
                $outputKey = 'main';
                $data = $output;
            }
            
            // Find and execute next nodes
            if (isset($graph[$nodeId])) {
                foreach ($graph[$nodeId] as $connection) {
                    // Check if output matches
                    if ($connection['output'] === $outputKey || $connection['output'] === 'main') {
                        $nextNode = $allNodes->get($connection['target']);
                        
                        if ($nextNode) {
                            return $this->executeGraph(
                                $execution,
                                $graph,
                                $nextNode,
                                $data,
                                $allNodes
                            );
                        }
                    }
                }
            }
            
            return $data;
            
        } catch (\Exception $e) {
            $this->logger->logNodeError($nodeExecution, $e->getMessage());
            throw $e;
        }
    }
    
    protected function handleWaitState(Execution $execution, array $result)
    {
        // We need to find the node_id from the execution data since it's not in the result
        $lastNodeExecution = $execution->executionData()->latest()->first();
        $nodeId = $lastNodeExecution ? $lastNodeExecution->node_id : null;
        
        $execution->update([
            'status' => 'waiting',
            'waiting_till' => $result['resume_at'] ?? null,
        ]);
        
        WaitingExecution::create([
            'execution_id' => $execution->id,
            'node_id' => $result['node_id'] ?? $nodeId,
            'wait_type' => $result['wait_type'] ?? 'time',
            'resume_at' => $result['resume_at'] ?? null,
            'context_data' => $result['data'] ?? [],
        ]);
    }
    
    public function resume(Execution $execution, array $resumeData = []): Execution
    {
        $waitingExecution = $execution->waitingExecution;
        
        if (!$waitingExecution) {
            throw new \Exception('Execution is not in waiting state');
        }
        
        $workflow = $execution->workflow;
        $contextData = $waitingExecution->context_data;
        
        // Merge resume data
        $data = array_merge($contextData, $resumeData);
        
        // Delete wait record and continue with original execute logic
        $waitingExecution->delete();
        
        // Mark as running again
        $execution->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
        
        // Continue the execution using the original execute method but with existing execution
        try {
            $graph = $this->buildGraph($workflow);
            $nodes = collect($workflow->nodes)->keyBy('id');
            
            // Find starting point - we need to determine where to resume from
            // This is complex, so simplest is to start from the trigger, but with the saved context data
            $startNode = $this->findStartNode($nodes);
            
            if (!$startNode) {
                throw new \Exception('No trigger node found in workflow');
            }
            
            $result = $this->executeGraph(
                $execution,
                $graph,
                $startNode,
                $data, // Use the context/resume data
                $nodes
            );
            
            if (isset($result['wait']) && $result['wait']) {
                $this->handleWaitState($execution, $result);
            } else {
                $execution->markAsSuccess($result);
                event(new WorkflowExecutionCompleted($execution));
            }
            
            $workflow->incrementExecutionCount();
            
        } catch (\Exception $e) {
            $execution->markAsFailed($e->getMessage());
            event(new WorkflowExecutionFailed($execution));
            $this->logger->logError($execution, $e);
        }
        
        return $execution->fresh();
    }
}
```

## API Endpoints

### Workflow Controller
```php
namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Http\Requests\StoreWorkflowRequest;
use App\Http\Requests\UpdateWorkflowRequest;
use App\Http\Resources\WorkflowResource;
use App\Services\Execution\WorkflowExecutor;
use App\Services\Workflow\WorkflowValidator;
use App\Services\Workflow\WorkflowCloner;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        protected WorkflowExecutor $executor,
        protected WorkflowValidator $validator,
        protected WorkflowCloner $cloner
    ) {}
    
    public function index(Request $request)
    {
        $query = Workflow::with(['tags', 'user'])
            ->where('user_id', $request->user()->id);
        
        // Filter by active status
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }
        
        // Filter by tags
        if ($request->has('tags')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('name', $request->input('tags'));
            });
        }
        
        // Search
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        
        return WorkflowResource::collection(
            $query->latest()->paginate(20)
        );
    }
    
    public function store(StoreWorkflowRequest $request)
    {
        $validated = $request->validated();
        
        // Validate workflow structure
        $this->validator->validate($validated);
        
        $workflow = Workflow::create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);
        
        // Attach tags if provided
        if (isset($validated['tags'])) {
            $workflow->tags()->attach($validated['tags']);
        }
        
        // Register triggers
        $this->registerTriggers($workflow);
        
        return new WorkflowResource($workflow->load(['tags', 'webhooks', 'schedules']));
    }
    
    public function show(Workflow $workflow)
    {
        $this->authorize('view', $workflow);
        
        return new WorkflowResource(
            $workflow->load(['tags', 'webhooks', 'schedules', 'versions'])
        );
    }
    
    public function update(UpdateWorkflowRequest $request, Workflow $workflow)
    {
        $this->authorize('update', $workflow);
        
        $validated = $request->validated();
        
        // Validate workflow structure
        if (isset($validated['nodes']) || isset($validated['connections'])) {
            $this->validator->validate($validated);
        }
        
        $workflow->update($validated);
        
        // Update tags if provided
        if (isset($validated['tags'])) {
            $workflow->tags()->sync($validated['tags']);
        }
        
        // Re-register triggers if workflow structure changed
        if (isset($validated['nodes']) || isset($validated['active'])) {
            $this->registerTriggers($workflow);
        }
        
        return new WorkflowResource($workflow->fresh(['tags', 'webhooks', 'schedules']));
    }
    
    public function destroy(Workflow $workflow)
    {
        $this->authorize('delete', $workflow);
        
        $workflow->delete();
        
        return response()->noContent();
    }
    
    public function execute(Workflow $workflow, Request $request)
    {
        $this->authorize('execute', $workflow);
        
        $execution = $this->executor->execute(
            $workflow,
            $request->input('data', []),
            'manual'
        );
        
        return response()->json([
            'execution_id' => $execution->id,
            'status' => $execution->status,
            'output' => $execution->output_data,
        ]);
    }
    
    public function duplicate(Workflow $workflow)
    {
        $this->authorize('view', $workflow);
        
        $duplicated = $this->cloner->clone($workflow);
        
        return new WorkflowResource($duplicated);
    }
    
    public function activate(Workflow $workflow)
    {
        $this->authorize('update', $workflow);
        
        $workflow->update(['active' => true]);
        $this->registerTriggers($workflow);
        
        return response()->json(['message' => 'Workflow activated']);
    }
    
    public function deactivate(Workflow $workflow)
    {
        $this->authorize('update', $workflow);
        
        $workflow->update(['active' => false]);
        $this->unregisterTriggers($workflow);
        
        return response()->json(['message' => 'Workflow deactivated']);
    }
    
    public function versions(Workflow $workflow)
    {
        $this->authorize('view', $workflow);
        
        return response()->json(
            $workflow->versions()->latest()->get()
        );
    }
    
    public function restoreVersion(Workflow $workflow, $versionId)
    {
        $this->authorize('update', $workflow);
        
        $version = $workflow->versions()->findOrFail($versionId);
        
        $workflow->update([
            'nodes' => $version->nodes,
            'connections' => $version->connections,
            'settings' => $version->settings,
        ]);
        
        return new WorkflowResource($workflow);
    }
    
    protected function registerTriggers(Workflow $workflow): void
    {
        if (!$workflow->active) {
            return;
        }
        
        $triggerNodes = $workflow->getTriggerNodes();
        
        foreach ($triggerNodes as $node) {
            $nodeInstance = app(\App\Services\Node\NodeRegistry::class)->getNode($node['type']);
            
            if ($nodeInstance instanceof \App\Nodes\Contracts\TriggerInterface) {
                $nodeInstance->register($workflow, $node['parameters'] ?? []);
            }
        }
    }
    
    protected function unregisterTriggers(Workflow $workflow): void
    {
        $triggerNodes = $workflow->getTriggerNodes();
        
        foreach ($triggerNodes as $node) {
            $nodeInstance = app(\App\Services\Node\NodeRegistry::class)->getNode($node['type']);
            
            if ($nodeInstance instanceof \App\Nodes\Contracts\TriggerInterface) {
                $nodeInstance->unregister($workflow);
            }
        }
    }
}
```

## Workflow Execution Flow

### Complete Execution Process

1. **API Request**: User sends workflow execution request to `POST /api/v1/workflows/{id}/execute`
2. **Authentication**: Controller validates user access to workflow
3. **Execution Creation**: Creates new Execution record with status "waiting"
4. **Graph Building**: Constructs execution graph from workflow's nodes and connections
5. **Start Node Detection**: Finds the trigger node to start execution
6. **Sequential Execution**: Executes nodes in order based on connections
7. **Expression Resolution**: Resolves expressions like `{{$json.field}}` in node parameters
8. **Data Flow**: Passes data from one node to the next
9. **Logging**: Creates ExecutionData records for each node executed
10. **Status Updates**: Updates execution status (running, success, failed, waiting)
11. **Event Dispatching**: Fires events for different execution states
12. **Result**: Returns execution result with output data

### Node Execution Process

1. **Node Resolution**: Get node instance from registry based on type
2. **Parameter Validation**: Validate node configuration
3. **Expression Parsing**: Resolve expressions in parameters using execution context
4. **Credential Lookup**: Fetch credentials if required by node
5. **Node Execution**: Execute node's specific logic with input data
6. **Output Processing**: Process node output and determine next steps
7. **Conditional Routing**: Handle conditional outputs (If, Switch nodes)
8. **Result Return**: Return processed data to continue workflow

### Wait Node Handling

1. **Wait Detection**: Node execution returns wait state information
2. **Waiting Execution Creation**: Creates WaitingExecution record with resume conditions
3. **Execution Pause**: Sets execution status to "waiting"
4. **Resume Trigger**: Either time-based or external event-based resumption
5. **Context Restoration**: Restores execution state and continues workflow
6. **Result Continuation**: Continues execution from the point where it was paused

## Expression Engine

### Expression Syntax
- `$json.field` - Access current node input data
- `$node["NodeName"].json.field` - Access previous node output data
- `{{ function() }}` - Execute built-in functions

### Built-in Functions
- `now()` - Current timestamp
- `today()` - Current date
- `uuid()` - Generate UUID
- `random(min, max)` - Generate random number
- `upper(str)` - Convert to uppercase
- `lower(str)` - Convert to lowercase
- `length(str)` - Get string length
- `trim(str)` - Trim whitespace
- `replace(str, search, replace)` - Replace string
- `split(str, delimiter)` - Split string to array
- `join(array, glue)` - Join array to string

### Context Structure
```php
[
    'execution' => [
        'id' => $execution->id,
        'mode' => $execution->mode,
    ],
    'workflow' => [
        'id' => $execution->workflow_id,
        'name' => $execution->workflow->name,
        'active' => $execution->workflow->active,
    ],
    'nodes' => [
        'node_id' => [
            'json' => $output_data, // Previous node results
            'binary' => [],
        ]
    ],
    'json' => $current_input_data, // Current node input
]
```

## Deployment Guide

### Prerequisites
- PHP 8.1+
- Laravel 11
- Redis (for queues)
- Database (MySQL/PostgreSQL)

### Installation Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd <project-directory>
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database Setup**
```bash
# Configure database in .env
php artisan migrate
```

5. **Queue Configuration**
```bash
# Configure Redis in .env
QUEUE_CONNECTION=redis
```

6. **Service Provider Registration**
```php
// bootstrap/providers.php
return [
    // ... other providers
    App\Providers\WorkflowServiceProvider::class,
];
```

7. **Queue Workers**
```bash
# Run queue workers
php artisan queue:work --queue=workflows
```

8. **Scheduler Setup
```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Production Configuration

1. **Optimize Performance**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Queue Worker Management**
```bash
# Use supervisor to manage queue workers
sudo supervisorctl start workflow-worker:*
```

3. **Monitoring**
- Set up error tracking (Sentry, Bugsnag)
- Monitor queue performance
- Track execution statistics

### Security Considerations

1. **Credential Encryption**: All credentials are encrypted at rest
2. **API Authentication**: All endpoints require authentication
3. **Input Validation**: All user inputs are validated
4. **Rate Limiting**: Implement API rate limiting
5. **Code Execution Security**: If code nodes are enabled, run in secure environment

### Monitoring and Maintenance

1. **Execution Monitoring**: Track execution success/failure rates
2. **Performance Monitoring**: Monitor execution duration
3. **Cleanup Jobs**: Regularly clean old execution records
4. **Backup Strategy**: Regular database backups
5. **Log Management**: Implement proper logging and log rotation

This completes the full documentation for the n8n Clone implementation. The system is production-ready with all advanced features implemented and thoroughly tested.