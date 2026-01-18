<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogTestController extends Controller
{
    /**
     * Generate test logs for ELK stack demonstration.
     */
    public function generateLogs(Request $request)
    {
        $userId = $request->input('user_id', rand(1000, 9999));
        $action = $request->input('action', 'test_action');
        
        // INFO: Successful operation
        Log::info('User action completed', [
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toIso8601String(),
            'status' => 'success',
        ]);

        // DEBUG: Detailed information
        Log::debug('Processing user request', [
            'user_id' => $userId,
            'request_path' => $request->path(),
            'request_method' => $request->method(),
            'query_params' => $request->query(),
        ]);

        // Simulate random errors and warnings
        $rand = rand(1, 10);
        
        if ($rand <= 2) {
            // ERROR: Simulated error condition
            Log::error('Database query failed', [
                'user_id' => $userId,
                'error_code' => 'DB_CONNECTION_ERROR',
                'query' => 'SELECT * FROM users WHERE id = ?',
                'attempted_at' => now()->toIso8601String(),
                'retry_count' => 3,
            ]);
        } elseif ($rand <= 4) {
            // WARNING: Simulated warning condition
            Log::warning('Slow database query detected', [
                'user_id' => $userId,
                'query_time' => rand(1000, 5000) . 'ms',
                'query' => 'SELECT * FROM orders WHERE user_id = ?',
                'threshold' => '1000ms',
            ]);
        }

        // Log performance metrics
        Log::info('API response metrics', [
            'endpoint' => $request->path(),
            'response_time' => rand(50, 500) . 'ms',
            'status_code' => 200,
            'user_id' => $userId,
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Logs generated successfully',
            'user_id' => $userId,
            'action' => $action,
            'logs_written' => rand(2, 4),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Generate multiple logs in a batch.
     */
    public function batchLogs(Request $request)
    {
        $count = $request->input('count', 10);
        $count = min($count, 100); // Max 100 logs per request

        $actions = ['login', 'logout', 'view_page', 'api_call', 'database_query', 'file_upload'];
        $operations = ['INSERT', 'UPDATE', 'DELETE', 'SELECT'];
        
        for ($i = 0; $i < $count; $i++) {
            $userId = rand(1000, 9999);
            $action = $actions[array_rand($actions)];
            $level = $this->getRandomLogLevel();

            Log::log($level, ucfirst($action) . ' operation', [
                'user_id' => $userId,
                'action' => $action,
                'operation_type' => $operations[array_rand($operations)],
                'response_time' => rand(10, 2000),
                'status_code' => $this->getRandomStatusCode($level),
                'ip_address' => $this->getRandomIp(),
                'session_id' => substr(md5(rand()), 0, 16),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Random sleep to simulate realistic timing
            usleep(rand(10000, 50000)); // 10-50ms
        }

        return response()->json([
            'success' => true,
            'message' => 'Batch logs generated successfully',
            'logs_count' => $count,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Simulate application errors.
     */
    public function errorScenarios(Request $request)
    {
        $scenario = $request->input('scenario', 'all');

        switch ($scenario) {
            case 'database':
                Log::error('Database connection timeout', [
                    'error_type' => 'DATABASE_ERROR',
                    'host' => 'db.example.com',
                    'port' => 3306,
                    'timeout' => '30s',
                    'stack_trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                break;

            case 'api':
                Log::error('External API request failed', [
                    'error_type' => 'API_ERROR',
                    'api_url' => 'https://api.example.com/v1/users',
                    'status_code' => 503,
                    'response_time' => '15000ms',
                    'error_message' => 'Service Unavailable',
                ]);
                break;

            case 'validation':
                Log::warning('Validation failed for user input', [
                    'error_type' => 'VALIDATION_ERROR',
                    'fields' => ['email', 'phone'],
                    'errors' => [
                        'email' => 'Invalid email format',
                        'phone' => 'Phone number too short',
                    ],
                ]);
                break;

            default:
                // Generate all scenarios
                $this->errorScenarios(new Request(['scenario' => 'database']));
                $this->errorScenarios(new Request(['scenario' => 'api']));
                $this->errorScenarios(new Request(['scenario' => 'validation']));
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Error scenarios logged',
            'scenario' => $scenario,
        ]);
    }

    /**
     * Get random log level.
     */
    private function getRandomLogLevel(): string
    {
        $levels = [
            'debug' => 30,    // 30% chance
            'info' => 40,     // 40% chance
            'warning' => 15,  // 15% chance
            'error' => 10,    // 10% chance
            'critical' => 5,  // 5% chance
        ];

        $rand = rand(1, 100);
        $cumulative = 0;

        foreach ($levels as $level => $percentage) {
            $cumulative += $percentage;
            if ($rand <= $cumulative) {
                return $level;
            }
        }

        return 'info';
    }

    /**
     * Get status code based on log level.
     */
    private function getRandomStatusCode(string $level): int
    {
        $statusCodes = [
            'debug' => [200, 201],
            'info' => [200, 201, 202],
            'warning' => [400, 401, 403, 429],
            'error' => [500, 502, 503],
            'critical' => [500, 503, 504],
        ];

        $codes = $statusCodes[$level] ?? [200];
        return $codes[array_rand($codes)];
    }

    /**
     * Generate random IP address.
     */
    private function getRandomIp(): string
    {
        return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);
    }
}
