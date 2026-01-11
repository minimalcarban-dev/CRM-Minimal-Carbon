<?php

namespace App\Http\Middleware;

use App\Services\MetaApiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyMetaWebhook
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification for GET requests (webhook verification)
        if ($request->isMethod('get')) {
            return $next($request);
        }

        $signature = $request->header('X-Hub-Signature-256', '');
        $payload = $request->getContent();

        if (empty($signature)) {
            Log::channel('meta')->warning('Missing webhook signature');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        if (!MetaApiService::verifyWebhookSignature($payload, $signature)) {
            Log::channel('meta')->warning('Invalid webhook signature', [
                'signature' => $signature,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
