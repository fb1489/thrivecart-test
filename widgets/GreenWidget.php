<?php declare(strict_types=1);

namespace Widgets;

use Lib\Currency;
use Lib\MonetaryValue;

class GreenWidget extends Widget {

    public function code(): WidgetCode
    {
        return WidgetCode::G01;
    }

    public function price(): MonetaryValue
    {
        return new MonetaryValue(24.95, Currency::USD);
    }
}