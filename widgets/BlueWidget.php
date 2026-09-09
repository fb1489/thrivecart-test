<?php declare(strict_types=1);

namespace Widgets;

use Lib\Currency;
use Lib\MonetaryValue;

class BlueWidget extends Widget {

    public function code(): WidgetCode
    {
        return WidgetCode::B01;
    }

    public function price(): MonetaryValue
    {
        return new MonetaryValue(7.95, Currency::USD);
    }
}