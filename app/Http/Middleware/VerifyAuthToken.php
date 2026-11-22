<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyAuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: Missing token'], 401);
        }

        // Gọi sang Auth Service (BTH2_SOA) để kiểm tra token
        // Lưu ý: Điều chỉnh endpoint (/me hoặc /profile hoặc /verify) cho khớp với AuthController của BTH2_SOA
        $authUrl = env('AUTH_SERVICE_URL', 'http://127.0.0.1:8000/api') . '/auth';

        try {
            $response = Http::withToken($token)->acceptJson()->get($authUrl);

            if ($response->failed()) {
    return response()->json([
        'message'       => 'Unauthorized: Invalid token',
        'auth_url'      => $authUrl,
        'auth_status'   => $response->status(),
        'auth_response' => $response->json() ?? $response->body(),
    ], 401);
}

            // Lưu thông tin user vào request nếu cần dùng trong controller
            $request->merge(['auth_user' => $response->json()]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Auth Service unavailable'], 503);
        }

        return $next($request);
    }
}