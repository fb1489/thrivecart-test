<?php declare(strict_types=1);

namespace Cart;

use Lib\Currency;
use Lib\MonetaryValue;
use Widgets\ListOfWidgets;
use Widgets\Widget;
use Widgets\WidgetCode;

class Cart {

    private ListOfWidgets $widgets;

    public function __construct(private Currency $currency)
    {
        $this->widgets = new ListOfWidgets();
    }

    public function currency(): Currency
    {
        return $this->currency;
    }

    public function widgets(): ListOfWidgets
    {
        return $this->widgets;
    }

    public function add(WidgetCode $widgetCode): self
    {
        $this->widgets->add(Widget::createFrom($widgetCode));
        return $this;
    }

    public function total(): MonetaryValue
    {
        $total = new MonetaryValue(0, $this->currency);

        foreach ($this->widgets->toArray() as $widget) {
            $total = $total->add($widget->price());
        }

        return $total;
    }

}