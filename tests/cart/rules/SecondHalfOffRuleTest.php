<?php declare(strict_types=1);

namespace Tests\Cart;

use Cart\Cart;
use Cart\Rules\SecondHalfOffRule;
use Lib\Currency;
use Lib\MonetaryValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Widgets\Widget;
use Widgets\WidgetCode;
use Widgets\WidgetFactory;

#[CoversClass(SecondHalfOffRule::class)]
class SecondHalfOffRuleTest extends TestCase
{
    #[Test]
    public function it_adds_a_half_value_discount_when_there_are_two_of_red_widget(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $widget = $widgetFactory->createFromCode(WidgetCode::R01);

        $cart->add($widget->code());
        $cart->add($widget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValueOfBothWidgets = $widget->price()->add($widget->price());
        $discountValue = new MonetaryValue($widget->price()->value() / 2, $widget->price()->currency());
        $totalValueWithDiscount = $totalValueOfBothWidgets->subtract($discountValue);

        assertThat($rule->afterTotalling($totalValueOfBothWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }
    
    #[Test]
    public function it_does_not_add_any_discount_when_there_is_only_one_of_each_item(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $redWidget = $widgetFactory->createFromCode(WidgetCode::R01);
        $greenWidget = $widgetFactory->createFromCode(WidgetCode::G01);
        $blueWidget = $widgetFactory->createFromCode(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValue = $redWidget->price()
            ->add($greenWidget->price())
            ->add($blueWidget->price());

        assertThat($rule->afterTotalling($totalValue)->value(), is(identicalTo($totalValue->value())));
    }
    
    #[Test]
    public function it_does_not_add_any_discount_when_there_is_only_two_of_the_other_widgets(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $greenWidget = $widgetFactory->createFromCode(WidgetCode::G01);
        $blueWidget = $widgetFactory->createFromCode(WidgetCode::B01);

        $cart->add($greenWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValue = $greenWidget->price()->add($blueWidget->price());
        assertThat($rule->afterTotalling($totalValue)->value(), is(identicalTo($totalValue->value())));
    }

    #[Test]
    public function it_adds_a_half_value_discount_only_for_the_red_widget_when_there_are_two_of_the_same_item_for_each_widget(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $redWidget = $widgetFactory->createFromCode(WidgetCode::R01);
        $greenWidget = $widgetFactory->createFromCode(WidgetCode::G01);
        $blueWidget = $widgetFactory->createFromCode(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValueOfAllWidgets = $redWidget->price()
            ->add($redWidget->price())
            ->add($greenWidget->price())
            ->add($greenWidget->price())
            ->add($blueWidget->price())
            ->add($blueWidget->price());

        $redWidgetDiscountValue = new MonetaryValue($redWidget->price()->value() / 2, $redWidget->price()->currency());
        $totalValueWithDiscount = $totalValueOfAllWidgets->subtract($redWidgetDiscountValue);

        assertThat($rule->afterTotalling($totalValueOfAllWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }

    #[Test]
    public function it_adds_a_half_value_discount_only_for_the_red_widget_that_is_eligible_and_not_for_other_widgets_that_only_have_one_in_the_cart(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $redWidget = $widgetFactory->createFromCode(WidgetCode::R01);
        $greenWidget = $widgetFactory->createFromCode(WidgetCode::G01);
        $blueWidget = $widgetFactory->createFromCode(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValueOfAllWidgets = $redWidget->price()
            ->add($redWidget->price())
            ->add($greenWidget->price())
            ->add($blueWidget->price());

        $redWidgetDiscountValue = new MonetaryValue($redWidget->price()->value() / 2, $redWidget->price()->currency());
        $totalValueWithDiscount = $totalValueOfAllWidgets->subtract($redWidgetDiscountValue);

        assertThat($rule->afterTotalling($totalValueOfAllWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }

    #[Test]
    public function it_only_applies_the_half_value_discounts_once_when_there_are_more_than_two_of_the_same_item(): void
    {
        $widgetFactory = $this->createWidgetFactory();
        $cart = new Cart($widgetFactory, Currency::USD);
        $widget = $widgetFactory->createFromCode(WidgetCode::R01);

        $cart->add($widget->code());
        $cart->add($widget->code());
        $cart->add($widget->code());
        $cart->add($widget->code());

        $rule = new SecondHalfOffRule($widgetFactory);
        $rule->beforeTotalling($cart);

        $totalValueOfAllWidgets = $widget->price()
            ->add($widget->price())
            ->add($widget->price())
            ->add($widget->price());

        $discountValue = new MonetaryValue($widget->price()->value() / 2, $widget->price()->currency());
        $totalValueWithDiscount = $totalValueOfAllWidgets->subtract($discountValue);

        assertThat($rule->afterTotalling($totalValueOfAllWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }

    private function createWidgetFactory(): WidgetFactory
    {
        return new WidgetFactory();
    }
}