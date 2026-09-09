<?php declare(strict_types=1);

namespace Widgets;

class WidgetFactory {
    public function createFromCode(WidgetCode $code): Widget {
        switch($code) {
            case WidgetCode::R01: return new RedWidget();
            case WidgetCode::G01: return new GreenWidget();
            case WidgetCode::B01: return new BlueWidget();
            default: throw new \RuntimeException("Widget Code [{$code}] not mapped to a widget");
        }
    }
}