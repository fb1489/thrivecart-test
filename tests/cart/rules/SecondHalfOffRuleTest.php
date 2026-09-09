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

#[CoversClass(SecondHalfOffRule::class)]
class SecondHalfOffRuleTest extends TestCase
{
    #[Test]
    public function it_adds_a_half_value_discount_when_there_are_two_of_the_same_item(): void
    {
        $cart = new Cart(Currency::USD);
        $widget = Widget::createFrom(WidgetCode::R01);

        $cart->add($widget->code());
        $cart->add($widget->code());

        $rule = new SecondHalfOffRule();
        $rule->beforeTotalling($cart);

        $totalValueOfBothWidgets = $widget->price()->add($widget->price());
        $discountValue = new MonetaryValue($widget->price()->value() / 2, $widget->price()->currency());
        $totalValueWithDiscount = $totalValueOfBothWidgets->subtract($discountValue);

        assertThat($rule->afterTotalling($totalValueOfBothWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }
    
    #[Test]
    public function it_does_not_add_any_discount_when_there_is_only_one_of_each_item(): void
    {
        $cart = new Cart(Currency::USD);
        $redWidget = Widget::createFrom(WidgetCode::R01);
        $greenWidget = Widget::createFrom(WidgetCode::G01);
        $blueWidget = Widget::createFrom(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule();
        $rule->beforeTotalling($cart);

        $totalValue = $redWidget->price()
            ->add($greenWidget->price())
            ->add($blueWidget->price());

        assertThat($rule->afterTotalling($totalValue)->value(), is(identicalTo($totalValue->value())));
    }

    #[Test]
    public function it_adds_a_half_value_discount_per_widget_when_there_are_two_of_the_same_item_for_each_widget(): void
    {
        $cart = new Cart(Currency::USD);
        $redWidget = Widget::createFrom(WidgetCode::R01);
        $greenWidget = Widget::createFrom(WidgetCode::G01);
        $blueWidget = Widget::createFrom(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule();
        $rule->beforeTotalling($cart);

        $totalValueOfAllWidgets = $redWidget->price()
            ->add($redWidget->price())
            ->add($greenWidget->price())
            ->add($greenWidget->price())
            ->add($blueWidget->price())
            ->add($blueWidget->price());

        $redWidgetDiscountValue = new MonetaryValue($redWidget->price()->value() / 2, $redWidget->price()->currency());
        $greenWidgetDiscountValue = new MonetaryValue($greenWidget->price()->value() / 2, $greenWidget->price()->currency());
        $blueWidgetDiscountValue = new MonetaryValue($blueWidget->price()->value() / 2, $blueWidget->price()->currency());

        $totalValueWithDiscount = $totalValueOfAllWidgets
            ->subtract($redWidgetDiscountValue)
            ->subtract($greenWidgetDiscountValue)
            ->subtract($blueWidgetDiscountValue);

        assertThat($rule->afterTotalling($totalValueOfAllWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }

    #[Test]
    public function it_adds_a_half_value_discount_only_for_a_widget_that_is_eligible_and_not_for_other_widgets_that_only_have_one_in_the_cart(): void
    {
        $cart = new Cart(Currency::USD);
        $redWidget = Widget::createFrom(WidgetCode::R01);
        $greenWidget = Widget::createFrom(WidgetCode::G01);
        $blueWidget = Widget::createFrom(WidgetCode::B01);

        $cart->add($redWidget->code());
        $cart->add($redWidget->code());
        $cart->add($greenWidget->code());
        $cart->add($blueWidget->code());

        $rule = new SecondHalfOffRule();
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
    public function it_does_not_adds_a_half_value_discount_when_there_are_more_than_two_of_the_same_item(): void
    {
        $cart = new Cart(Currency::USD);
        $widget = Widget::createFrom(WidgetCode::R01);

        $cart->add($widget->code());
        $cart->add($widget->code());
        $cart->add($widget->code());
        $cart->add($widget->code());

        $rule = new SecondHalfOffRule();
        $rule->beforeTotalling($cart);

        $totalValueOfAllWidgets = $widget->price()
            ->add($widget->price())
            ->add($widget->price())
            ->add($widget->price());

        $discountValue = new MonetaryValue($widget->price()->value() / 2, $widget->price()->currency());
        $totalValueWithDiscount = $totalValueOfAllWidgets->subtract($discountValue);

        assertThat($rule->afterTotalling($totalValueOfAllWidgets)->value(), is(identicalTo($totalValueWithDiscount->value())));
    }
}