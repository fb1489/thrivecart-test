<?php declare(strict_types=1);

namespace Tests\Cart;

use Cart\Rules\DeliveryFeesRule;
use Lib\Currency;
use Lib\MonetaryValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Widgets\WidgetFactory;

#[CoversClass(DeliveryFeesRule::class)]
class DeliveryFeesRuleTest extends TestCase
{
    #[Test]
    #[DataProvider('deliveryFeesDataProvider')]
    public function it_adds_a_delivery_fee_depending_on_the_value(MonetaryValue $total, float $expectedDeliveryCost): void
    {
        $totalWithDeliveryCost = $total->add(new MonetaryValue($expectedDeliveryCost, Currency::USD));
        $rule = new DeliveryFeesRule($this->createWidgetFactory());
        assertThat($rule->afterTotalling($total)->value(), is(identicalTo($totalWithDeliveryCost->value())));
    }
    
    public static function deliveryFeesDataProvider(): array
    {
        return [
            // Exacly 0 - Should it add a delivery fee?
            "$0.00 adds [BELOW 50] delivery fee" => [new MonetaryValue(0, Currency::USD), DeliveryFeesRule::BELOW_50],

            // Under 50$ - adds $4.95
            "$1.00 adds [BELOW 50] delivery fee" => [new MonetaryValue(1, Currency::USD), DeliveryFeesRule::BELOW_50],
            "$2.00 adds [BELOW 50] delivery fee" => [new MonetaryValue(2, Currency::USD), DeliveryFeesRule::BELOW_50],
            "$49.00 adds [BELOW 50] delivery fee" => [new MonetaryValue(49, Currency::USD), DeliveryFeesRule::BELOW_50],
            "$49.99 adds [BELOW 50] delivery fee" => [new MonetaryValue(49.99, Currency::USD), DeliveryFeesRule::BELOW_50],
            "$49.994 adds [BELOW 50] delivery fee" => [new MonetaryValue(49.994, Currency::USD), DeliveryFeesRule::BELOW_50],
            
            // under $90
            "$49.995 adds [BELOW 90] delivery fee" => [new MonetaryValue(49.995, Currency::USD), DeliveryFeesRule::BELOW_90],
            "$50.00 adds [BELOW 90] delivery fee" => [new MonetaryValue(50, Currency::USD), DeliveryFeesRule::BELOW_90],
            "$89.00 adds [BELOW 90] delivery fee" => [new MonetaryValue(89, Currency::USD), DeliveryFeesRule::BELOW_90],
            "$89.99 adds [BELOW 90] delivery fee" => [new MonetaryValue(89.99, Currency::USD), DeliveryFeesRule::BELOW_90],
            "$89.994 adds [BELOW 90] delivery fee" => [new MonetaryValue(89.994, Currency::USD), DeliveryFeesRule::BELOW_90],

            // $90 or over
            "$89.995 adds [OR OR OVER 90] delivery fee" => [new MonetaryValue(89.995, Currency::USD), DeliveryFeesRule::ON_OR_OVER_90],
            "$90.00 adds [OR OR OVER 90] delivery fee" => [new MonetaryValue(90, Currency::USD), DeliveryFeesRule::ON_OR_OVER_90],
            "$1,000,000,000.00 adds [OR OR OVER 90] delivery fee" => [new MonetaryValue(1_000_000_000, Currency::USD), DeliveryFeesRule::ON_OR_OVER_90],
        ];
    }

    private function createWidgetFactory(): WidgetFactory
    {
        return new WidgetFactory();
    }
}