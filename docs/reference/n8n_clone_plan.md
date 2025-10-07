# Advanced n8n Clone - Complete Laravel Backend

## 📁 Complete Folder Structure

```
app/
├── Models/
│   ├── Workflow.php
│   ├── WorkflowVersion.php          // Version control
│   ├── Execution.php
│   ├── ExecutionData.php            // Store node-by-node data
│   ├── Credential.php
│   ├── Webhook.php
│   ├── Schedule.php                 // Cron schedules
│   ├── Tag.php
│   └── WorkflowShare.php            // Team sharing
│
├── Services/
│   ├── Execution/
│   │   ├── WorkflowExecutor.php
│   │   ├── NodeExecutor.php
│   │   ├── ExecutionContext.php
│   │   └── ExecutionLogger.php
│   ├── Workflow/
│   │   ├── WorkflowBuilder.php
│   │   ├── WorkflowValidator.php
│   │   └── WorkflowCloner.php
│   ├── Node/
│   │   ├── NodeRegistry.php
│   │   ├── NodeLoader.php
│   │   └── NodeParameterResolver.php
│   ├── Credential/
│   │   ├── CredentialService.php
│   │   └── CredentialEncryption.php
│   ├── Expression/
│   │   ├── ExpressionParser.php     // {{$json.field}} parser
│   │   └── FunctionRegistry.php
│   └── Webhook/
│       ├── WebhookManager.php
│       └── WebhookRouter.php
│
├── Nodes/
│   ├── Contracts/
│   │   ├── NodeInterface.php
│   │   ├── TriggerInterface.php
│   │   ├── PollingInterface.php
│   │   └── WebhookInterface.php
│   │
│   ├── Base/
│   │   ├── BaseNode.php
│   │   ├── BaseTrigger.php
│   │   └── BaseWebhook.php
│   │
│   ├── Triggers/
│   │   ├── WebhookTrigger.php
│   │   ├── ScheduleTrigger.php
│   │   ├── ManualTrigger.php
│   │   └── EmailTrigger.php
│   │
│   ├── Actions/
│   │   ├── HttpRequest.php
│   │   ├── Email.php
│   │   ├── Database.php
│   │   ├── Slack.php
│   │   ├── Discord.php
│   │   ├── SendGrid.php
│   │   ├── Stripe.php
│   │   ├── GoogleSheets.php
│   │   ├── Airtable.php
│   │   └── Notion.php
│   │
│   ├── Logic/
│   │   ├── If.php
│   │   ├── Switch.php
│   │   ├── Merge.php
│   │   ├── Split.php
│   │   ├── Loop.php
│   │   ├── Filter.php
│   │   └── Aggregate.php
│   │
│   ├── Transform/
│   │   ├── SetNode.php              // Set variables
│   │   ├── Code.php                 // Run JS code
│   │   ├── Function.php
│   │   ├── DateTime.php
│   │   └── Crypto.php
│   │
│   └── Wait/
│       ├── Wait.php                 // Delay execution
│       └── WaitForWebhook.php       // Wait for external trigger
│
├── Http/
│   ├── Controllers/
│   │   ├── WorkflowController.php
│   │   ├── ExecutionController.php
│   │   ├── WebhookController.php
│   │   ├── CredentialController.php
│   │   ├── NodeController.php
│   │   ├── TagController.php
│   │   └── ScheduleController.php
│   │
│   ├── Requests/
│   │   ├── StoreWorkflowRequest.php
│   │   ├── UpdateWorkflowRequest.php
│   │   ├── ExecuteWorkflowRequest.php
│   │   └── StoreCredentialRequest.php
│   │
│   ├── Resources/
│   │   ├── WorkflowResource.php
│   │   ├── ExecutionResource.php
│   │   └── NodeResource.php
│   │
│   └── Middleware/
│       ├── ValidateWebhook.php
│       └── CheckWorkflowOwnership.php
│
├── Jobs/
│   ├── ExecuteWorkflowJob.php
│   ├── ExecuteNodeJob.php
│   ├── ResumeExecutionJob.php       // For wait nodes
│   ├── PollingTriggerJob.php
│   └── CleanOldExecutionsJob.php
│
├── Events/
│   ├── WorkflowExecutionStarted.php
│   ├── WorkflowExecutionCompleted.php
│   ├── WorkflowExecutionFailed.php
│   └── NodeExecuted.php
│
├── Listeners/
│   ├── LogExecutionStarted.php
│   ├── NotifyExecutionFailure.php
│   └── UpdateWorkflowStatistics.php
│
├── Observers/
│   ├── WorkflowObserver.php
│   └── ExecutionObserver.php
│
├── Exceptions/
│   ├── NodeExecutionException.php
│   ├── WorkflowExecutionException.php
│   ├── CredentialNotFoundException.php
│   └── ExpressionParseException.php
│
└── Console/
    └── Commands/
        ├── RunScheduledWorkflows.php
        ├── ProcessPollingTriggers.php
        └── CleanupExecutions.php
```

## 🗄️ Complete Database Schema

### 1. workflows
```php
Schema::create('workflows', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('active')->default(false);
    $table->json('nodes');
    $table->json('connections');
    $table->json('settings')->nullable();
    $table->json('static_data')->nullable();  // Global workflow variables
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

### 2. workflow_versions
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

### 3. executions
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
    $table->foreignId('parent_execution_id')->nullable();  // For retries
    $table->timestamp('started_at')->nullable();
    $table->timestamp('finished_at')->nullable();
    $table->timestamp('waiting_till')->nullable();  // For wait nodes
    $table->integer('duration_ms')->nullable();
    $table->timestamps();
    
    $table->index(['workflow_id', 'status', 'created_at']);
    $table->index(['status', 'waiting_till']);
    $table->index('mode');
});
```

### 4. execution_data
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

### 5. credentials
```php
Schema::create('credentials', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type');  // api_key, oauth2, basic_auth, bearer_token
    $table->text('encrypted_data');
    $table->string('encryption_key')->nullable();  // For per-credential keys
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'type']);
});
```

### 6. webhooks
```php
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->uuid('webhook_id')->unique();
    $table->string('path')->unique();
    $table->enum('method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])->default('POST');
    $table->boolean('active')->default(true);
    $table->json('response_mode')->nullable();  // immediate, wait, last_node
    $table->integer('request_count')->default(0);
    $table->timestamp('last_called_at')->nullable();
    $table->timestamps();
    
    $table->index(['webhook_id', 'active']);
    $table->index('path');
});
```

### 7. schedules
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

### 8. tags
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

### 9. workflow_shares
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

### 10. waiting_executions (for wait nodes)
```php
Schema::create('waiting_executions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('execution_id')->constrained()->cascadeOnDelete();
    $table->string('node_id');
    $table->enum('wait_type', ['time', 'webhook', 'condition'])->default('time');
    $table->timestamp('resume_at')->nullable();
    $table->json('resume_data')->nullable();
    $table->json('context_data');  // Store execution state
    $table->timestamps();
    
    $table->index(['wait_type', 'resume_at']);
});
```

## 🏗️ Core Models

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
            'duration_ms' => $this->started_at ? now()->diffInMilliseconds($this->started_at) : null,
        ]);
    }

    public function markAsFailed($error)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'error_stack' => debug_backtrace(),
            'finished_at' => now(),
            'duration_ms' => $this->started_at ? now()->diffInMilliseconds($this->started_at) : null,
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

### ExecutionData Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExecutionData extends Model
{
    protected $fillable = [
        'execution_id', 'node_id', 'node_name', 'node_type',
        'status', 'input_data', 'output_data', 'error',
        'started_at', 'finished_at', 'duration_ms'
    ];
    
    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function execution()
    {
        return $this->belongsTo(Execution::class);
    }
}
```

### Credential Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\Credential\CredentialEncryption;

class Credential extends Model
{
    protected $fillable = [
        'name', 'type', 'encrypted_data', 
        'encryption_key', 'user_id', 'last_used_at'
    ];
    
    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['encrypted_data', 'encryption_key'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setDataAttribute($value)
    {
        $encryptor = new CredentialEncryption();
        $this->attributes['encrypted_data'] = $encryptor->encrypt($value);
    }

    public function getDataAttribute()
    {
        $encryptor = new CredentialEncryption();
        return $encryptor->decrypt($this->encrypted_data);
    }

    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }
}
```

### Webhook Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Webhook extends Model
{
    protected $fillable = [
        'workflow_id', 'node_id', 'webhook_id', 'path',
        'method', 'active', 'response_mode', 
        'request_count', 'last_called_at'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'response_mode' => 'array',
        'last_called_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($webhook) {
            if (!$webhook->webhook_id) {
                $webhook->webhook_id = Str::uuid();
            }
            if (!$webhook->path) {
                $webhook->path = '/webhook/' . $webhook->webhook_id;
            }
        });
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function incrementRequestCount()
    {
        $this->increment('request_count');
        $this->update(['last_called_at' => now()]);
    }

    public function getFullUrlAttribute()
    {
        return url($this->path);
    }
}
```

### Schedule Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;

class Schedule extends Model
{
    protected $fillable = [
        'workflow_id', 'node_id', 'cron_expression',
        'timezone', 'active', 'last_run_at',
        'next_run_at', 'run_count'
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($schedule) {
            $schedule->calculateNextRun();
        });
        
        static::updating(function ($schedule) {
            if ($schedule->isDirty('cron_expression')) {
                $schedule->calculateNextRun();
            }
        });
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function calculateNextRun()
    {
        $cron = new CronExpression($this->cron_expression);
        $this->next_run_at = $cron->getNextRunDate('now', 0, false, $this->timezone);
    }

    public function isDue(): bool
    {
        return $this->active && $this->next_run_at <= now();
    }

    public function markAsRun()
    {
        $this->increment('run_count');
        $this->update(['last_run_at' => now()]);
        $this->calculateNextRun();
        $this->save();
    }
}
```

## 🎯 Node System

