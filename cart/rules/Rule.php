<?php declare(strict_types=1);

namespace Cart\Rules;

use Cart\Cart;
use Lib\MonetaryValue;

abstract class Rule {
    public function beforeTotalling(Cart $cart): Cart
    {
        return $cart;
    }

    public function afterTotalling(MonetaryValue $total): MonetaryValue
    {
        return $total;
    }
}