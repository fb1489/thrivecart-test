<?php declare(strict_types=1);

namespace Tests\Cart;

use Cart\Cart;
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
        $cart = new Cart(Currency::USD);

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
}