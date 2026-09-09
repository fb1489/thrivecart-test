<?php declare(strict_types=1);

namespace Lib;

class MonetaryValue {
    public function __construct(private float $value, private Currency $currency) {}

    public function value(): float {
        return round($this->value, 2, PHP_ROUND_HALF_UP);
    }

    public function currency(): Currency {
        return $this->currency;
    }

    public function toDisplayFormat(): string {
        return $this->currency->value . number_format($this->value, 2);
    }

    public function add(MonetaryValue $monetaryValue): MonetaryValue
    {
        if ($this->currency() !== $monetaryValue->currency()) {
            throw new Exceptions\CurrencyMismatchException("Cannot add together values from different currencies");
        }

        return new MonetaryValue($this->value() + $monetaryValue->value(), $this->currency());
    }
}