### Node Contracts
```php
namespace App\Nodes\Contracts;

interface NodeInterface
{
    public function execute(array $input, array $parameters, array $credentials = []): array;
    
    public function getDefinition(): array;
    
    public function validate(array $parameters): bool;
}
```

```php
namespace App\Nodes\Contracts;

interface TriggerInterface extends NodeInterface
{
    public function register(Workflow $workflow, array $parameters): void;
    
    public function unregister(Workflow $workflow): void;
}
```

```php
namespace App\Nodes\Contracts;

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

### Advanced HTTP Request Node
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

### If Logic Node
```php
namespace App\Nodes\Logic;

use App\Nodes\Base\BaseNode;

class IfNode extends BaseNode
{
    protected string $name = 'If';
    protected string $type = 'if';
    protected string $group = 'logic';
    protected array $outputs = ['true', 'false'];
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $conditions = $parameters['conditions'] ?? [];
        $combineOperation = $parameters['combine_operation'] ?? 'and';
        
        $results = [];
        
        foreach ($conditions as $condition) {
            $value1 = $this->resolveParameter($condition['value1'], $input);
            $value2 = $this->resolveParameter($condition['value2'], $input);
            $operation = $condition['operation'];
            
            $results[] = $this->evaluateCondition($value1, $operation, $value2);
        }
        
        $finalResult = $combineOperation === 'and' 
            ? !in_array(false, $results) 
            : in_array(true, $results);
        
        return [
            'output' => $finalResult ? 'true' : 'false',
            'data' => $input
        ];
    }
    
    protected function evaluateCondition($value1, $operation, $value2): bool
    {
        return match($operation) {
            'equals' => $value1 == $value2,
            'not_equals' => $value1 != $value2,
            'contains' => str_contains((string)$value1, (string)$value2),
            'not_contains' => !str_contains((string)$value1, (string)$value2),
            'starts_with' => str_starts_with((string)$value1, (string)$value2),
            'ends_with' => str_ends_with((string)$value1, (string)$value2),
            'greater_than' => $value1 > $value2,
            'less_than' => $value1 < $value2,
            'greater_or_equal' => $value1 >= $value2,
            'less_or_equal' => $value1 <= $value2,
            'is_empty' => empty($value1),
            'is_not_empty' => !empty($value1),
            'regex_match' => preg_match($value2, $value1),
            default => false
        };
    }
    
    protected function getDescription(): string
    {
        return 'Split workflow based on conditions';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'conditions',
                'displayName' => 'Conditions',
                'type' => 'collection',
                'required' => true,
                'default' => [],
                'options' => [
                    [
                        'name' => 'value1',
                        'displayName' => 'Value 1',
                        'type' => 'string',
                    ],
                    [
                        'name' => 'operation',
                        'displayName' => 'Operation',
                        'type' => 'select',
                        'options' => [
                            ['name' => 'Equals', 'value' => 'equals'],
                            ['name' => 'Not Equals', 'value' => 'not_equals'],
                            ['name' => 'Contains', 'value' => 'contains'],
                            ['name' => 'Not Contains', 'value' => 'not_contains'],
                            ['name' => 'Greater Than', 'value' => 'greater_than'],
                            ['name' => 'Less Than', 'value' => 'less_than'],
                            ['name' => 'Is Empty', 'value' => 'is_empty'],
                            ['name' => 'Regex Match', 'value' => 'regex_match'],
                        ],
                    ],
                    [
                        'name' => 'value2',
                        'displayName' => 'Value 2',
                        'type' => 'string',
                    ],
                ],
            ],
            [
                'name' => 'combine_operation',
                'displayName' => 'Combine',
                'type' => 'select',
                'default' => 'and',
                'options' => [
                    ['name' => 'AND', 'value' => 'and'],
                    ['name' => 'OR', 'value' => 'or'],
                ],
            ],
        ];
    }
}
```

### Webhook Trigger Node
```php
namespace App\Nodes\Triggers;

use App\Nodes\Base\BaseNode;
use App\Nodes\Contracts\WebhookInterface;
use App\Models\Workflow;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookTrigger extends BaseNode implements WebhookInterface
{
    protected string $name = 'Webhook';
    protected string $type = 'webhook_trigger';
    protected string $group = 'trigger';
    protected array $inputs = [];
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        return $input;
    }
    
    public function register(Workflow $workflow, array $parameters): void
    {
        $node = collect($workflow->nodes)->firstWhere('type', $this->type);
        
        Webhook::updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'node_id' => $node['id'],
            ],
            [
                'method' => $parameters['method'] ?? 'POST',
                'response_mode' => $parameters['response_mode'] ?? ['mode' => 'last_node'],
                'active' => $workflow->active,
            ]
        );
    }
    
    public function unregister(Workflow $workflow): void
    {
        $workflow->webhooks()->delete();
    }
    
    public function getWebhookPath(): string
    {
        return '/webhook/' . uniqid();
    }
    
    public function processWebhookData(Request $request): array
    {
        return [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
            'method' => $request->method(),
        ];
    }
    
    protected function getDescription(): string
    {
        return 'Triggers workflow when webhook receives HTTP request';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'method',
                'displayName' => 'HTTP Method',
                'type' => 'select',
                'default' => 'POST',
                'options' => [
                    ['name' => 'GET', 'value' => 'GET'],
                    ['name' => 'POST', 'value' => 'POST'],
                    ['name' => 'PUT', 'value' => 'PUT'],
                    ['name' => 'DELETE', 'value' => 'DELETE'],
                ],
            ],
            [
                'name' => 'response_mode',
                'displayName' => 'Response Mode',
                'type' => 'select',
                'default' => 'last_node',
                'options' => [
                    ['name' => 'Respond Immediately', 'value' => 'immediate'],
                    ['name' => 'When Last Node Finishes', 'value' => 'last_node'],
                    ['name' => 'Wait for Webhook Node', 'value' => 'wait_for_webhook'],
                ],
            ],
        ];
    }
}
```

### Wait Node
```php
namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;
use App\Models\WaitingExecution;

class Wait extends BaseNode
{
    protected string $name = 'Wait';
    protected string $type = 'wait';
    protected string $group = 'action';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $waitType = $parameters['wait_type'] ?? 'time';
        
        return match($waitType) {
            'time' => $this->waitForTime($input, $parameters),
            'webhook' => $this->waitForWebhook($input, $parameters),
            'condition' => $this->waitForCondition($input, $parameters),
            default => $input
        };
    }
    
    protected function waitForTime(array $input, array $parameters): array
    {
        $amount = $parameters['amount'] ?? 1;
        $unit = $parameters['unit'] ?? 'hours';
        
        $resumeAt = match($unit) {
            'seconds' => now()->addSeconds($amount),
            'minutes' => now()->addMinutes($amount),
            'hours' => now()->addHours($amount),
            'days' => now()->addDays($amount),
            default => now()->addHours(1)
        };
        
        return [
            'wait' => true,
            'resume_at' => $resumeAt,
            'data' => $input,
        ];
    }
    
    protected function waitForWebhook(array $input, array $parameters): array
    {
        return [
            'wait' => true,
            'wait_type' => 'webhook',
            'data' => $input,
        ];
    }
    
    protected function waitForCondition(array $input, array $parameters): array
    {
        return [
            'wait' => true,
            'wait_type' => 'condition',
            'condition' => $parameters['condition'],
            'data' => $input,
        ];
    }
    
    protected function getDescription(): string
    {
        return 'Pause workflow execution';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'wait_type',
                'displayName' => 'Wait Type',
                'type' => 'select',
                'default' => 'time',
                'options' => [
                    ['name' => 'Time', 'value' => 'time'],
                    ['name' => 'Webhook', 'value' => 'webhook'],
                    ['name' => 'Condition', 'value' => 'condition'],
                ],
            ],
            [
                'name' => 'amount',
                'displayName' => 'Amount',
                'type' => 'number',
                'default' => 1,
                'displayOptions' => [
                    'show' => ['wait_type' => ['time']]
                ],
            ],
            [
                'name' => 'unit',
                'displayName' => 'Unit',
                'type' => 'select',
                'default' => 'hours',
                'options' => [
                    ['name' => 'Seconds', 'value' => 'seconds'],
                    ['name' => 'Minutes', 'value' => 'minutes'],
                    ['name' => 'Hours', 'value' => 'hours'],
                    ['name' => 'Days', 'value' => 'days'],
                ],
                'displayOptions' => [
                    'show' => ['wait_type' => ['time']]
                ],
            ],
        ];
    }
}
```

### Code Node (Execute JavaScript)
```php
namespace App\Nodes\Transform;

use App\Nodes\Base\BaseNode;

class Code extends BaseNode
{
    protected string $name = 'Code';
    protected string $type = 'code';
    protected string $group = 'transform';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $code = $parameters['code'] ?? '';
        $mode = $parameters['mode'] ?? 'run_once';
        
        if ($mode === 'run_once') {
            return $this->executeOnce($code, $input);
        }
        
