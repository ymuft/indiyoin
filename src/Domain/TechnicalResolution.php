<?php

declare(strict_types=1);

namespace Indiyoin\Domain;

final readonly class TechnicalResolution
{
    /** @param list<TechnicalParameter> $candidates */
    private function __construct(
        public string $status,
        public array $candidates,
    ) {}

    public static function matched(TechnicalParameter $parameter): self
    {
        return new self('MATCHED', [$parameter]);
    }

    /** @param list<TechnicalParameter> $candidates */
    public static function ambiguous(array $candidates): self
    {
        return new self('AMBIGUOUS', $candidates);
    }

    public static function unresolved(): self
    {
        return new self('UNRESOLVED', []);
    }

    public function parameter(): ?TechnicalParameter
    {
        return $this->status === 'MATCHED' ? $this->candidates[0] : null;
    }
}
