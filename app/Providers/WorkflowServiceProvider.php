<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Node\NodeRegistry;
use App\Models\Workflow;
use App\Models\Execution;
use App\Observers\WorkflowObserver;
use App\Observers\ExecutionObserver;

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
        $registry->register('wait', \App\Nodes\Actions\Wait::class);
        
        // Logic
        $registry->register('if', \App\Nodes\Logic\IfNode::class);
        $registry->register('switch', \App\Nodes\Logic\SwitchNode::class);
        $registry->register('merge', \App\Nodes\Logic\MergeNode::class);
        
        // Transform
        $registry->register('code', \App\Nodes\Transform\Code::class);
        $registry->register('set', \App\Nodes\Transform\SetNode::class);
        $registry->register('datetime', \App\Nodes\Transform\DateTime::class);
        
        // Register observers
        Workflow::observe(WorkflowObserver::class);
        Execution::observe(ExecutionObserver::class);
        
        // Schedule commands
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            
            // Run scheduled workflows every minute
            $schedule->command('workflows:run-scheduled')->everyMinute();
            
            // Process waiting executions every minute
            $schedule->command('workflows:process-waiting')->everyMinute();
            
            // Clean old executions daily
            $schedule->command('workflows:cleanup-executions')->daily();
        });
    }
}