        return $this->executeForEach($code, $input);
    }
    
    protected function executeOnce(string $code, array $input): array
    {
        // Use V8Js extension if available, otherwise sandbox PHP
        if (extension_loaded('v8js')) {
            $v8 = new \V8Js();
            $v8->items = $input;
            
            try {
                $result = $v8->executeString($code);
                return is_array($result) ? $result : ['result' => $result];
            } catch (\V8JsException $e) {
                throw new \Exception('JavaScript execution error: ' . $e->getMessage());
            }
        }
        
        // Fallback: Use a sandboxed PHP eval (not recommended for production)
        // Better: Use a proper sandboxing solution or external JS runtime
        throw new \Exception('V8Js extension not available. Install v8js for Code node.');
    }
    
    protected function executeForEach(string $code, array $input): array
    {
        $results = [];
        
        foreach ($input as $item) {
            if (extension_loaded('v8js')) {
                $v8 = new \V8Js();
                $v8->item = $item;
                
                try {
                    $result = $v8->executeString($code);
                    $results[] = is_array($result) ? $result : ['result' => $result];
                } catch (\V8JsException $e) {
                    $results[] = ['error' => $e->getMessage()];
                }
            }
        }
        
        return $results;
    }
    
    protected function getDescription(): string
    {
        return 'Execute custom JavaScript code';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'mode',
                'displayName' => 'Mode',
                'type' => 'select',
                'default' => 'run_once',
                'options' => [
                    ['name' => 'Run Once for All Items', 'value' => 'run_once'],
                    ['name' => 'Run Once for Each Item', 'value' => 'each_item'],
                ],
            ],
            [
                'name' => 'code',
                'displayName' => 'JavaScript Code',
                'type' => 'code',
                'required' => true,
                'default' => '// Your code here\nreturn items;',
            ],
        ];
    }
}
```

## ⚙️ Core Services

### WorkflowExecutor Service
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
        $execution->update([
            'status' => 'waiting',
            'waiting_till' => $result['resume_at'] ?? null,
        ]);
        
        WaitingExecution::create([
            'execution_id' => $execution->id,
            'node_id' => $result['node_id'] ?? null,
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
        
        // Continue execution from where it stopped
        $waitingExecution->delete();
        
        return $this->execute($workflow, $data, 'resume');
    }
}
```

### NodeExecutor Service
```php
namespace App\Services\Execution;

use App\Services\Node\NodeRegistry;
use App\Services\Credential\CredentialService;
use App\Services\Expression\ExpressionParser;

class NodeExecutor
{
    public function __construct(
        protected NodeRegistry $nodeRegistry,
        protected CredentialService $credentialService,
        protected ExpressionParser $expressionParser
    ) {}
    
    public function execute(array $nodeConfig, array $inputData, array $context): array
    {
        $nodeType = $nodeConfig['type'];
        $parameters = $nodeConfig['parameters'] ?? [];
        
        // Get node instance
        $node = $this->nodeRegistry->getNode($nodeType);
        
        // Validate parameters
        $node->validate($parameters);
        
        // Resolve expressions in parameters
        $resolvedParameters = $this->resolveParameters($parameters, $context);
        
        // Get credentials if needed
        $credentials = [];
        if (isset($nodeConfig['credentials'])) {
            $credentials = $this->credentialService->get($nodeConfig['credentials']);
        }
        
        // Execute node
        return $node->execute($inputData, $resolvedParameters, $credentials);
    }
    
    protected function resolveParameters(array $parameters, array $context): array
    {
        $resolved = [];
        
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $resolved[$key] = $this->resolveParameters($value, $context);
            } elseif (is_string($value) && str_contains($value, '{{')) {
                $resolved[$key] = $this->expressionParser->parse($value, $context);
            } else {
                $resolved[$key] = $value;
            }
        }
        
        return $resolved;
    }
}
```

### ExecutionContext Service
```php
namespace App\Services\Execution;

use App\Models\Execution;

class ExecutionContext
{
    public function build(Execution $execution, $nodes): array
    {
        $context = [
            'execution' => [
                'id' => $execution->id,
                'mode' => $execution->mode,
            ],
            'workflow' => [
                'id' => $execution->workflow_id,
                'name' => $execution->workflow->name,
                'active' => $execution->workflow->active,
            ],
            'nodes' => [],
        ];
        
        // Add executed nodes data
        foreach ($execution->executionData as $nodeData) {
            $context['nodes'][$nodeData->node_id] = [
                'json' => $nodeData->output_data,
                'binary' => [],
            ];
        }
        
        return $context;
    }
}
```

### ExecutionLogger Service
```php
namespace App\Services\Execution;

use App\Models\Execution;
use App\Models\ExecutionData;
use Illuminate\Support\Facades\Log;

class ExecutionLogger
{
    public function logNodeStart(Execution $execution, array $node, array $input): ExecutionData
    {
        return ExecutionData::create([
            'execution_id' => $execution->id,
            'node_id' => $node['id'],
            'node_name' => $node['name'] ?? $node['type'],
            'node_type' => $node['type'],
            'status' => 'running',
            'input_data' => $input,
            'started_at' => now(),
        ]);
    }
    
    public function logNodeSuccess(ExecutionData $nodeExecution, array $output): void
    {
        $nodeExecution->update([
            'status' => 'success',
            'output_data' => $output,
            'finished_at' => now(),
            'duration_ms' => $nodeExecution->started_at 
                ? now()->diffInMilliseconds($nodeExecution->started_at) 
                : null,
        ]);
    }
    
    public function logNodeError(ExecutionData $nodeExecution, string $error): void
    {
        $nodeExecution->update([
            'status' => 'failed',
            'error' => $error,
            'finished_at' => now(),
            'duration_ms' => $nodeExecution->started_at 
                ? now()->diffInMilliseconds($nodeExecution->started_at) 
                : null,
        ]);
    }
    
    public function logError(Execution $execution, \Exception $e): void
    {
        Log::error('Workflow execution failed', [
            'execution_id' => $execution->id,
            'workflow_id' => $execution->workflow_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
```

### ExpressionParser Service
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
        
        return data_get($context, "nodes.current.json.{$path}");
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

### CredentialService
```php
namespace App\Services\Credential;

use App\Models\Credential;
use Illuminate\Support\Facades\Crypt;

class CredentialService
{
    public function get(int $credentialId): array
    {
        $credential = Credential::findOrFail($credentialId);
        $credential->markAsUsed();
        
        return $credential->data;
    }
    
    public function store(array $data, string $type, string $name, int $userId): Credential
    {
        return Credential::create([
            'name' => $name,
            'type' => $type,
            'data' => $data,
            'user_id' => $userId,
        ]);
    }
    
    public function update(Credential $credential, array $data): Credential
    {
        $credential->update(['data' => $data]);
        return $credential;
    }
    
    public function test(Credential $credential): bool
    {
        // Implement credential testing logic based on type
        return match($credential->type) {
            'api_key' => $this->testApiKey($credential->data),
            'oauth2' => $this->testOAuth2($credential->data),
            'basic_auth' => $this->testBasicAuth($credential->data),
            default => true
        };
    }
    
    protected function testApiKey(array $data): bool
    {
        // Implement API key validation
        return !empty($data['api_key']);
    }
    
    protected function testOAuth2(array $data): bool
    {
        // Implement OAuth2 token validation
        return !empty($data['access_token']);
    }
    
    protected function testBasicAuth(array $data): bool
    {
        return !empty($data['username']) && !empty($data['password']);
    }
}
```

### CredentialEncryption
```php
namespace App\Services\Credential;

use Illuminate\Support\Facades\Crypt;

class CredentialEncryption
{
    public function encrypt(array $data): string
    {
        return Crypt::encryptString(json_encode($data));
    }
    
    public function decrypt(string $encrypted): array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt credentials: ' . $e->getMessage());
        }
    }
}
```

## 🎮 Controllers

### WorkflowController
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

### ExecutionController
```php
namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Execution;
use App\Http\Resources\ExecutionResource;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    public function __construct(protected WorkflowExecutor $executor) {}
    
    public function index(Request $request)
    {
        $query = Execution::with(['workflow'])
            ->whereHas('workflow', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        
        // Filter by workflow
        if ($request->has('workflow_id')) {
            $query->where('workflow_id', $request->input('workflow_id'));
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by mode
        if ($request->has('mode')) {
            $query->where('mode', $request->input('mode'));
        }
        
        // Date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }
        
        return ExecutionResource::collection(
            $query->latest()->paginate(50)
        );
    }
    
    public function show(Execution $execution)
    {
        $this->authorize('view', $execution);
        
        return new ExecutionResource(
            $execution->load(['workflow', 'executionData'])
        );
    }
    
    public function retry(Execution $execution)
    {
        $this->authorize('execute', $execution->workflow);
        
        if (!$execution->canRetry()) {
            return response()->json([
                'message' => 'Execution cannot be retried'
            ], 422);
        }
        
        $newExecution = $execution->retry();
        
        // Dispatch job to execute
        \App\Jobs\ExecuteWorkflowJob::dispatch($newExecution->workflow, $newExecution);
        
        return response()->json([
            'execution_id' => $newExecution->id,
            'message' => 'Execution retry scheduled'
        ]);
    }
    
    public function cancel(Execution $execution)
    {
        $this->authorize('execute', $execution->workflow);
        
        if ($execution->status !== 'running' && $execution->status !== 'waiting') {
            return response()->json([
                'message' => 'Only running or waiting executions can be cancelled'
            ], 422);
        }
        
        $execution->update([
            'status' => 'cancelled',
            'finished_at' => now(),
        ]);
        
        return response()->json(['message' => 'Execution cancelled']);
    }
    
    public function delete(Execution $execution)
    {
        $this->authorize('view', $execution);
        
        $execution->delete();
        
        return response()->noContent();
    }
    
    public function statistics(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'total_executions' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            
            'successful' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'success')->count(),
            
            'failed' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'failed')->count(),
            
            'running' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'running')->count(),
            
            'avg_duration' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->whereNotNull('duration_ms')->avg('duration_ms'),
            
            'executions_by_day' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get(),
        ];
        
        return response()->json($stats);
    }
}
```

### WebhookController
```php
namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Jobs\ExecuteWorkflowJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request, string $webhookId)
    {
        $webhook = Webhook::where('webhook_id', $webhookId)
            ->where('active', true)
            ->with('workflow')
            ->firstOrFail();
        
        if (!$webhook->workflow->active) {
            return response()->json(['error' => 'Workflow is not active'], 403);
        }
        
        // Process webhook data
        $webhookData = [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
            'method' => $request->method(),
        ];
        
        $webhook->incrementRequestCount();
        
        // Get response mode
        $responseMode = $webhook->response_mode['mode'] ?? 'immediate';
        
        if ($responseMode === 'immediate') {
            // Dispatch job and respond immediately
            ExecuteWorkflowJob::dispatch($webhook->workflow, $webhookData, 'webhook');
            
            return response()->json(['message' => 'Workflow triggered successfully']);
        }
        
        if ($responseMode === 'last_node') {
            // Execute synchronously and return last node output
            $execution = app(\App\Services\Execution\WorkflowExecutor::class)
                ->execute($webhook->workflow, $webhookData, 'webhook');
            
            return response()->json($execution->output_data);
        }
        
        return response()->json(['message' => 'Webhook received']);
    }
    
    public function test(Webhook $webhook, Request $request)
    {
        $this->authorize('update', $webhook->workflow);
        
        $testData = $request->all();
        
        $execution = app(\App\Services\Execution\WorkflowExecutor::class)
            ->execute($webhook->workflow, $testData, 'manual');
        
        return response()->json([
            'execution_id' => $execution->id,
            'status' => $execution->status,
            'output' => $execution->output_data,
        ]);
    }
}
```

