<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    WorkflowController,
    ExecutionController,
    WebhookController,
    CredentialController,
    NodeController,
    TagController,
    ScheduleController,
    AuthController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes (public)
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});

// Get authenticated user (auth required)
Route::middleware('auth:api')->get('/user', [AuthController::class, 'user']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'services' => [
            'database' => 'ok',
            'queue' => 'ok',
            'cache' => 'ok',
        ]
    ]);
});

// Public webhook endpoint (outside auth middleware)
Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/webhook/{webhookId}', [WebhookController::class, 'handle']);

// API routes with authentication
Route::middleware('auth:api')->prefix('v1')->group(function () {
    
    // Workflows
    Route::apiResource('workflows', WorkflowController::class)->middleware('subscription:workflows');
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
    Route::delete('executions/{execution}', [ExecutionController::class, 'destroy']);
    
    // Credentials
    Route::apiResource('credentials', CredentialController::class);
    Route::post('credentials/{credential}/test', [CredentialController::class, 'test']);
    
    // Nodes
    Route::get('nodes', [NodeController::class, 'index']);
    Route::get('nodes/by-group', [NodeController::class, 'byGroup']);
    Route::get('nodes/{type}', [NodeController::class, 'show']);
    
    // Tags
    Route::apiResource('tags', TagController::class);
    
    // Schedules
    Route::apiResource('schedules', ScheduleController::class);
    
    // Webhooks (admin operations)
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test']);

    // Organizations and Teams
    Route::apiResource('organizations', 'App\Http\Controllers\OrganizationController');
    Route::apiResource('organizations.teams', 'App\Http\Controllers\TeamController')->shallow();

    // Team Members
    Route::post('teams/{team}/members', ['App\Http\Controllers\TeamMemberController', 'store'])->middleware('subscription:members');
    Route::delete('teams/{team}/members/{user}', ['App\Http\Controllers\TeamMemberController', 'destroy']);

    // Invitations
    Route::post('teams/{team}/invitations', ['App\Http\Controllers\InvitationController', 'store']);
    Route::post('invitations/{invitation:token}/accept', ['App\Http\Controllers\InvitationController', 'accept']);

    // Current Team
    Route::put('current-team', ['App\Http\Controllers\CurrentTeamController', 'update']);
});