<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Widgets\WidgetCode;
use Widgets\ListOfWidgetCodes;
use Widgets\WidgetFactory;
use Cart\Cart;
use Cart\Rules\DeliveryFeesRule;
use Cart\Rules\SecondHalfOffRule;
use Lib\Currency;

echo "Please enter a comma-separated list of items (Codes: R01, G01, B01): ";
$input = trim(fgets(STDIN));

$userEnteredWidgetCodes = array_map('trim', explode(',', $input));

try {
    $widgetFactory = new WidgetFactory();

    $cart = new Cart(
        $widgetFactory,
        Currency::USD,
        new SecondHalfOffRule($widgetFactory),
        new DeliveryFeesRule($widgetFactory),
    );

    $widgetCodes = new ListOfWidgetCodes();
    foreach ($userEnteredWidgetCodes as $code) {
        $cart->add(WidgetCode::from($code));
    }

    echo "The total value is: {$cart->total()->toDisplayFormat()}\n";
} catch (\ValueError $e) {
    echo "Invalid code entered. Please use R01, G01, or B01.\n";
    exit(1);
}