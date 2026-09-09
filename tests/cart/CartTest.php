<?php declare(strict_types=1);

namespace Tests\Cart;

use Cart\Cart;
use Cart\Rules\DeliveryFeesRule;
use Cart\Rules\SecondHalfOffRule;
use Lib\Currency;
use Lib\MonetaryValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Widgets\ListOfWidgetCodes;
use Widgets\Widget;
use Widgets\WidgetCode;
use Widgets\WidgetFactory;

#[CoversClass(Cart::class)]
class CartTest extends TestCase
{
    #[Test]
    #[DataProvider('technicalTestTotalExamples')]
    public function it_returns_the_correct_total_as_per_the_technical_test_document(ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $widgetFactory = $this->createWidgetFactory();

        $cart = new Cart(
            $widgetFactory,
            Currency::USD,
            new SecondHalfOffRule($widgetFactory),
            new DeliveryFeesRule($widgetFactory),
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
    public function it_returns_the_total_of_the_given_widgets_if_no_rules_are_given(array $widgets, ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $widgetFactory = $this->mockWidgetFactoryWith($widgets);
        $cart = new Cart($widgetFactory, Currency::USD);

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function cartTotalDataProvider(): array
    {
        $widgets = [
            WidgetCode::R01->value => 100,
            WidgetCode::G01->value => 10,
            WidgetCode::B01->value => 1,
        ];

        return [
            "[B01, G01] total of $11.00" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$11.00'
            ],
            "[R01, G01, B01] total of $111.00" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01, WidgetCode::B01),
                '$111.00'
            ],
            "[R01, R01, R01, G01] total of $310.00" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01, WidgetCode::R01, WidgetCode::G01),
                '$310.00'
            ],
        ];
    }
    
    #[Test]
    #[DataProvider('cartTotalWithDeliveryFeesDataProvider')]
    public function it_processes_the_delivery_fees_rule_according_to_the_total_value_of_the_cart(float $widgetValue, float $expectedDeliveryFee): void
    {
        $widgetFactory = $this->mockWidgetFactoryWith([
            WidgetCode::B01->value => $widgetValue,
        ]);

        $cart = new Cart(
            $widgetFactory,
            Currency::USD,
            new DeliveryFeesRule($widgetFactory)
        );

        $cart->add(WidgetCode::B01);

        $widgetMonetaryValue = new MonetaryValue($widgetValue, Currency::USD);
        $deliveryFeeMonetaryValue = new MonetaryValue($expectedDeliveryFee, Currency::USD);

        $totalValueWithDeliveryFee = $widgetMonetaryValue->add($deliveryFeeMonetaryValue);        
        assertThat($cart->total()->value(), is(identicalTo($totalValueWithDeliveryFee->value())));
    }

    public static function cartTotalWithDeliveryFeesDataProvider(): array
    {
        return [
            // Exacly 0 - Should it add a delivery fee?
            "$0.00 adds [BELOW 50] delivery fee" => [0, DeliveryFeesRule::BELOW_50],

            // Under 50$
            "$1.00 adds [BELOW 50] delivery fee" => [1, DeliveryFeesRule::BELOW_50],
            "$2.00 adds [BELOW 50] delivery fee" => [2, DeliveryFeesRule::BELOW_50],
            "$49.00 adds [BELOW 50] delivery fee" => [49, DeliveryFeesRule::BELOW_50],
            "$49.99 adds [BELOW 50] delivery fee" => [49.99, DeliveryFeesRule::BELOW_50],
            
            // under $90
            "$50.00 adds [BELOW 90] delivery fee" => [50, DeliveryFeesRule::BELOW_90],
            "$89.00 adds [BELOW 90] delivery fee" => [89, DeliveryFeesRule::BELOW_90],
            "$89.99 adds [BELOW 90] delivery fee" => [89.99, DeliveryFeesRule::BELOW_90],
            "$89.994 adds [BELOW 90] delivery fee" => [89.994, DeliveryFeesRule::BELOW_90],

            // $90 or over
            "$90.00 adds [OR OR OVER 90] delivery fee" => [90, DeliveryFeesRule::ON_OR_OVER_90],
            "$1,000,000,000.00 adds [OR OR OVER 90] delivery fee" => [1_000_000_000, DeliveryFeesRule::ON_OR_OVER_90],
        ];
    }
    
    #[Test]
    #[DataProvider('cartTotalWithSecondHalfOffDataProvider')]
    public function it_processes_the_second_half_off_rule_according_to_the_widgets_in_the_cart(array $widgets, ListOfWidgetCodes $widgetCodes, string $expectedValue): void
    {
        $widgetFactory = $this->mockWidgetFactoryWith($widgets);
        $cart = new Cart(
            $widgetFactory,
            Currency::USD,
            new SecondHalfOffRule($widgetFactory),
        );

        foreach ($widgetCodes->toArray() as $widgetCode) {
            $cart->add($widgetCode);
        }

        assertThat($cart->total()->toDisplayFormat(), is(identicalTo($expectedValue)));
    }

    public static function cartTotalWithSecondHalfOffDataProvider(): array
    {
        $widgets = [
            WidgetCode::R01->value => 100,
            WidgetCode::G01->value => 10,
            WidgetCode::B01->value => 1,
        ];

        return [
            "[B01, G01] total of $11.00 (no deal)" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::B01, WidgetCode::G01),
                '$11.00'
            ],
            "[R01, G01, B01] total of $111.00 (no deal)" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::G01, WidgetCode::B01),
                '$111.00'
            ],
            "[R01, R01, R01, R01] total of $400.00 and one half-off (-$50.00) of one red widget only" => [
                $widgets,
                new ListOfWidgetCodes(WidgetCode::R01, WidgetCode::R01, WidgetCode::R01, WidgetCode::R01),
                '$350.00'
            ],
        ];
    }
    
    #[Test]
    public function it_processes_the_rules_in_order_so_the_second_hand_off_is_applied_before_adding_the_delivery_fees(): void
    {
        $widgetFactory = $this->mockWidgetFactoryWith([
            WidgetCode::R01->value => 32.95,
            WidgetCode::G01->value => 24.95,
            WidgetCode::B01->value => 7.95,
        ]);

        $cart = new Cart(
            $widgetFactory,
            Currency::USD,
            new SecondHalfOffRule($widgetFactory),
            new DeliveryFeesRule($widgetFactory),
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
        $widgetFactory = $this->mockWidgetFactoryWith([
            WidgetCode::R01->value => 32.95,
            WidgetCode::G01->value => 24.95,
            WidgetCode::B01->value => 7.95,
        ]);

        $cart = new Cart(
            $widgetFactory,
            Currency::USD,
            new SecondHalfOffRule($widgetFactory),
            new DeliveryFeesRule($widgetFactory),
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

    private function createWidgetFactory(): WidgetFactory
    {
        return new WidgetFactory();
    }

    private function mockWidget(WidgetCode $code, MonetaryValue $price): Widget
    {
        $widget = $this->createStub(Widget::class);

        $widget->method('code')->willReturn($code);
        $widget->method('price')->willReturn($price);

        return $widget;
    }

    private function mockWidgetFactoryWith(array $widgetsByCode): WidgetFactory
    {
        $factory = $this->createStub(WidgetFactory::class);

        $factory->method('createFromCode')
            ->willReturnCallback(
                fn(WidgetCode $code) => $this->mockWidget($code, new MonetaryValue($widgetsByCode[$code->name], Currency::USD))
                    ?? throw new \RuntimeException("No mock widget configured for code [{$code->name}]")
            );

        return $factory;
    }
}