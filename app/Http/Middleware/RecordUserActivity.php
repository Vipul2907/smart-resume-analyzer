<?php

namespace App\Http\Middleware;

use App\Models\AdminActivity;
use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();
        $routeParameters = collect($route?->parameters() ?? [])
            ->map(fn ($parameter) => is_object($parameter) && isset($parameter->id)
                ? (int) $parameter->id
                : (is_numeric($parameter) ? (int) $parameter : null))
            ->filter(fn ($parameter) => $parameter !== null)
            ->all();

        if ($user && is_string($routeName) && str_starts_with($routeName, 'admin.')) {
            if (! $user->is_admin) {
                return $response;
            }

            $subjectId = $routeParameters['user'] ?? null;
            $ticket = $route?->parameter('supportRequest');
            if ($subjectId === null && is_object($ticket) && isset($ticket->user_id) && is_numeric($ticket->user_id)) {
                $subjectId = (int) $ticket->user_id;
            }

            AdminActivity::query()->create([
                'admin_user_id' => $user->id,
                'subject_user_id' => $subjectId,
                'route_name' => $routeName,
                'http_method' => $request->method(),
                'route_parameters' => $routeParameters ?: null,
                'response_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        } elseif ($user && is_string($routeName)) {
            UserActivity::query()->create([
                'user_id' => $user->id,
                'route_name' => $routeName,
                'http_method' => $request->method(),
                'route_parameters' => $routeParameters ?: null,
                'response_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
