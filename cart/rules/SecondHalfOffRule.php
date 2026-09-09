<?php declare(strict_types=1);

namespace Cart\Rules;

use Cart\Cart;
use Lib\MonetaryValue;
use Override;
use Widgets\ListOfWidgetCodes;
use Widgets\ListOfWidgets;

class SecondHalfOffRule extends Rule {

    private MonetaryValue $discountValue;

    #[Override]
    public function beforeTotalling(Cart $cart): Cart
    {
        $this->discountValue = new MonetaryValue(0, $cart->currency());

        $processedWidgets = new ListOfWidgets();
        $alreadyDiscountedWidgetCodes = new ListOfWidgetCodes();

        foreach ($cart->widgets()->toArray() as $widget) {
            if ($alreadyDiscountedWidgetCodes->includes($widget->code())) {
                continue;
            }

            if ($processedWidgets->includes($widget)) {
                $halfPrice = new MonetaryValue(
                    round($widget->price()->value() / 2, 2),
                    $this->discountValue->currency()
                );
                $this->discountValue = $this->discountValue->add($halfPrice);
                $alreadyDiscountedWidgetCodes->add($widget->code());
            }

            $processedWidgets->add($widget);
        }


        return $cart;
    }

    #[Override]
    public function afterTotalling(MonetaryValue $total): MonetaryValue
    {
        return $total->subtract($this->discountValue);
    }
}