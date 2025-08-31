<?php

namespace App\Filament\Support;

use Filament\Navigation\NavigationGroup;

class CustomNavigationGroup extends NavigationGroup
{
    protected string $icon;

    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }
}
