<?php declare(strict_types=1);

namespace Widgets;

class ListOfWidgets {

    /** @var Widget[] */
    private array $widgets;

    public function __construct(Widget ...$widgets) {
        $this->widgets = $widgets;
    }

    public function add(Widget $widget): self {
        $this->widgets[] = $widget;
        return $this;
    }

    public function includes(Widget $widgetToCheck): bool
    {
        return array_any($this->toArray(), fn ($widget) => $widget->code() === $widgetToCheck->code());
    }

    public function toArray(): array
    {
        return $this->widgets;
    }
}