<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogLivewireRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Always record the entry
        Log::debug('middleware:LogLivewireRequests:entered', [
            'uri' => $request->getRequestUri(),
            'path' => $request->getPathInfo(),
        ]);

        // Detect Livewire endpoints more broadly (match both /livewire/... and /livewire-{id}/update patterns)
        $uri = $request->getRequestUri();
        if (! str_contains($uri, '/livewire')) {
            return $next($request);
        }

        $start = microtime(true);
        $queries = 0;

        $listener = function ($query) use (&$queries) {
            $queries++;
            Log::debug('sql', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
            ]);
        };
        \Illuminate\Support\Facades\DB::listen($listener);

        $response = $next($request);

        $ms = round((microtime(true) - $start) * 1000, 1);
        $bytes = strlen((string) $response->getContent());

        // --- DEBUG: log Livewire request payload (temporary) ---
        $raw = $request->getContent();
        $payload = null;
        if (! empty($raw)) {
            $payload = @json_decode($raw, true);
        }

        // Log a small, safe subset so we don't fill logs with huge payloads.
        Log::debug('livewire:payload', [
            'uri' => $uri,
            'raw_bytes' => $raw ? strlen($raw) : 0,
            'parsed' => is_array($payload)
                ? [
                    'name' => $payload['name'] ?? null,
                    'method' => $payload['method'] ?? null,
                    'fingerprint' => $payload['fingerprint'] ?? null,
                    'serverMemo' => isset($payload['serverMemo']) ? 'present' : null,
                ]
                : null,
        ]);

        Log::info('livewire:handled', [
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'time_ms' => $ms,
            'queries' => $queries,
            'response_bytes' => $bytes,
        ]);

        return $response;
    }
}
