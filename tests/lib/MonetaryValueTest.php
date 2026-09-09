<?php declare(strict_types=1);

namespace Tests\Lib;

use Lib\Currency;
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
}