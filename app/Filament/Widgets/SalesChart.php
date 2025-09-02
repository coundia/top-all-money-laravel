<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\TransactionEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SalesChart extends ChartWidget
{
    protected   ?string $heading = 'Sales (last 12 months)';

    protected function getData(): array
    {
        $from   = now()->startOfMonth()->subMonths(11);
        $driver = DB::connection()->getDriverName();

        $ymExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%Y', createdAt) AS INTEGER) as y, CAST(strftime('%m', createdAt) AS INTEGER) as m",
            'mysql'  => "YEAR(createdAt) as y, MONTH(createdAt) as m",
            'pgsql'  => "EXTRACT(YEAR FROM \"createdAt\")::int as y, EXTRACT(MONTH FROM \"createdAt\")::int as m",
            default  => "EXTRACT(YEAR FROM createdAt) as y, EXTRACT(MONTH FROM createdAt) as m",
        };

        $sumExpr = "SUM(CASE WHEN typeEntry = 'CREDIT' THEN amount ELSE -amount END) as total";

        $rows = TransactionEntry::whereNull('deletedAt')
            ->where('createdAt', '>=', $from)
            ->selectRaw("$ymExpr, $sumExpr")
            ->groupBy('y', 'm')
            ->orderBy('y')
            ->orderBy('m')
            ->get();

        $series = [];
        $labels = [];
        $cursor = $from->copy();

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-n');
            $labels[] = $cursor->format('M Y');
            $series[$key] = 0;
            $cursor->addMonth();
        }

        foreach ($rows as $r) {
            $key = $r->y . '-' . (int) $r->m;
            if (array_key_exists($key, $series)) {
                $series[$key] = (int) $r->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Net amount (credit - debit)',
                    'data'  => array_values($series),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
