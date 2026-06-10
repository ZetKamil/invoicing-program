<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue Chart';

    protected function getData(): array
    {
        $invoices = \App\Models\Invoice::where('status', \App\Enums\InvoiceStatus::PAID)
            ->whereYear('paid_at', date('Y'))
            ->get();

        $data = [];
        foreach ($invoices as $invoice) {
            $month = $invoice->paid_at->format('m');
            $data[$month] = ($data[$month] ?? 0) + $invoice->total_amount;
        }

        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $dataset = [];
        foreach ($months as $month) {
            $dataset[] = $data[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $dataset,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
