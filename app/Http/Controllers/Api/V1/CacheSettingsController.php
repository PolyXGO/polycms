<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CacheSettingsController extends Controller
{
    public function testRedisConnection(Request $request): JsonResponse
    {
        $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|integer',
            'password' => 'nullable|string',
        ]);

        $host = $request->input('host');
        $port = (int) $request->input('port');
        $password = $request->input('password');

        try {
            if (class_exists(\Redis::class)) {
                $redis = new \Redis();
                
                // Connect with timeout of 2.0s
                $connected = @$redis->connect($host, $port, 2.0);
                
                if (!$connected) {
                    return response()->json([
                        'success' => false,
                        'message' => "Could not connect to Redis server at {$host}:{$port}. Please verify the host and port."
                    ]);
                }

                if ($password) {
                    $authenticated = @$redis->auth($password);
                    if (!$authenticated) {
                        return response()->json([
                            'success' => false,
                            'message' => "Connection successful, but Redis authentication failed. Please verify the password."
                        ]);
                    }
                }

                $ping = @$redis->ping();
                if ($ping) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Successfully connected and authenticated with Redis!'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Connected to server, but ping response was invalid.'
                ]);
            } else {
                // Socket fallback
                $socket = @fsockopen($host, $port, $errno, $errstr, 2.0);
                if (!$socket) {
                    return response()->json([
                        'success' => false,
                        'message' => "Could not open connection to {$host}:{$port}: {$errstr} ({$errno})"
                    ]);
                }
                fclose($socket);
                return response()->json([
                    'success' => true,
                    'message' => "Port is open. Socket connection successful (phpredis extension not installed, but port is reachable)."
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ]);
        }
    }
}
