<?php

namespace App\Services\Provider;

interface ProviderInterface
{
    /**
     * Fetch balance from provider API
     * Returns: ['success' => bool, 'balance' => string, 'currency' => string, 'error' => ?string]
     */
    public function getBalance(): array;

    /**
     * Fetch service catalogue from provider API
     * Returns: ['success' => bool, 'services' => array, 'error' => ?string]
     */
    public function getServices(): array;

    /**
     * Submit an order to the provider API
     * Returns: ['success' => bool, 'order_id' => ?string, 'error' => ?string, 'raw' => array]
     */
    public function createOrder(array $params): array;

    /**
     * Fetch order status from provider API
     * Returns: ['success' => bool, 'status' => ?string, 'start_count' => ?int, 'remains' => ?int, 'error' => ?string]
     */
    public function getOrderStatus(string|int $remoteOrderId): array;
}
