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