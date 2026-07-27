<?php

namespace App\Services\Cdp;

class CdpInjectionResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $rewardId,
        public readonly ?int $originProId,
        public readonly ?string $errorMessage = null,
        public readonly ?int $httpStatus = null,
        public readonly ?array $rawResponse = null,
    ) {
    }

    public static function created(int $rewardId, ?int $originProId): self
    {
        return new self('created', $rewardId, $originProId);
    }

    public static function orphan(): self
    {
        return new self('orphan', null, null);
    }

    public static function duplicate(int $rewardId, ?int $originProId): self
    {
        return new self('duplicate', $rewardId, $originProId);
    }

    public static function error(string $message, ?int $status = null, ?array $raw = null): self
    {
        return new self('error', null, null, $message, $status, $raw);
    }
}