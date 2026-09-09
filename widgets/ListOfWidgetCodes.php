<?php declare(strict_types=1);

namespace Widgets;

class ListOfWidgetCodes {

    /** @var WidgetCode[] */
    private array $widgetCodes;

    public function __construct(WidgetCode ...$widgetCodes) {
        $this->widgetCodes = $widgetCodes;
    }

    public function toArray(): array
    {
        return $this->widgetCodes;
    }
}