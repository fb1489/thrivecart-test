<?php declare(strict_types=1);

namespace Widgets;

use Error;
use Lib\MonetaryValue;

abstract class Widget {
    abstract public function code(): WidgetCode;
    abstract public function price(): MonetaryValue;

    public static function createFrom(WidgetCode $code): static
    {
        switch($code) {
            case WidgetCode::R01: return new RedWidget();
            case WidgetCode::G01: return new GreenWidget();
            case WidgetCode::B01: return new BlueWidget();
            default: throw new Error("Widget Code not mapped to a widget");
        }
    }
}