<?php declare(strict_types=1);

namespace Widgets;

class ListOfWidgetCodes {

    /** @var WidgetCode[] */
    private array $widgetCodes;

    public function __construct(WidgetCode ...$widgetCodes) {
        $this->widgetCodes = $widgetCodes;
    }

    public function add(WidgetCode $widgetCode): self
    {
        $this->widgetCodes[] = $widgetCode;
        return $this;
    }

    public function includes(WidgetCode $widgetCodeToCheck): bool
    {
        return array_any($this->toArray(), fn ($widgetCode) => $widgetCode === $widgetCodeToCheck);
    }

    public function toArray(): array
    {
        return $this->widgetCodes;
    }
}