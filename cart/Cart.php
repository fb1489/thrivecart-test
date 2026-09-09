<?php declare(strict_types=1);

namespace Cart;

use Cart\Rules\Rule;
use Lib\Currency;
use Lib\MonetaryValue;
use Widgets\ListOfWidgets;
use Widgets\WidgetCode;
use Widgets\WidgetFactory;

class Cart {

    private ListOfWidgets $widgets;
    private array $rules;

    public function __construct(private WidgetFactory $widgetFactory, private Currency $currency, Rule ...$rules)
    {
        $this->widgets = new ListOfWidgets();
        $this->rules = $rules;
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
        $this->widgets->add($this->widgetFactory->createFromCode($widgetCode));
        return $this;
    }

    public function total(): MonetaryValue
    {
        $cart = $this;
        foreach ($this->rules as $rule) {
            $cart = $rule->beforeTotalling($cart);
        }

        $total = new MonetaryValue(0, $cart->currency);
        foreach ($cart->widgets->toArray() as $widget) {
            $total = $total->add($widget->price());
        }

        foreach ($this->rules as $rule) {
            $total = $rule->afterTotalling($total);
        }

        return $total;
    }

}