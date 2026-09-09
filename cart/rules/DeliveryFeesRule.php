<?php declare(strict_types=1);

namespace Cart\Rules;

use Lib\MonetaryValue;
use Override;
use Widgets\WidgetFactory;

class DeliveryFeesRule extends Rule {

    public const BELOW_50 = 4.95;
    public const BELOW_90 = 2.95;
    public const ON_OR_OVER_90 = 0;

    #[Override]
    public function afterTotalling(MonetaryValue $total): MonetaryValue
    {
        if ($total->value() < 50) {
            $deliveryFee = new MonetaryValue(self::BELOW_50, $total->currency());
        } elseif ($total->value() < 90) {
            $deliveryFee = new MonetaryValue(self::BELOW_90, $total->currency());
        } else {
            $deliveryFee = new MonetaryValue(self::ON_OR_OVER_90, $total->currency());
        }

        return $total->add($deliveryFee);
    }
}