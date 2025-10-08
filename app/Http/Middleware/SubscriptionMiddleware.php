<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$features): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        foreach ($features as $feature) {
            switch ($feature) {
                case 'workflows':
                    if (! $user->canCreateMoreWorkflows()) {
                        return response()->json(['message' => 'You have reached the maximum number of workflows for your subscription plan.'], 403);
                    }
                    break;
                case 'members':
                    $team = $request->route('team');
                    if (! $user->canAddMoreTeamMembers($team->organization)) {
                        return response()->json(['message' => 'You have reached the maximum number of team members for your subscription plan.'], 403);
                    }
                    break;
            }
        }

        return $next($request);
    }
}