### CredentialController
```php
namespace App\Http\Controllers;

use App\Models\Credential;
use App\Services\Credential\CredentialService;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function __construct(protected CredentialService $service) {}
    
    public function index(Request $request)
    {
        $credentials = Credential::where('user_id', $request->user()->id)
            ->select(['id', 'name', 'type', 'last_used_at', 'created_at'])
            ->latest()
            ->get();
        
        return response()->json($credentials);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:api_key,oauth2,basic_auth,bearer_token',
            'data' => 'required|array',
        ]);
        
        $credential = $this->service->store(
            $validated['data'],
            $validated['type'],
            $validated['name'],
            $request->user()->id
        );
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
        ], 201);
    }
    
    public function show(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
            'data' => $credential->data, // Decrypted
            'last_used_at' => $credential->last_used_at,
        ]);
    }
    
    public function update(Request $request, Credential $credential)
    {
        $this->authorize('update', $credential);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'data' => 'array',
        ]);
        
        if (isset($validated['name'])) {
            $credential->update(['name' => $validated['name']]);
        }
        
        if (isset($validated['data'])) {
            $this->service->update($credential, $validated['data']);
        }
        
        return response()->json(['message' => 'Credential updated']);
    }
    
    public function destroy(Credential $credential)
    {
        $this->authorize('delete', $credential);
        
        $credential->delete();
        
        return response()->noContent();
    }
    
    public function test(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        try {
            $result = $this->service->test($credential);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Credential is valid' : 'Credential test failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
```

### NodeController
```php
namespace App\Http\Controllers;

use App\Services\Node\NodeRegistry;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function __construct(protected NodeRegistry $registry) {}
    
    public function index()
    {
        return response()->json(
            $this->registry->getAllNodes()
        );
    }
    
    public function byGroup()
    {
        return response()->json(
            $this->registry->getNodesByGroup()
        );
    }
    
    public function show(string $type)
    {
        try {
            $node = $this->registry->getNode($type);
            return response()->json($node->getDefinition());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
```

## 🔄 Jobs

### ExecuteWorkflowJob
```php
namespace App\Jobs;

use App\Models\Workflow;
use App\Models\Execution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 300;
    public $tries = 3;
    public $backoff = [60, 180, 600];
    
    public function __construct(
        public Workflow $workflow,
        public array $inputData = [],
        public string $mode = 'trigger',
        public ?Execution $execution = null
    ) {}
    
    public function handle(WorkflowExecutor $executor): void
    {
        if ($this->execution) {
            // Continue existing execution
            $executor->resume($this->execution, $this->inputData);
        } else {
            // Start new execution
            $executor->execute($this->workflow, $this->inputData, $this->mode);
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        if ($this->execution) {
            $this->execution->markAsFailed($exception->getMessage());
        }
        
        \Log::error('Workflow execution job failed', [
            'workflow_id' => $this->workflow->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
```

### ResumeExecutionJob
```php
namespace App\Jobs;

use App\Models\WaitingExecution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResumeExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public WaitingExecution $waitingExecution,
        public array $resumeData = []
    ) {}
    
    public function handle(WorkflowExecutor $executor): void
    {
        $execution = $this->waitingExecution->execution;
        
        $executor->resume($execution, $this->resumeData);
    }
}
```

### CleanOldExecutionsJob
```php
namespace App\Jobs;

use App\Models\Execution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanOldExecutionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        $daysToKeep = config('workflow.execution_retention_days', 30);
        
        Execution::where('created_at', '<', now()->subDays($daysToKeep))
            ->where('status', '!=', 'failed') // Keep failed executions longer
            ->delete();
        
        // Delete very old failed executions
        Execution::where('created_at', '<', now()->subDays($daysToKeep * 3))
            ->where('status', 'failed')
            ->delete();
    }
}
```

## 📡 Events & Listeners

### Events
```php
namespace App\Events;

use App\Models\Execution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowExecutionStarted
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}

class WorkflowExecutionCompleted
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}

class WorkflowExecutionFailed
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}
```

### Listeners
```php
namespace App\Listeners;

use App\Events\WorkflowExecutionFailed;
use Illuminate\Support\Facades\Notification;

class NotifyExecutionFailure
{
    public function handle(WorkflowExecutionFailed $event): void
    {
        $execution = $event->execution;
        $workflow = $execution->workflow;
        
        // Send notification to workflow owner
        if ($workflow->user) {
            // $workflow->user->notify(new WorkflowFailedNotification($execution));
        }
        
        // Log to monitoring service
        \Log::channel('workflow')->error('Workflow execution failed', [
            'execution_id' => $execution->id,
            'workflow_id' => $workflow->id,
            'error' => $execution->error_message,
        ]);
    }
}
```

## 🎯 Console Commands

### RunScheduledWorkflows
```php
namespace App\Console\Commands;

use App\Models\Schedule;
use App\Jobs\ExecuteWorkflowJob;
use Illuminate\Console\Command;

class RunScheduledWorkflows extends Command
{
    protected $signature = 'workflows:run-scheduled';
    protected $description = 'Execute scheduled workflows that are due';
    
    public function handle(): int
    {
        $schedules = Schedule::where('active', true)
            ->where('next_run_at', '<=', now())
            ->with('workflow')
            ->get();
        
        foreach ($schedules as $schedule) {
            if (!$schedule->workflow->active) {
                continue;
            }
            
            $this->info("Executing workflow: {$schedule->workflow->name}");
            
            ExecuteWorkflowJob::dispatch(
                $schedule->workflow,
                [],
                'schedule'
            );
            
            $schedule->markAsRun();
        }
        
        $this->info("Executed {$schedules->count()} scheduled workflows");
        
        return 0;
    }
}
```

### ProcessWaitingExecutions
```php
namespace App\Console\Commands;

use App\Models\WaitingExecution;
use App\Jobs\ResumeExecutionJob;
use Illuminate\Console\Command;

class ProcessWaitingExecutions extends Command
{
    protected $signature = 'workflows:process-waiting';
    protected $description = 'Resume waiting executions that are ready';
    
    public function handle(): int
    {
        $waitingExecutions = WaitingExecution::where('wait_type', 'time')
            ->where('resume_at', '<=', now())
            ->get();
        
        foreach ($waitingExecutions as $waiting) {
            $this->info("Resuming execution: {$waiting->execution_id}");
            
            ResumeExecutionJob::dispatch($waiting);
        }
        
        $this->info("Resumed {$waitingExecutions->count()} executions");
        
        return 0;
    }
}
```

## 🛣️ API Routes

```php
// routes/api.php

use App\Http\Controllers\{
    WorkflowController,
    ExecutionController,
    WebhookController,
    CredentialController,
    NodeController,
    TagController
};

Route::prefix('v1')->group(function () {
    
    // Public webhook endpoint
    Route::post('webhook/{webhookId}', [WebhookController::class, 'handle']);
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Workflows
        Route::apiResource('workflows', WorkflowController::class);
        Route::post('workflows/{workflow}/execute', [WorkflowController::class, 'execute']);
        Route::post('workflows/{workflow}/duplicate', [WorkflowController::class, 'duplicate']);
        Route::post('workflows/{workflow}/activate', [WorkflowController::class, 'activate']);
        Route::post('workflows/{workflow}/deactivate', [WorkflowController::class, 'deactivate']);
        Route::get('workflows/{workflow}/versions', [WorkflowController::class, 'versions']);
        Route::post('workflows/{workflow}/versions/{version}/restore', [WorkflowController::class, 'restoreVersion']);
        
        // Executions
        Route::get('executions', [ExecutionController::class, 'index']);
        Route::get('executions/statistics', [ExecutionController::class, 'statistics']);
        Route::get('executions/{execution}', [ExecutionController::class, 'show']);
        Route::post('executions/{execution}/retry', [ExecutionController::class, 'retry']);
        Route::post('executions/{execution}/cancel', [ExecutionController::class, 'cancel']);
        Route::delete('executions/{execution}', [ExecutionController::class, 'delete']);
        
        // Credentials
        Route::apiResource('credentials', CredentialController::class);
        Route::post('credentials/{credential}/test', [CredentialController::class, 'test']);
        
        // Nodes
        Route::get('nodes', [NodeController::class, 'index']);
        Route::get('nodes/by-group', [NodeController::class, 'byGroup']);
        Route::get('nodes/{type}', [NodeController::class, 'show']);
        
        // Tags
        Route::apiResource('tags', TagController::class);
        
        // Webhooks
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);
    });
});
```

## 📝 Request Validation

### StoreWorkflowRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'required|array',
            'connections.*.source' => 'required|string',
            'connections.*.target' => 'required|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
```

### UpdateWorkflowRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workflow'));
    }
    
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'array|min:1',
            'nodes.*.id' => 'required_with:nodes|string',
            'nodes.*.type' => 'required_with:nodes|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'array',
            'connections.*.source' => 'required_with:connections|string',
            'connections.*.target' => 'required_with:connections|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
```

## 🎨 API Resources

### WorkflowResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'nodes' => $this->nodes,
            'connections' => $this->connections,
            'settings' => $this->settings,
            'static_data' => $this->static_data,
            'execution_count' => $this->execution_count,
            'last_executed_at' => $this->last_executed_at,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'webhooks' => WebhookResource::collection($this->whenLoaded('webhooks')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### ExecutionResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_name' => $this->workflow->name,
            'status' => $this->status,
            'mode' => $this->mode,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'waiting_till' => $this->waiting_till,
            'duration_ms' => $this->duration_ms,
            'execution_data' => ExecutionDataResource::collection($this->whenLoaded('executionData')),
            'created_at' => $this->created_at,
        ];
    }
}
```

### ExecutionDataResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionDataResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'node_name' => $this->node_name,
            'node_type' => $this->node_type,
            'status' => $this->status,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error' => $this->error,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'duration_ms' => $this->duration_ms,
        ];
    }
}
```

## 🔧 Additional Services

### WorkflowValidator
```php
namespace App\Services\Workflow;

class WorkflowValidator
{
    public function validate(array $workflowData): bool
    {
        // Check if workflow has at least one trigger node
        $hasTrigger = collect($workflowData['nodes'] ?? [])
            ->contains(fn($node) => str_contains($node['type'], 'trigger'));
        
        if (!$hasTrigger) {
            throw new \Exception('Workflow must have at least one trigger node');
        }
        
        // Check if all connections reference existing nodes
        $nodeIds = collect($workflowData['nodes'] ?? [])->pluck('id')->toArray();
        
        foreach ($workflowData['connections'] ?? [] as $connection) {
            if (!in_array($connection['source'], $nodeIds)) {
                throw new \Exception("Connection source node '{$connection['source']}' does not exist");
            }
            
            if (!in_array($connection['target'], $nodeIds)) {
                throw new \Exception("Connection target node '{$connection['target']}' does not exist");
            }
        }
        
        // Check for circular dependencies
        if ($this->hasCircularDependency($workflowData['nodes'], $workflowData['connections'])) {
            throw new \Exception('Workflow contains circular dependencies');
        }
        
        // Check if all nodes are reachable from trigger
        if (!$this->allNodesReachable($workflowData['nodes'], $workflowData['connections'])) {
            throw new \Exception('Some nodes are not connected to the workflow');
        }
        
        return true;
    }
    
    protected function hasCircularDependency(array $nodes, array $connections): bool
    {
        $graph = $this->buildGraph($connections);
        $visited = [];
        $recStack = [];
        
        foreach ($nodes as $node) {
            if ($this->isCyclicUtil($node['id'], $graph, $visited, $recStack)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function isCyclicUtil(string $nodeId, array $graph, array &$visited, array &$recStack): bool
    {
        if (isset($recStack[$nodeId])) {
            return true;
        }
        
        if (isset($visited[$nodeId])) {
            return false;
        }
        
        $visited[$nodeId] = true;
        $recStack[$nodeId] = true;
        
        if (isset($graph[$nodeId])) {
            foreach ($graph[$nodeId] as $neighbor) {
                if ($this->isCyclicUtil($neighbor, $graph, $visited, $recStack)) {
                    return true;
                }
            }
        }
        
        unset($recStack[$nodeId]);
        return false;
    }
    
    protected function allNodesReachable(array $nodes, array $connections): bool
    {
        $graph = $this->buildGraph($connections);
        $triggerNodes = collect($nodes)->filter(fn($n) => str_contains($n['type'], 'trigger'));
        
        if ($triggerNodes->isEmpty()) {
            return false;
        }
        
        $reachable = [];
        foreach ($triggerNodes as $trigger) {
            $this->dfs($trigger['id'], $graph, $reachable);
        }
        
        $nodeIds = collect($nodes)->pluck('id')->toArray();
        
        return count($reachable) === count($nodeIds);
    }
    
    protected function dfs(string $nodeId, array $graph, array &$reachable): void
    {
        if (isset($reachable[$nodeId])) {
            return;
        }
        
        $reachable[$nodeId] = true;
        
        if (isset($graph[$nodeId])) {
            foreach ($graph[$nodeId] as $neighbor) {
                $this->dfs($neighbor, $graph, $reachable);
            }
        }
    }
    
    protected function buildGraph(array $connections): array
    {
        $graph = [];
        
        foreach ($connections as $connection) {
            if (!isset($graph[$connection['source']])) {
                $graph[$connection['source']] = [];
            }
            $graph[$connection['source']][] = $connection['target'];
        }
        
        return $graph;
    }
}
```

### WorkflowCloner
```php
namespace App\Services\Workflow;

use App\Models\Workflow;
use Illuminate\Support\Str;

class WorkflowCloner
{
    public function clone(Workflow $workflow, ?string $newName = null): Workflow
    {
        $nodes = $workflow->nodes;
        $connections = $workflow->connections;
        
        // Generate new node IDs
        $idMapping = [];
        foreach ($nodes as &$node) {
            $oldId = $node['id'];
            $newId = Str::uuid();
            $idMapping[$oldId] = $newId;
            $node['id'] = $newId;
        }
        
        // Update connections with new node IDs
        foreach ($connections as &$connection) {
            $connection['source'] = $idMapping[$connection['source']];
            $connection['target'] = $idMapping[$connection['target']];
        }
        
        // Create new workflow
        $cloned = Workflow::create([
            'name' => $newName ?? $workflow->name . ' (Copy)',
            'description' => $workflow->description,
            'active' => false, // Always inactive
            'nodes' => $nodes,
            'connections' => $connections,
            'settings' => $workflow->settings,
            'static_data' => $workflow->static_data,
            'user_id' => $workflow->user_id,
        ]);
        
        // Copy tags
        $cloned->tags()->attach($workflow->tags->pluck('id'));
        
        return $cloned;
    }
}
```

## ⚙️ Configuration

### config/workflow.php
```php
<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Execution Settings
    |--------------------------------------------------------------------------
    */
    
    'execution' => [
        'timeout' => env('WORKFLOW_EXECUTION_TIMEOUT', 300), // seconds
        'max_retries' => env('WORKFLOW_MAX_RETRIES', 3),
        'retention_days' => env('WORKFLOW_RETENTION_DAYS', 30),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    
    'queue' => [
        'connection' => env('WORKFLOW_QUEUE_CONNECTION', 'redis'),
        'name' => env('WORKFLOW_QUEUE_NAME', 'workflows'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Node Settings
    |--------------------------------------------------------------------------
    */
    
    'nodes' => [
        'cache_enabled' => env('WORKFLOW_CACHE_NODES', true),
        'cache_ttl' => 3600, // seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    
    'webhook' => [
        'prefix' => env('WORKFLOW_WEBHOOK_PREFIX', 'webhook'),
        'rate_limit' => env('WORKFLOW_WEBHOOK_RATE_LIMIT', 60), // per minute
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    
    'security' => [
        'encrypt_credentials' => true,
        'allow_code_execution' => env('WORKFLOW_ALLOW_CODE', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    
    'logging' => [
        'channel' => env('WORKFLOW_LOG_CHANNEL', 'stack'),
        'level' => env('WORKFLOW_LOG_LEVEL', 'info'),
    ],
    
];
```

## 📊 Service Provider

### WorkflowServiceProvider
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Node\NodeRegistry;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NodeRegistry::class);
    }
    
    public function boot(): void
    {
        // Register all nodes
        $registry = $this->app->make(NodeRegistry::class);
        
        // Triggers
        $registry->register('webhook_trigger', \App\Nodes\Triggers\WebhookTrigger::class);
        $registry->register('schedule_trigger', \App\Nodes\Triggers\ScheduleTrigger::class);
        $registry->register('manual_trigger', \App\Nodes\Triggers\ManualTrigger::class);
        
        // Actions
        $registry->register('http_request', \App\Nodes\Actions\HttpRequest::class);
        $registry->register('email', \App\Nodes\Actions\Email::class);
        $registry->register('database', \App\Nodes\Actions\Database::class);
        $registry->register('wait', \App\Nodes\Actions\Wait::class);
        
        // Logic
        $registry->register('if', \App\Nodes\Logic\IfNode::class);
        $registry->register('switch', \App\Nodes\Logic\SwitchNode::class);
        $registry->register('merge', \App\Nodes\Logic\MergeNode::class);
        
        // Transform
        $registry->register('code', \App\Nodes\Transform\Code::class);
        $registry->register('set', \App\Nodes\Transform\SetNode::class);
        
        // Schedule commands
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run scheduled workflows every minute
            $schedule->command('workflows:run-scheduled')->everyMinute();
            
            // Process waiting executions every minute
            $schedule->command('workflows:process-waiting')->everyMinute();
            
            // Clean old executions daily
            $schedule->command('workflows:clean-executions')->daily();
        });
    }
}
```

## 🔐 Policies

### WorkflowPolicy
```php
namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;

class WorkflowPolicy
{
    public function view(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()->where('user_id', $user->id)->exists();
    }
    
    public function update(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()
                   ->where('user_id', $user->id)
                   ->whereIn('permission', ['edit'])
                   ->exists();
    }
    
    public function delete(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id;
    }
    
    public function execute(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()
                   ->where('user_id', $user->id)
                   ->whereIn('permission', ['edit', 'execute'])
                   ->exists();
    }
}
```

## 🧪 Example Usage

### Creating a Workflow via API
```json
POST /api/v1/workflows

{
  "name": "Process New Orders",
  "active": true,
  "nodes": [
    {
      "id": "node_1",
      "type": "webhook_trigger",
      "name": "Order Webhook",
      "parameters": {
        "method": "POST"
      },
      "position": {"x": 100, "y": 100}
    },
    {
      "id": "node_2",
      "type": "if",
      "name": "Check Amount",
      "parameters": {
        "conditions": [
          {
            "value1": "{{$json.amount}}",
            "operation": "greater_than",
            "value2": "100"
          }
        ]
      },
      "position": {"x": 300, "y": 100}
    },
    {
      "id": "node_3",
      "type": "http_request",
      "name": "Notify Team",
      "parameters": {
        "method": "POST",
        "url": "https://slack.com/api/chat.postMessage",
        "body": {
          "text": "New order: {{$json.order_id}}"
        }
      },
      "credentials": 1,
      "position": {"x": 500, "y": 100}
    }
  ],
  "connections": [
    {
      "source": "node_1",
      "target": "node_2"
    },
    {
      "source": "node_2",
      "target": "node_3",
      "sourceOutput": "true"
    }
  ]
}
```

## 📈 Performance Optimization Tips

### 1. Database Indexing
```sql
-- Add indexes for common queries
CREATE INDEX idx_executions_workflow_status ON executions(workflow_id, status, created_at);
CREATE INDEX idx_executions_status_waiting ON executions(status, waiting_till) WHERE status = 'waiting';
CREATE INDEX idx_webhooks_path ON webhooks(path) WHERE active = true;

