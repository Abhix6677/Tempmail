<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model {

    public $timestamps = false;

    protected $fillable = [
        'type',
        'count',
        'date',
    ];

    public static function storeMessagesReceived($count = 1) {
        $stat = Stat::where('type', 'messages_received')->whereDate('date', now()->toDateString())->first();
        if (!$stat) {
            $stat = new Stat();
            $stat->type = 'messages_received';
            $stat->date = now()->toDateString();
            $stat->count = $count;
        } else {
            $stat->count += $count;
        }
        $stat->save();
    }

    public static function storeEmailsCreated($count = 1) {
        $stat = Stat::where('type', 'emails_created')->whereDate('date', now()->toDateString())->first();
        if (!$stat) {
            $stat = new Stat();
            $stat->type = 'emails_created';
            $stat->date = now()->toDateString();
            $stat->count = $count;
        } else {
            $stat->count += $count;
        }
        $stat->save();
    }

    public static function getChartData($type, $period = 'last_7_days') {
        $x = [];
        $y = [];
        $today = Carbon::today();

        switch ($period) {
            case 'last_7_days':
                $start = $today->copy()->subDays(6);
                $data = Stat::where('type', $type)
                    ->whereBetween('date', [$start, $today])
                    ->select(DB::raw('DATE(date) as label'), DB::raw('SUM(count) as total'))
                    ->groupBy('label')
                    ->pluck('total', 'label');

                for ($date = $start->copy(); $date->lte($today); $date->addDay()) {
                    $x[] = $date->format('j M');
                    $y[] = $data->get($date->toDateString(), 0);
                }
                break;

            case 'last_6_weeks':
                $startOfWeek = $today->copy()->startOfWeek(); // Sunday
                $start = $startOfWeek->copy()->subWeeks(5); // 8 weeks total

                $data = Stat::where('type', $type)
                    ->whereBetween('date', [$start, $today])
                    ->select(
                        DB::raw('YEARWEEK(date, 1) as weeknum'),
                        DB::raw('MIN(DATE(date)) as week_start'),
                        DB::raw('MAX(DATE(date)) as week_end'),
                        DB::raw('SUM(count) as total')
                    )
                    ->groupBy('weeknum')
                    ->orderBy('week_start')
                    ->get();

                // Map by week start for easy access
                $weeklyData = $data->mapWithKeys(function ($item) {
                    $start = Carbon::parse($item->week_start)->startOfWeek();
                    return [$start->toDateString() => $item->total];
                });

                for ($i = 0; $i < 6; $i++) {
                    $startOf = $start->copy()->addWeeks($i)->startOfWeek();
                    $endOf = $startOf->copy()->endOfWeek();

                    $label = $startOf->format('j') . ' - ' . $endOf->format('j M');
                    $x[] = $label;
                    $y[] = $weeklyData->get($startOf->toDateString(), 0);
                }
                break;

            case 'last_12_months':
                $start = $today->copy()->subMonths(11)->startOfMonth();
                $end = $today->copy()->endOfMonth();

                $data = Stat::where('type', $type)
                    ->whereBetween('date', [$start, $end])
                    ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('SUM(count) as total'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->pluck('total', 'month');

                for ($i = 0; $i < 12; $i++) {
                    $month = $start->copy()->addMonths($i);
                    $key = $month->format('Y-m');
                    $x[] = $month->format('M'); // e.g. Jul 2025
                    $y[] = $data->get($key, 0);
                }
                break;

            case 'last_7_years':
                $currentYear = $today->year;
                $startYear = $currentYear - 6;

                $data = Stat::where('type', $type)
                    ->whereBetween('date', [Carbon::create($startYear, 1, 1), Carbon::create($currentYear, 12, 31)])
                    ->select(DB::raw('YEAR(date) as year'), DB::raw('SUM(count) as total'))
                    ->groupBy('year')
                    ->orderBy('year')
                    ->pluck('total', 'year');

                for ($year = $startYear; $year <= $currentYear; $year++) {
                    $x[] = (string) $year;
                    $y[] = $data->get($year, 0);
                }
                break;

            default:
                break;
        }

        return [
            'x' => $x,
            'y' => $y,
        ];
    }
}
