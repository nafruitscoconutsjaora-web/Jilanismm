<?php

namespace App\Services\Provider;

use App\Database\Database;

class StandardSmmProvider implements ProviderInterface
{
    protected array $provider;
    protected string $apiUrl;
    protected string $apiKey;
    protected int $timeout;

    public function __construct(array $provider, int $timeout = 30)
    {
        $this->provider = $provider;
        $this->apiUrl = trim($provider['api_url'] ?? '');
        $this->apiKey = trim($provider['api_key'] ?? '');
        $this->timeout = $timeout;
    }

    public function getBalance(): array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            return [
                'success' => false,
                'balance' => '0.0000',
                'currency' => $this->provider['currency'] ?? 'USD',
                'error' => 'Provider API URL or API Key is not configured.'
            ];
        }

        $res = $this->sendRequest(['action' => 'balance'], 'balance');

        if (!$res['success']) {
            return [
                'success' => false,
                'balance' => '0.0000',
                'currency' => $this->provider['currency'] ?? 'USD',
                'error' => $res['error'] ?? 'Failed to retrieve balance from provider.'
            ];
        }

        $data = $res['data'];
        if (isset($data['error'])) {
            return [
                'success' => false,
                'balance' => '0.0000',
                'currency' => $this->provider['currency'] ?? 'USD',
                'error' => is_string($data['error']) ? $data['error'] : json_encode($data['error'])
            ];
        }

        $balance = $data['balance'] ?? '0.0000';
        $currency = $data['currency'] ?? ($this->provider['currency'] ?? 'USD');

        // Update provider balance in database
        Database::execute(
            "UPDATE `providers` SET `balance` = :bal, `currency` = :curr WHERE `id` = :id",
            [':bal' => (float)$balance, ':curr' => $currency, ':id' => $this->provider['id']]
        );

        return [
            'success' => true,
            'balance' => (string)$balance,
            'currency' => $currency,
            'error' => null
        ];
    }

    public function getServices(): array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            return [
                'success' => false,
                'services' => [],
                'error' => 'Provider API URL or API Key is not configured.'
            ];
        }

        $res = $this->sendRequest(['action' => 'services'], 'services');

        if (!$res['success']) {
            return [
                'success' => false,
                'services' => [],
                'error' => $res['error'] ?? 'Failed to fetch services from provider.'
            ];
        }

        $data = $res['data'];
        if (isset($data['error'])) {
            return [
                'success' => false,
                'services' => [],
                'error' => is_string($data['error']) ? $data['error'] : json_encode($data['error'])
            ];
        }

        if (!is_array($data)) {
            return [
                'success' => false,
                'services' => [],
                'error' => 'Invalid services format returned by provider.'
            ];
        }

        return [
            'success' => true,
            'services' => $data,
            'error' => null
        ];
    }

    public function createOrder(array $params): array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            return [
                'success' => false,
                'order_id' => null,
                'error' => 'Provider API URL or API Key is not configured.',
                'raw' => []
            ];
        }

        $payload = array_merge(['action' => 'add'], $params);
        $orderRef = $params['internal_order_id'] ?? null;

        $res = $this->sendRequest($payload, 'create_order', $orderRef ? (int)$orderRef : null);

        if (!$res['success']) {
            return [
                'success' => false,
                'order_id' => null,
                'error' => $res['error'] ?? 'Connection to provider failed.',
                'raw' => $res['data'] ?? []
            ];
        }

        $data = $res['data'];
        if (isset($data['error'])) {
            $err = is_string($data['error']) ? $data['error'] : json_encode($data['error']);
            return [
                'success' => false,
                'order_id' => null,
                'error' => $err,
                'raw' => $data
            ];
        }

        if (isset($data['order'])) {
            return [
                'success' => true,
                'order_id' => (string)$data['order'],
                'error' => null,
                'raw' => $data
            ];
        }

        return [
            'success' => false,
            'order_id' => null,
            'error' => 'Provider did not return a valid order ID.',
            'raw' => $data
        ];
    }

    public function getOrderStatus(string|int $remoteOrderId): array
    {
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            return [
                'success' => false,
                'status' => null,
                'start_count' => null,
                'remains' => null,
                'error' => 'Provider API URL or API Key is not configured.'
            ];
        }

        $res = $this->sendRequest(['action' => 'status', 'order' => $remoteOrderId], 'order_status');

        if (!$res['success']) {
            return [
                'success' => false,
                'status' => null,
                'start_count' => null,
                'remains' => null,
                'error' => $res['error'] ?? 'Failed to check order status.'
            ];
        }

        $data = $res['data'];
        if (isset($data['error'])) {
            return [
                'success' => false,
                'status' => null,
                'start_count' => null,
                'remains' => null,
                'error' => is_string($data['error']) ? $data['error'] : json_encode($data['error'])
            ];
        }

        return [
            'success' => true,
            'status' => $data['status'] ?? null,
            'charge' => $data['charge'] ?? null,
            'start_count' => isset($data['start_count']) ? (int)$data['start_count'] : 0,
            'remains' => isset($data['remains']) ? (int)$data['remains'] : 0,
            'currency' => $data['currency'] ?? null,
            'error' => null
        ];
    }

    /**
     * Send HTTP POST request with strict security, safe logging, timeout, and JSON validation
     */
    protected function sendRequest(array $params, string $action = 'api_call', ?int $orderId = null): array
    {
        $params['key'] = $this->apiKey;

        $startTime = microtime(true);
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'SMM-Engine/2.0 (PHP ' . PHP_VERSION . ')'
        ]);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $durationMs = (int)round((microtime(true) - $startTime) * 1000);

        // Mask API key in request payload before logging
        $safeParams = $params;
        if (isset($safeParams['key'])) {
            $keyLen = strlen($safeParams['key']);
            $safeParams['key'] = $keyLen > 4 ? substr($safeParams['key'], 0, 4) . str_repeat('*', max(4, $keyLen - 4)) : '****';
        }
        $requestPayloadJson = json_encode($safeParams, JSON_UNESCAPED_SLASHES);

        $errorMessage = null;
        $decodedData = null;

        if ($responseBody === false || !empty($curlError)) {
            $errorMessage = 'cURL Connection Error: ' . $curlError;
            $this->logApiCall($action, $requestPayloadJson, null, $httpCode, $durationMs, $errorMessage, $orderId);
            return [
                'success' => false,
                'error' => $errorMessage,
                'data' => null
            ];
        }

        $decodedData = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'Invalid JSON response from provider (HTTP ' . $httpCode . '): ' . substr($responseBody, 0, 200);
            $this->logApiCall($action, $requestPayloadJson, substr($responseBody, 0, 1000), $httpCode, $durationMs, $errorMessage, $orderId);
            return [
                'success' => false,
                'error' => $errorMessage,
                'data' => null
            ];
        }

        if ($httpCode >= 400) {
            $errorMessage = 'Provider returned HTTP ' . $httpCode . ': ' . ($decodedData['error'] ?? 'Bad Request');
        }

        // Log successful or API error response safely
        $this->logApiCall($action, $requestPayloadJson, substr($responseBody, 0, 3000), $httpCode, $durationMs, $errorMessage, $orderId);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300) && !isset($decodedData['error']),
            'data' => $decodedData,
            'error' => $errorMessage ?? ($decodedData['error'] ?? null)
        ];
    }

    /**
     * Store safe log into provider_api_logs
     */
    protected function logApiCall(string $action, string $request, ?string $response, int $statusCode, int $durationMs, ?string $error, ?int $orderId = null): void
    {
        try {
            Database::execute(
                "INSERT INTO `provider_api_logs` (`provider_id`, `order_id`, `endpoint`, `action`, `request_payload`, `response_payload`, `status_code`, `duration_ms`, `error_message`, `created_at`) 
                 VALUES (:pid, :oid, :endpoint, :action, :req, :resp, :code, :dur, :err, NOW())",
                [
                    ':pid' => $this->provider['id'] ?? null,
                    ':oid' => $orderId,
                    ':endpoint' => $this->apiUrl,
                    ':action' => $action,
                    ':req' => $request,
                    ':resp' => $response,
                    ':code' => $statusCode,
                    ':dur' => $durationMs,
                    ':err' => $error
                ]
            );
        } catch (\Throwable $e) {
            // Silently continue if logging encounters temporary issue
            error_log('Failed to log provider API call: ' . $e->getMessage());
        }
    }
}
