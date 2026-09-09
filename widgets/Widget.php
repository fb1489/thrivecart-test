<?php declare(strict_types=1);

namespace Widgets;

use Lib\MonetaryValue;

abstract class Widget {
    abstract public function code(): WidgetCode;
    abstract public function price(): MonetaryValue;
}