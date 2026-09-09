<?php declare(strict_types=1);

namespace Widgets;

use Lib\Currency;
use Lib\MonetaryValue;

class RedWidget extends Widget {

    public function code(): WidgetCode
    {
        return WidgetCode::R01;
    }

    public function price(): MonetaryValue
    {
        return new MonetaryValue(32.95, Currency::USD);
    }
}