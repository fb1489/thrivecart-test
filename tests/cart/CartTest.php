<?php declare(strict_types=1);

namespace Tests\Cart;

use Cart\Cart;
use Cart\Rules\DeliveryFeesRule;
use Cart\Rules\SecondHalfOffRule;
use Lib\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Widgets\ListOfWidgetCodes;
use Widgets\WidgetCode;

#[CoversClass(Cart::class)]
class CartTest extends TestCase
{
    #[Test]
    #[DataProvider('technicalTestTotalExamples')]
    public function it_returns_the_correct_total_as_per_the_technical_test_document(ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $cart = new Cart(
            Currency::USD,
            new SecondHalfOffRule(),
            new DeliveryFeesRule(),
        );

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function technicalTestTotalExamples(): array
    {
        return [
            "[B01, G01] w/ $4.95 delivery for a total of $37.85" => [
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$37.85'
            ],
            "[R01, R01] w/ special deal second half price and w/ $4.95 delivery for a total of $54.37" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01),
                '$54.37'
            ],
            "[R01, G01] w/ $2.95 delivery for a total of $60.85" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01),
                '$60.85'
            ],
            "[B01, B01, R01, R01, R01] w/ special deal second half price and w/ free delivery for a total of $98.27" => [
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::B01, WidgetCode::R01, WidgetCode::R01, WidgetCode::R01),
                '$98.27'
            ],
        ];
    }
    
    #[Test]
    #[DataProvider('cartTotalDataProvider')]
    public function it_returns_the_total_of_the_given_widgets_if_no_rules_are_given(ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $cart = new Cart(Currency::USD);

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function cartTotalDataProvider(): array
    {
        return [
            "[B01, G01] total of $32.90" => [
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$32.90'
            ],
            "[R01, G01, B01] total of $65.85" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01, WidgetCode::B01),
                '$65.85'
            ],
            "[R01, R01, R01, G01] total of $123.80" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01, WidgetCode::R01, WidgetCode::G01),
                '$123.80'
            ],
        ];
    }
    
    #[Test]
    #[DataProvider('cartTotalWithDeliveryFeesDataProvider')]
    public function it_processes_the_delivery_fees_rule_according_to_the_total_value_of_the_cart(ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $cart = new Cart(Currency::USD, new DeliveryFeesRule());

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function cartTotalWithDeliveryFeesDataProvider(): array
    {
        return [
            "[B01, G01] total of $32.90 + $4.95 delivery (under $50)" => [
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$37.85'
            ],
            "[R01, G01, B01] total of $65.85 + $2.95 delivery (under $90)" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01, WidgetCode::B01),
                '$68.80'
            ],
            "[R01, R01, R01, G01] total of $123.80 and no delivery fee (over $90)" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01, WidgetCode::R01, WidgetCode::G01),
                '$123.80'
            ],
        ];
    }
    
    #[Test]
    #[DataProvider('cartTotalWithSecondHalfOffDataProvider')]
    public function it_processes_the_second_half_off_rule_according_to_the_widgets_in_the_cart(ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $cart = new Cart(Currency::USD, new SecondHalfOffRule());

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function cartTotalWithSecondHalfOffDataProvider(): array
    {
        return [
            "[B01, G01] total of $32.90 (no deal)" => [
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$32.90'
            ],
            "[R01, G01, B01] total of $65.85 (no deal)" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01, WidgetCode::B01),
                '$65.85'
            ],
            "[R01, R01, R01, R01] total of $131.80 and one half-off (-$16.48) of one red widget only" => [
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01, WidgetCode::R01, WidgetCode::R01),
                '$115.32'
            ],
        ];
    }
    
    #[Test]
    public function it_processes_the_rules_in_order_so_the_second_hand_off_is_applied_before_adding_the_delivery_fees(): void
    {
        $cart = new Cart(
            Currency::USD,
            new SecondHalfOffRule(),
            new DeliveryFeesRule(),
        );
        
        $cart->add(WidgetCode::R01);
        $cart->add(WidgetCode::R01);

        // One red widget is $32.95 + one half of at $16.48 = $49.42
        // $49.42 is under $50, so it applies the highest delivery fee (under $50)
        // which is $4.95 so it totals to $54.37
        assertThat($cart->total()->toDisplayFormat(), is(identicalTo('$54.37')));
    }
    
    #[Test]
    public function it_processes_the_value_for_a_cart_with_many_items(): void
    {
        $cart = new Cart(
            Currency::USD,
            new SecondHalfOffRule(),
            new DeliveryFeesRule(),
        );
        
        // 5 reds - $148.27 (1 half-off)
        $cart->add(WidgetCode::R01);
        $cart->add(WidgetCode::R01);
        $cart->add(WidgetCode::R01);
        $cart->add(WidgetCode::R01);
        $cart->add(WidgetCode::R01);

        // 3 greens - $74.85
        $cart->add(WidgetCode::G01);
        $cart->add(WidgetCode::G01);
        $cart->add(WidgetCode::G01);

        // 4 blue - $31.80
        $cart->add(WidgetCode::B01);
        $cart->add(WidgetCode::B01);
        $cart->add(WidgetCode::B01);
        $cart->add(WidgetCode::B01);

        // Total - $254.92 (no delivery fee)
        assertThat($cart->total()->toDisplayFormat(), is(identicalTo('$254.92')));
    }
}