<?php declare(strict_types=1);

namespace Tests\Lib;

use Lib\Currency;
use Lib\Exceptions\CurrencyMismatchException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Lib\MonetaryValue;

#[CoversClass(MonetaryValue::class)]
class MonetaryValueTest extends TestCase
{
    #[Test]
    #[DataProvider('toDisplayFormatDataProvider')]
    public function toDisplayFormat_returns_the_monetary_value_in_the_expect_format(MonetaryValue $monetaryValue, string $expectedDisplayFormat): void
    {
        assertThat($monetaryValue->toDisplayFormat(), is(identicalTo($expectedDisplayFormat)));
    }

    public static function toDisplayFormatDataProvider(): array
    {
        return [
            "A value in GBP" => [new MonetaryValue(100, Currency::GBP), '£100.00' ],
            "A value in USD" => [new MonetaryValue(200, Currency::USD), '$200.00' ],
            "A value in GBP with thousands separator" => [new MonetaryValue(1234567890, Currency::GBP), '£1,234,567,890.00' ],
            "A value in USD with thousands separator" => [new MonetaryValue(1234567890, Currency::USD), '$1,234,567,890.00' ],
        ];
    }

    #[Test]
    #[DataProvider('roundedValuesDataProvider')]
    public function it_rounds_values_when_they_have_more_than_two_decimal_places(MonetaryValue $monetaryValue, float $expectedValue): void
    {
        assertThat($monetaryValue->value(), is(identicalTo($expectedValue)));
    }

    public static function roundedValuesDataProvider(): array
    {
        return [
            "[Half-way point rounds up] 1.125 rounds to 1.13" => [new MonetaryValue(1.125, Currency::USD), 1.13 ],
            "[Below half-way point rounds down] 1.124 rounds to 1.12" => [new MonetaryValue(1.124, Currency::USD), 1.12 ],
            "[Above half-way point rounds up] 1.126 rounds to 1.16" => [new MonetaryValue(1.124, Currency::USD), 1.12 ],
        ];
    }
    
    #[Test]
    #[DataProvider('monetaryValuesAddDataProvider')]
    public function add_sums_the_monetary_values(MonetaryValue $monetaryValue, MonetaryValue $monetaryValueToAdd, float $expectedValue): void
    {
        assertThat($monetaryValue->add($monetaryValueToAdd)->value(), is(identicalTo($expectedValue)));
    }

    public static function monetaryValuesAddDataProvider(): array
    {
        return [
            "1 + 1 = 2" => [new MonetaryValue(1, Currency::USD), new MonetaryValue(1, Currency::USD), 2],
            "1.5 + 1.5 = 3" => [new MonetaryValue(1.5, Currency::USD), new MonetaryValue(1.5, Currency::USD), 3],
            "1,500 + 312.59 = 1,812.59" => [new MonetaryValue(1_500, Currency::USD), new MonetaryValue(312.59, Currency::USD), 1_812.59],
            "1 + 1.125 rounds to 2.13" => [new MonetaryValue(1, Currency::USD), new MonetaryValue(1.125, Currency::USD), 2.13 ],
        ];
    }
    
    #[Test]
    public function it_throws_CurrencyMismatchException_when_trying_to_add_monetary_values_with_different_currencies(): void
    {
        $onePound = new MonetaryValue(1, Currency::GBP);
        $oneDollar = new MonetaryValue(1, Currency::USD);

        $this->expectException(CurrencyMismatchException::class);
        $onePound->add($oneDollar);
    }
}