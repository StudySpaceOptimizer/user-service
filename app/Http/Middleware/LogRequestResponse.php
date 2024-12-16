<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 記錄請求資訊
        Log::info('Request Details', [
            'method' => $request->getMethod(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $response = $next($request);

        // 記錄回應資訊
        Log::info('Response Details', [
            'status' => $response->getStatusCode(),
            'content' => $response->getContent(),
        ]);

        return $response;
    }
}