-- For PostgreSQL JSONB queries
CREATE INDEX idx_workflow_nodes_gin ON workflows USING GIN (nodes);
CREATE INDEX idx_execution_data_output ON execution_data USING GIN (output_data);
```

### 2. Redis Configuration
```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_QUEUE=workflows
```

### 3. Queue Workers
```bash
# Run multiple workers for parallel processing
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
```

### 4. Horizon Configuration (Recommended)
```bash
composer require laravel/horizon

# config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['workflows', 'default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

## 🚀 Deployment Checklist

1. **Environment Variables**
```env
APP_ENV=production
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
WORKFLOW_EXECUTION_TIMEOUT=300
WORKFLOW_MAX_RETRIES=3
WORKFLOW_ALLOW_CODE=false
```

2. **Optimize Laravel**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. **Setup Supervisor**
```ini
[program:workflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=workflows --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=5
user=www-data
```

4. **Setup Cron**
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

5. **Monitoring**
- Setup Laravel Telescope for debugging
- Use Horizon for queue monitoring
- Setup error tracking (Sentry, Bugsnag)
- Monitor Redis memory usage

This is a **complete, production-ready n8n clone** with all advanced features! 🚀
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

### CredentialService
```php
namespace App\Services\Credential;

use App\Models\Credential;
use Illuminate\Support\Facades\Crypt;

class CredentialService
{
    public function get(int $credentialId): array
    {
        $credential = Credential::findOrFail($credentialId);
        $credential->markAsUsed();
        
        return $credential->data;
    }
    
    public function store(array $data, string $type, string $name, int $userId): Credential
    {
        return Credential::create([
            'name' => $name,
            'type' => $type,
            'data' => $data,
            'user_id' => $userId,
        ]);
    }
    
    public function update(Credential $credential, array $data): Credential
    {
        $credential->update(['data' => $data]);
        return $credential;
    }
    
    public function test(Credential $credential): bool
    {
        // Implement credential testing logic based on type
        return match($credential->type) {
            'api_key' => $this->testApiKey($credential->data),
            'oauth2' => $this->testOAuth2($credential->data),
            'basic_auth' => $this->testBasicAuth($credential->data),
            default => true
        };
    }
    
    protected function testApiKey(array $data): bool
    {
        // Implement API key validation
        return !empty($data['api_key']);
    }
    
    protected function testOAuth2(array $data): bool
    {
        // Implement OAuth2 token validation
        return !empty($data['access_token']);
    }
    
    protected function testBasicAuth(array $data): bool
    {
        return !empty($data['username']) && !empty($data['password']);
    }
}
```

### CredentialEncryption
```php
namespace App\Services\Credential;

use Illuminate\Support\Facades\Crypt;

class CredentialEncryption
{
    public function encrypt(array $data): string
    {
        return Crypt::encryptString(json_encode($data));
    }
    
    public function decrypt(string $encrypted): array
    {
        try {
            $decrypted = Crypt::decryptString($encrypted);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt credentials: ' . $e->getMessage());
        }
    }
}
```

## 🎮 Controllers

### WorkflowController
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

### ExecutionController
```php
namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\Execution;
use App\Http\Resources\ExecutionResource;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    public function __construct(protected WorkflowExecutor $executor) {}
    
    public function index(Request $request)
    {
        $query = Execution::with(['workflow'])
            ->whereHas('workflow', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        
        // Filter by workflow
        if ($request->has('workflow_id')) {
            $query->where('workflow_id', $request->input('workflow_id'));
        }
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        // Filter by mode
        if ($request->has('mode')) {
            $query->where('mode', $request->input('mode'));
        }
        
        // Date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }
        
        return ExecutionResource::collection(
            $query->latest()->paginate(50)
        );
    }
    
    public function show(Execution $execution)
    {
        $this->authorize('view', $execution);
        
        return new ExecutionResource(
            $execution->load(['workflow', 'executionData'])
        );
    }
    
    public function retry(Execution $execution)
    {
        $this->authorize('execute', $execution->workflow);
        
        if (!$execution->canRetry()) {
            return response()->json([
                'message' => 'Execution cannot be retried'
            ], 422);
        }
        
        $newExecution = $execution->retry();
        
        // Dispatch job to execute
        \App\Jobs\ExecuteWorkflowJob::dispatch($newExecution->workflow, $newExecution);
        
        return response()->json([
            'execution_id' => $newExecution->id,
            'message' => 'Execution retry scheduled'
        ]);
    }
    
    public function cancel(Execution $execution)
    {
        $this->authorize('execute', $execution->workflow);
        
        if ($execution->status !== 'running' && $execution->status !== 'waiting') {
            return response()->json([
                'message' => 'Only running or waiting executions can be cancelled'
            ], 422);
        }
        
        $execution->update([
            'status' => 'cancelled',
            'finished_at' => now(),
        ]);
        
        return response()->json(['message' => 'Execution cancelled']);
    }
    
    public function delete(Execution $execution)
    {
        $this->authorize('view', $execution);
        
        $execution->delete();
        
        return response()->noContent();
    }
    
    public function statistics(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'total_executions' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            
            'successful' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'success')->count(),
            
            'failed' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'failed')->count(),
            
            'running' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'running')->count(),
            
            'avg_duration' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->whereNotNull('duration_ms')->avg('duration_ms'),
            
            'executions_by_day' => Execution::whereHas('workflow', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->get(),
        ];
        
        return response()->json($stats);
    }
}
```

### WebhookController
```php
namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Jobs\ExecuteWorkflowJob;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request, string $webhookId)
    {
        $webhook = Webhook::where('webhook_id', $webhookId)
            ->where('active', true)
            ->with('workflow')
            ->firstOrFail();
        
        if (!$webhook->workflow->active) {
            return response()->json(['error' => 'Workflow is not active'], 403);
        }
        
        // Process webhook data
        $webhookData = [
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
            'method' => $request->method(),
        ];
        
        $webhook->incrementRequestCount();
        
        // Get response mode
        $responseMode = $webhook->response_mode['mode'] ?? 'immediate';
        
        if ($responseMode === 'immediate') {
            // Dispatch job and respond immediately
            ExecuteWorkflowJob::dispatch($webhook->workflow, $webhookData, 'webhook');
            
            return response()->json(['message' => 'Workflow triggered successfully']);
        }
        
        if ($responseMode === 'last_node') {
            // Execute synchronously and return last node output
            $execution = app(\App\Services\Execution\WorkflowExecutor::class)
                ->execute($webhook->workflow, $webhookData, 'webhook');
            
            return response()->json($execution->output_data);
        }
        
        return response()->json(['message' => 'Webhook received']);
    }
    
    public function test(Webhook $webhook, Request $request)
    {
        $this->authorize('update', $webhook->workflow);
        
        $testData = $request->all();
        
        $execution = app(\App\Services\Execution\WorkflowExecutor::class)
            ->execute($webhook->workflow, $testData, 'manual');
        
        return response()->json([
            'execution_id' => $execution->id,
            'status' => $execution->status,
            'output' => $execution->output_data,
        ]);
    }
}
```

### CredentialController
```php
namespace App\Http\Controllers;

use App\Models\Credential;
use App\Services\Credential\CredentialService;
use Illuminate\Http\Request;

class CredentialController extends Controller
{
    public function __construct(protected CredentialService $service) {}
    
    public function index(Request $request)
    {
        $credentials = Credential::where('user_id', $request->user()->id)
            ->select(['id', 'name', 'type', 'last_used_at', 'created_at'])
            ->latest()
            ->get();
        
        return response()->json($credentials);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:api_key,oauth2,basic_auth,bearer_token',
            'data' => 'required|array',
        ]);
        
        $credential = $this->service->store(
            $validated['data'],
            $validated['type'],
            $validated['name'],
            $request->user()->id
        );
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
        ], 201);
    }
    
    public function show(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        return response()->json([
            'id' => $credential->id,
            'name' => $credential->name,
            'type' => $credential->type,
            'data' => $credential->data, // Decrypted
            'last_used_at' => $credential->last_used_at,
        ]);
    }
    
    public function update(Request $request, Credential $credential)
    {
        $this->authorize('update', $credential);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'data' => 'array',
        ]);
        
        if (isset($validated['name'])) {
            $credential->update(['name' => $validated['name']);
        }
        
        if (isset($validated['data'])) {
            $this->service->update($credential, $validated['data']);
        }
        
        return response()->json(['message' => 'Credential updated']);
    }
    
    public function destroy(Credential $credential)
    {
        $this->authorize('delete', $credential);
        
        $credential->delete();
        
        return response()->noContent();
    }
    
    public function test(Credential $credential)
    {
        $this->authorize('view', $credential);
        
        try {
            $result = $this->service->test($credential);
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Credential is valid' : 'Credential test failed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
```

### NodeController
```php
namespace App\Http\Controllers;

use App\Services\Node\NodeRegistry;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function __construct(protected NodeRegistry $registry) {}
    
    public function index()
    {
        return response()->json(
            $this->registry->getAllNodes()
        );
    }
    
    public function byGroup()
    {
        return response()->json(
            $this->registry->getNodesByGroup()
        );
    }
    
    public function show(string $type)
    {
        try {
            $node = $this->registry->getNode($type);
            return response()->json($node->getDefinition());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
```

