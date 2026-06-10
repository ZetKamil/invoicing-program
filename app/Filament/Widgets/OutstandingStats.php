<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OutstandingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $outstanding = \App\Models\Invoice::whereIn('status', [\App\Enums\InvoiceStatus::SENT, \App\Enums\InvoiceStatus::OVERDUE])
            ->sum('total_amount');

        $paidInvoices = \App\Models\Invoice::where('status', \App\Enums\InvoiceStatus::PAID)->get();
        $totalRevenue = $paidInvoices->sum('total_amount');
        $uniqueCustomersCount = $paidInvoices->pluck('customer_id')->unique()->count();
        $avgRevenuePerCustomer = $uniqueCustomersCount > 0 ? $totalRevenue / $uniqueCustomersCount : 0;

        return [
            Stat::make('Total Outstanding', '€ ' . number_format($outstanding, 2))
                ->description('Unpaid sent invoices')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Total Revenue', '€ ' . number_format($totalRevenue, 2))
                ->description('From paid invoices')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Avg Revenue per Customer', '€ ' . number_format($avgRevenuePerCustomer, 2))
                ->description('Across ' . $uniqueCustomersCount . ' paying customers')
                ->color('primary'),
        ];
    }
}
