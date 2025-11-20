<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChatRateLimiter
{
	/**
	 * Limit messages per admin per channel per minute using cache counters.
	 */
	public function handle(Request $request, Closure $next)
	{
		$admin = Auth::guard('admin')->user();
		if (!$admin) {
			return response()->json(['error' => 'Unauthorized'], 401);
		}

		// Resolve channel id from route binding (if available)
		$channel = $request->route('channel');
		$channelId = is_object($channel) ? ($channel->id ?? null) : (int) $channel;
		if (!$channelId) {
			return $next($request); // No channel context, skip
		}

		$limit = (int) env('CHAT_RATE_LIMIT', 20);
		$key = sprintf('chat_rate:%d:%d', $admin->id, $channelId);
		$count = Cache::increment($key, 1);
		if ($count === 1) {
			Cache::put($key, $count, now()->addSeconds(60));
		}

		if ($count > $limit) {
			return response()->json([
				'error' => 'Too Many Messages',
				'retry_after' => Cache::getExpiration($key)?->getTimestamp() - time(),
				'limit' => $limit,
			], 429);
		}

		return $next($request);
	}
}
