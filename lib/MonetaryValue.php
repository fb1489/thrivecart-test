<?php declare(strict_types=1);

namespace Lib;

class MonetaryValue {
    public function __construct(private float $value, private Currency $currency) {}

    public function value(): float {
        return $this->value;
    }

    public function currency(): Currency {
        return $this->currency;
    }

    public function toDisplayFormat(): string {
        return $this->currency->value . number_format($this->value, 2);
    }
}