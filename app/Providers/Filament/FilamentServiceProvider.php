<?php

namespace App\Providers\Filament;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            // Use the correct auth guard based on the panel
            Filament::setCurrentPanel(
                Filament::getPanel(auth('supplier')->check() ? 'supplier' : 'admin')
            );
        });
    }
}
