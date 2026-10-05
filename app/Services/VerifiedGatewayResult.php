<?php

namespace App\Services;

// Internal DTO, constructed only from authenticated GET status response by MidtransGateway.
final readonly class VerifiedGatewayResult
{
    public function __construct(public string $reference, public string $transactionId, public int $amount, public string $currency, public string $gatewayStatus, public ?string $fraudStatus, public string $status) {}

    public function key(): string
    {
        return hash('sha256', implode('|', [$this->reference, $this->transactionId, $this->amount, $this->currency, $this->gatewayStatus, $this->fraudStatus ?? '']));
    }
}