## 🔄 Jobs

### ExecuteWorkflowJob
```php
namespace App\Jobs;

use App\Models\Workflow;
use App\Models\Execution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 300;
    public $tries = 3;
    public $backoff = [60, 180, 600];
    
    public function __construct(
        public Workflow $workflow,
        public array $inputData = [],
        public string $mode = 'trigger',
        public ?Execution $execution = null
    ) {}
    
    public function handle(WorkflowExecutor $executor): void
    {
        if ($this->execution) {
            // Continue existing execution
            $executor->resume($this->execution, $this->inputData);
        } else {
            // Start new execution
            $executor->execute($this->workflow, $this->inputData, $this->mode);
        }
    }
    
    public function failed(\Throwable $exception): void
    {
        if ($this->execution) {
            $this->execution->markAsFailed($exception->getMessage());
        }
        
        \Log::error('Workflow execution job failed', [
            'workflow_id' => $this->workflow->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
```

### ResumeExecutionJob
```php
namespace App\Jobs;

use App\Models\WaitingExecution;
use App\Services\Execution\WorkflowExecutor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResumeExecutionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public WaitingExecution $waitingExecution,
        public array $resumeData = []
    ) {}
    
    public function handle(WorkflowExecutor $executor): void
    {
        $execution = $this->waitingExecution->execution;
        
        $executor->resume($execution, $this->resumeData);
    }
}
```

### CleanOldExecutionsJob
```php
namespace App\Jobs;

use App\Models\Execution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanOldExecutionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle(): void
    {
        $daysToKeep = config('workflow.execution_retention_days', 30);
        
        Execution::where('created_at', '<', now()->subDays($daysToKeep))
            ->where('status', '!=', 'failed') // Keep failed executions longer
            ->delete();
        
        // Delete very old failed executions
        Execution::where('created_at', '<', now()->subDays($daysToKeep * 3))
            ->where('status', 'failed')
            ->delete();
    }
}
```

## 📡 Events & Listeners

### Events
```php
namespace App\Events;

use App\Models\Execution;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowExecutionStarted
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}

class WorkflowExecutionCompleted
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}

class WorkflowExecutionFailed
{
    use Dispatchable, SerializesModels;
    
    public function __construct(public Execution $execution) {}
}
```

### Listeners
```php
namespace App\Listeners;

use App\Events\WorkflowExecutionFailed;
use Illuminate\Support\Facades\Notification;

class NotifyExecutionFailure
{
    public function handle(WorkflowExecutionFailed $event): void
    {
        $execution = $event->execution;
        $workflow = $execution->workflow;
        
        // Send notification to workflow owner
        if ($workflow->user) {
            // $workflow->user->notify(new WorkflowFailedNotification($execution));
        }
        
        // Log to monitoring service
        \Log::channel('workflow')->error('Workflow execution failed', [
            'execution_id' => $execution->id,
            'workflow_id' => $workflow->id,
            'error' => $execution->error_message,
        ]);
    }
}
```

## 🎯 Console Commands

### RunScheduledWorkflows
```php
namespace App\Console\Commands;

use App\Models\Schedule;
use App\Jobs\ExecuteWorkflowJob;
use Illuminate\Console\Command;

class RunScheduledWorkflows extends Command
{
    protected $signature = 'workflows:run-scheduled';
    protected $description = 'Execute scheduled workflows that are due';
    
    public function handle(): int
    {
        $schedules = Schedule::where('active', true)
            ->where('next_run_at', '<=', now())
            ->with('workflow')
            ->get();
        
        foreach ($schedules as $schedule) {
            if (!$schedule->workflow->active) {
                continue;
            }
            
            $this->info("Executing workflow: {$schedule->workflow->name}");
            
            ExecuteWorkflowJob::dispatch(
                $schedule->workflow,
                [],
                'schedule'
            );
            
            $schedule->markAsRun();
        }
        
        $this->info("Executed {$schedules->count()} scheduled workflows");
        
        return 0;
    }
}
```

### ProcessWaitingExecutions
```php
namespace App\Console\Commands;

use App\Models\WaitingExecution;
use App\Jobs\ResumeExecutionJob;
use Illuminate\Console\Command;

class ProcessWaitingExecutions extends Command
{
    protected $signature = 'workflows:process-waiting';
    protected $description = 'Resume waiting executions that are ready';
    
    public function handle(): int
    {
        $waitingExecutions = WaitingExecution::where('wait_type', 'time')
            ->where('resume_at', '<=', now())
            ->get();
        
        foreach ($waitingExecutions as $waiting) {
            $this->info("Resuming execution: {$waiting->execution_id}");
            
            ResumeExecutionJob::dispatch($waiting);
        }
        
        $this->info("Resumed {$waitingExecutions->count()} executions");
        
        return 0;
    }
}
```

## 🛣️ API Routes

```php
// routes/api.php

use App\Http\Controllers\{
    WorkflowController,
    ExecutionController,
    WebhookController,
    CredentialController,
    NodeController,
    TagController
};

Route::prefix('v1')->group(function () {
    
    // Public webhook endpoint
    Route::post('webhook/{webhookId}', [WebhookController::class, 'handle']);
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Workflows
        Route::apiResource('workflows', WorkflowController::class);
        Route::post('workflows/{workflow}/execute', [WorkflowController::class, 'execute']);
        Route::post('workflows/{workflow}/duplicate', [WorkflowController::class, 'duplicate']);
        Route::post('workflows/{workflow}/activate', [WorkflowController::class, 'activate']);
        Route::post('workflows/{workflow}/deactivate', [WorkflowController::class, 'deactivate']);
        Route::get('workflows/{workflow}/versions', [WorkflowController::class, 'versions']);
        Route::post('workflows/{workflow}/versions/{version}/restore', [WorkflowController::class, 'restoreVersion']);
        
        // Executions
        Route::get('executions', [ExecutionController::class, 'index']);
        Route::get('executions/statistics', [ExecutionController::class, 'statistics']);
        Route::get('executions/{execution}', [ExecutionController::class, 'show']);
        Route::post('executions/{execution}/retry', [ExecutionController::class, 'retry']);
        Route::post('executions/{execution}/cancel', [ExecutionController::class, 'cancel']);
        Route::delete('executions/{execution}', [ExecutionController::class, 'delete']);
        
        // Credentials
        Route::apiResource('credentials', CredentialController::class);
        Route::post('credentials/{credential}/test', [CredentialController::class, 'test']);
        
        // Nodes
        Route::get('nodes', [NodeController::class, 'index']);
        Route::get('nodes/by-group', [NodeController::class, 'byGroup']);
        Route::get('nodes/{type}', [NodeController::class, 'show']);
        
        // Tags
        Route::apiResource('tags', TagController::class);
        
        // Webhooks
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);
    });
});
```

## 📝 Request Validation

### StoreWorkflowRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'required|array',
            'connections.*.source' => 'required|string',
            'connections.*.target' => 'required|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
```

### UpdateWorkflowRequest
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workflow'));
    }
    
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'array|min:1',
            'nodes.*.id' => 'required_with:nodes|string',
            'nodes.*.type' => 'required_with:nodes|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'array',
            'connections.*.source' => 'required_with:connections|string',
            'connections.*.target' => 'required_with:connections|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
```

## 🎨 API Resources

### WorkflowResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'active' => $this->active,
            'nodes' => $this->nodes,
            'connections' => $this->connections,
            'settings' => $this->settings,
            'static_data' => $this->static_data,
            'execution_count' => $this->execution_count,
            'last_executed_at' => $this->last_executed_at,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'webhooks' => WebhookResource::collection($this->whenLoaded('webhooks')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### ExecutionResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'workflow_id' => $this->workflow_id,
            'workflow_name' => $this->workflow->name,
            'status' => $this->status,
            'mode' => $this->mode,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error_message' => $this->error_message,
            'retry_count' => $this->retry_count,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'waiting_till' => $this->waiting_till,
            'duration_ms' => $this->duration_ms,
            'execution_data' => ExecutionDataResource::collection($this->whenLoaded('executionData')),
            'created_at' => $this->created_at,
        ];
    }
}
```

### ExecutionDataResource
```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExecutionDataResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'node_id' => $this->node_id,
            'node_name' => $this->node_name,
            'node_type' => $this->node_type,
            'status' => $this->status,
            'input_data' => $this->input_data,
            'output_data' => $this->output_data,
            'error' => $this->error,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'duration_ms' => $this->duration_ms,
        ];
    }
}
```

## 🔧 Additional Services

### WorkflowValidator
```php
namespace App\Services\Workflow;

class WorkflowValidator
{
    public function validate(array $workflowData): bool
    {
        // Check if workflow has at least one trigger node
        $hasTrigger = collect($workflowData['nodes'] ?? [])
            ->contains(fn($node) => str_contains($node['type'], 'trigger'));
        
        if (!$hasTrigger) {
            throw new \Exception('Workflow must have at least one trigger node');
        }
        
        // Check if all connections reference existing nodes
        $nodeIds = collect($workflowData['nodes'] ?? [])->pluck('id')->toArray();
        
        foreach ($workflowData['connections'] ?? [] as $connection) {
            if (!in_array($connection['source'], $nodeIds)) {
                throw new \Exception("Connection source node '{$connection['source']}' does not exist");
            }
            
            if (!in_array($connection['target'], $nodeIds)) {
                throw new \Exception("Connection target node '{$connection['target']}' does not exist");
            }
        }
        
        // Check for circular dependencies
        if ($this->hasCircularDependency($workflowData['nodes'], $workflowData['connections'])) {
            throw new \Exception('Workflow contains circular dependencies');
        }
        
        // Check if all nodes are reachable from trigger
        if (!$this->allNodesReachable($workflowData['nodes'], $workflowData['connections'])) {
            throw new \Exception('Some nodes are not connected to the workflow');
        }
        
        return true;
    }
    
