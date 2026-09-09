<?php declare(strict_types=1);

namespace Cart\Rules;

use Cart\Cart;
use Lib\MonetaryValue;
use Override;
use Widgets\WidgetCode;
use Widgets\WidgetFactory;

class SecondHalfOffRule extends Rule {

    private MonetaryValue $discountValue;

    #[Override]
    public function beforeTotalling(Cart $cart): Cart
    {
        $redWidget = $this->widgetFactory->createFromCode(WidgetCode::R01);
        $this->discountValue = new MonetaryValue(0, $cart->currency());

        $numberOfRedWidgets = array_reduce(
            $cart->widgets()->toArray(),
            fn ($count, $widget) => $count + ($widget->code() === $redWidget->code() ? 1 : 0),
            0
        );

        if ($numberOfRedWidgets > 1) {
            $halfPrice = new MonetaryValue(
                round($redWidget->price()->value() / 2, 2),
                $this->discountValue->currency()
            );

            $this->discountValue = $this->discountValue->add($halfPrice);
        }

        return $cart;
    }

    #[Override]
    public function afterTotalling(MonetaryValue $total): MonetaryValue
    {
        return $total->subtract($this->discountValue);
    }
}