<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class UserStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    public ?int $recordId = null;

    protected function getStats(): array
    {
        // Check if recordId is set
        if (!$this->recordId) {
            return [
                Stat::make('Error', 'User not found')
                    ->description('Please refresh the page')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Find the user
        $user = User::find($this->recordId);

        // Check if user exists
        if (!$user) {
            return [
                Stat::make('Error', 'User not found')
                    ->description('The user may have been deleted')
                    ->color('danger')
                    ->icon('heroicon-m-exclamation-circle'),
            ];
        }

        // Check if user has a pharmacy relationship
        $pharmacyName = $user->pharmacy?->name ?? 'Not assigned';

        // Check if created_at is valid
        $createdAt = $user->created_at ? $user->created_at->format('M d, Y') : 'Unknown';

        return [
            Stat::make('Role', $user->role)
                ->description('User role in the pharmacy')
                ->icon('heroicon-m-identification')
                ->color(match ($user->role) {
                    'PHARMACY_OWNER' => 'primary',
                    'PHARMACIST' => 'success',
                    'CASHIER' => 'warning',
                    default => 'gray',
                }),

            Stat::make('Pharmacy', $pharmacyName)
                ->description('Associated pharmacy')
                ->icon('heroicon-m-building-office'),

            Stat::make('Account Created', $createdAt)
                ->description('Date account was created')
                ->icon('heroicon-m-calendar'),
        ];
    }
}