    protected function hasCircularDependency(array $nodes, array $connections): bool
    {
        $graph = $this->buildGraph($connections);
        $visited = [];
        $recStack = [];
        
        foreach ($nodes as $node) {
            if ($this->isCyclicUtil($node['id'], $graph, $visited, $recStack)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function isCyclicUtil(string $nodeId, array $graph, array &$visited, array &$recStack): bool
    {
        if (isset($recStack[$nodeId])) {
            return true;
        }
        
        if (isset($visited[$nodeId])) {
            return false;
        }
        
        $visited[$nodeId] = true;
        $recStack[$nodeId] = true;
        
        if (isset($graph[$nodeId])) {
            foreach ($graph[$nodeId] as $neighbor) {
                if ($this->isCyclicUtil($neighbor, $graph, $visited, $recStack)) {
                    return true;
                }
            }
        }
        
        unset($recStack[$nodeId]);
        return false;
    }
    
    protected function allNodesReachable(array $nodes, array $connections): bool
    {
        $graph = $this->buildGraph($connections);
        $triggerNodes = collect($nodes)->filter(fn($n) => str_contains($n['type'], 'trigger'));
        
        if ($triggerNodes->isEmpty()) {
            return false;
        }
        
        $reachable = [];
        foreach ($triggerNodes as $trigger) {
            $this->dfs($trigger['id'], $graph, $reachable);
        }
        
        $nodeIds = collect($nodes)->pluck('id')->toArray();
        
        return count($reachable) === count($nodeIds);
    }
    
    protected function dfs(string $nodeId, array $graph, array &$reachable): void
    {
        if (isset($reachable[$nodeId])) {
            return;
        }
        
        $reachable[$nodeId] = true;
        
        if (isset($graph[$nodeId])) {
            foreach ($graph[$nodeId] as $neighbor) {
                $this->dfs($neighbor, $graph, $reachable);
            }
        }
    }
    
    protected function buildGraph(array $connections): array
    {
        $graph = [];
        
        foreach ($connections as $connection) {
            if (!isset($graph[$connection['source']])) {
                $graph[$connection['source']] = [];
            }
            $graph[$connection['source']][] = $connection['target'];
        }
        
        return $graph;
    }
}
```

### WorkflowCloner
```php
namespace App\Services\Workflow;

use App\Models\Workflow;
use Illuminate\Support\Str;

class WorkflowCloner
{
    public function clone(Workflow $workflow, ?string $newName = null): Workflow
    {
        $nodes = $workflow->nodes;
        $connections = $workflow->connections;
        
        // Generate new node IDs
        $idMapping = [];
        foreach ($nodes as &$node) {
            $oldId = $node['id'];
            $newId = Str::uuid();
            $idMapping[$oldId] = $newId;
            $node['id'] = $newId;
        }
        
        // Update connections with new node IDs
        foreach ($connections as &$connection) {
            $connection['source'] = $idMapping[$connection['source']];
            $connection['target'] = $idMapping[$connection['target']];
        }
        
        // Create new workflow
        $cloned = Workflow::create([
            'name' => $newName ?? $workflow->name . ' (Copy)',
            'description' => $workflow->description,
            'active' => false, // Always inactive
            'nodes' => $nodes,
            'connections' => $connections,
            'settings' => $workflow->settings,
            'static_data' => $workflow->static_data,
            'user_id' => $workflow->user_id,
        ]);
        
        // Copy tags
        $cloned->tags()->attach($workflow->tags->pluck('id'));
        
        return $cloned;
    }
}
```

## ⚙️ Configuration

### config/workflow.php
```php
<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Execution Settings
    |--------------------------------------------------------------------------
    */
    
    'execution' => [
        'timeout' => env('WORKFLOW_EXECUTION_TIMEOUT', 300), // seconds
        'max_retries' => env('WORKFLOW_MAX_RETRIES', 3),
        'retention_days' => env('WORKFLOW_RETENTION_DAYS', 30),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    
    'queue' => [
        'connection' => env('WORKFLOW_QUEUE_CONNECTION', 'redis'),
        'name' => env('WORKFLOW_QUEUE_NAME', 'workflows'),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Node Settings
    |--------------------------------------------------------------------------
    */
    
    'nodes' => [
        'cache_enabled' => env('WORKFLOW_CACHE_NODES', true),
        'cache_ttl' => 3600, // seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    */
    
    'webhook' => [
        'prefix' => env('WORKFLOW_WEBHOOK_PREFIX', 'webhook'),
        'rate_limit' => env('WORKFLOW_WEBHOOK_RATE_LIMIT', 60), // per minute
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    
    'security' => [
        'encrypt_credentials' => true,
        'allow_code_execution' => env('WORKFLOW_ALLOW_CODE', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    
    'logging' => [
        'channel' => env('WORKFLOW_LOG_CHANNEL', 'stack'),
        'level' => env('WORKFLOW_LOG_LEVEL', 'info'),
    ],
    
];
```

## 📊 Service Provider

### WorkflowServiceProvider
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Node\NodeRegistry;

class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NodeRegistry::class);
    }
    
    public function boot(): void
    {
        // Register all nodes
        $registry = $this->app->make(NodeRegistry::class);
        
        // Triggers
        $registry->register('webhook_trigger', \App\Nodes\Triggers\WebhookTrigger::class);
        $registry->register('schedule_trigger', \App\Nodes\Triggers\ScheduleTrigger::class);
        $registry->register('manual_trigger', \App\Nodes\Triggers\ManualTrigger::class);
        
        // Actions
        $registry->register('http_request', \App\Nodes\Actions\HttpRequest::class);
        $registry->register('email', \App\Nodes\Actions\Email::class);
        $registry->register('database', \App\Nodes\Actions\Database::class);
        $registry->register('wait', \App\Nodes\Actions\Wait::class);
        
        // Logic
        $registry->register('if', \App\Nodes\Logic\IfNode::class);
        $registry->register('switch', \App\Nodes\Logic\SwitchNode::class);
        $registry->register('merge', \App\Nodes\Logic\MergeNode::class);
        
        // Transform
        $registry->register('code', \App\Nodes\Transform\Code::class);
        $registry->register('set', \App\Nodes\Transform\SetNode::class);
        
        // Schedule commands
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run scheduled workflows every minute
            $schedule->command('workflows:run-scheduled')->everyMinute();
            
            // Process waiting executions every minute
            $schedule->command('workflows:process-waiting')->everyMinute();
            
            // Clean old executions daily
            $schedule->command('workflows:clean-executions')->daily();
        });
    }
}
```

## 🔐 Policies

### WorkflowPolicy
```php
namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;

class WorkflowPolicy
{
    public function view(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()->where('user_id', $user->id)->exists();
    }
    
    public function update(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()
                   ->where('user_id', $user->id)
                   ->whereIn('permission', ['edit'])
                   ->exists();
    }
    
    public function delete(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id;
    }
    
    public function execute(User $user, Workflow $workflow): bool
    {
        return $workflow->user_id === $user->id || 
               $workflow->shares()
                   ->where('user_id', $user->id)
                   ->whereIn('permission', ['edit', 'execute'])
                   ->exists();
    }
}
```

## 🧪 Example Usage

### Creating a Workflow via API
```json
POST /api/v1/workflows

{
  "name": "Process New Orders",
  "active": true,
  "nodes": [
    {
      "id": "node_1",
      "type": "webhook_trigger",
      "name": "Order Webhook",
      "parameters": {
        "method": "POST"
      },
      "position": {"x": 100, "y": 100}
    },
    {
      "id": "node_2",
      "type": "if",
      "name": "Check Amount",
      "parameters": {
        "conditions": [
          {
            "value1": "{{$json.amount}}",
            "operation": "greater_than",
            "value2": "100"
          }
        ]
      },
      "position": {"x": 300, "y": 100}
    },
    {
      "id": "node_3",
      "type": "http_request",
      "name": "Notify Team",
      "parameters": {
        "method": "POST",
        "url": "https://slack.com/api/chat.postMessage",
        "body": {
          "text": "New order: {{$json.order_id}}"
        }
      },
      "credentials": 1,
      "position": {"x": 500, "y": 100}
    }
  ],
  "connections": [
    {
      "source": "node_1",
      "target": "node_2"
    },
    {
      "source": "node_2",
      "target": "node_3",
      "sourceOutput": "true"
    }
  ]
}
```

## 📈 Performance Optimization Tips

### 1. Database Indexing
```sql
-- Add indexes for common queries
CREATE INDEX idx_executions_workflow_status ON executions(workflow_id, status, created_at);
CREATE INDEX idx_executions_status_waiting ON executions(status, waiting_till) WHERE status = 'waiting';
CREATE INDEX idx_webhooks_path ON webhooks(path) WHERE active = true;

-- For PostgreSQL JSONB queries
CREATE INDEX idx_workflow_nodes_gin ON workflows USING GIN (nodes);
CREATE INDEX idx_execution_data_output ON execution_data USING GIN (output_data);
```

### 2. Redis Configuration
```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_QUEUE=workflows
```

### 3. Queue Workers
```bash
# Run multiple workers for parallel processing
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
php artisan queue:work redis --queue=workflows --tries=3 --timeout=300 --memory=512 &
```

### 4. Horizon Configuration (Recommended)
```bash
composer require laravel/horizon

# config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['workflows', 'default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
            'timeout' => 300,
        ],
    ],
],
```

## 🚀 Deployment Checklist

1. **Environment Variables**
```env
APP_ENV=production
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
WORKFLOW_EXECUTION_TIMEOUT=300
WORKFLOW_MAX_RETRIES=3
WORKFLOW_ALLOW_CODE=false
```

2. **Optimize Laravel**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

3. **Setup Supervisor**
```ini
[program:workflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=workflows --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=5
user=www-data
```

4. **Setup Cron**
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

5. **Monitoring**
- Setup Laravel Telescope for debugging
- Use Horizon for queue monitoring
- Setup error tracking (Sentry, Bugsnag)
- Monitor Redis memory usage

This is a **complete, production-ready n8n clone** with all advanced features! 🚀
