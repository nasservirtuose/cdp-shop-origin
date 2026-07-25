<?php

namespace App\Services\Rex;

class RexDrawResult
{
    private function __construct(
        public readonly bool $valid,
        public readonly ?float $amount = null,
        public readonly ?int $tierNumber = null,
        public readonly ?float $tierMin = null,
        public readonly ?float $tierMax = null,
        public readonly ?int $tierProbability = null,
        public readonly ?string $invalidReason = null,
        public readonly ?string $invalidMessage = null,
        public readonly array $economicSnapshot = [],
    ) {
    }

    public static function valid(
        float $amount,
        int $tierNumber,
        float $tierMin,
        float $tierMax,
        int $tierProbability,
        array $snapshot,
    ): self {
        return new self(
            valid: true,
            amount: $amount,
            tierNumber: $tierNumber,
            tierMin: $tierMin,
            tierMax: $tierMax,
            tierProbability: $tierProbability,
            economicSnapshot: $snapshot,
        );
    }

    public static function invalid(string $reason, string $message = '', array $snapshot = []): self
    {
        return new self(
            valid: false,
            invalidReason: $reason,
            invalidMessage: $message,
            economicSnapshot: $snapshot,
        );
    }

    public function drawContext(): array
    {
        return [
            'tier' => $this->tierNumber,
            'tier_min' => $this->tierMin,
            'tier_max' => $this->tierMax,
            'tier_probability' => $this->tierProbability,
        ];
    }
}
