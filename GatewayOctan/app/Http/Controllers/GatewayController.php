<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    public function forward(Request $request, string $service, ?string $path = null)
    {
        // Map service name to config key
        $serviceMap = [
            'orders'   => 'order_service',
            'payments' => 'payment_service',
            'accounts' => 'account_service',
        ];

        // Validate service
        if (!isset($serviceMap[$service])) {
            return response()->json(['message' => 'Unknown service'], 404);
        }

        $baseUrl = config('services.' . $serviceMap[$service] . '.url');

        // Full target URL (append any extra path)
        $targetUrl = rtrim($baseUrl, '/') . '/' . ltrim($path ?? '', '/');

        try {
            // Forward the request
            $response = Http::withHeaders([
                    'Accept' => 'application/json',
                ])
                ->withToken($request->bearerToken())
                ->send($request->method(), $targetUrl, [
                    'query' => $request->query(),
                    'json' => $request->all(),
                ]);

            // Return response as-is
            return response($response->body(), $response->status())
                ->withHeaders($response->headers());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => "Failed to contact {$service} service",
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}