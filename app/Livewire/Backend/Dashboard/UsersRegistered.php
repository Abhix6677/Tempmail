<?php

namespace App\Livewire\Backend\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersRegistered extends Component {

    public $filter = 'last_7_days';
    public $chartId = 'usersChart';

    public array $chartData = [
        'x' => [],
        'y' => [],
    ];

    public function mount() {
        $this->updateChartData();
    }

    public function updateChartData() {
        if ($this->filter === 'last_7_days') {
            $days = collect(range(0, 6))->map(function ($i) {
                return Carbon::today()->subDays(6 - $i);
            });
            $labels = $days->map(fn($d) => $d->format('j M'))->toArray();
            $counts = $days->map(function ($day) {
                return DB::table('users')->whereDate('created_at', $day->toDateString())->count();
            })->toArray();
            $this->chartData = [
                'x' => $labels,
                'y' => $counts,
            ];
        } elseif ($this->filter === 'last_6_weeks') {
            $weeks = collect(range(0, 5))->map(function ($i) {
                $start = Carbon::today()->subWeeks(5 - $i)->startOfWeek();
                $end = $start->copy()->endOfWeek();
                return [$start, $end];
            });
            $labels = $weeks->map(function ($range) {
                $start = $range[0];
                $end = $range[1];
                return $start->format('j M') . ' - ' . $end->format('j M');
            })->toArray();
            $counts = $weeks->map(function ($range) {
                $start = $range[0];
                $end = $range[1];
                return DB::table('users')->whereBetween('created_at', [$start->toDateString(), $end->toDateString()])->count();
            })->toArray();
            $this->chartData = [
                'x' => $labels,
                'y' => $counts,
            ];
        } elseif ($this->filter === 'last_12_months') {
            $months = collect(range(0, 11))->map(function ($i) {
                return Carbon::today()->subMonths(11 - $i)->startOfMonth();
            });
            $labels = $months->map(fn($m) => $m->format('M'))->toArray();
            $counts = $months->map(function ($month) {
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
                return DB::table('users')->whereBetween('created_at', [$start->toDateString(), $end->toDateString()])->count();
            })->toArray();
            $this->chartData = [
                'x' => $labels,
                'y' => $counts,
            ];
        } else {
            $this->chartData = ['x' => [], 'y' => []];
        }
        $this->dispatch("update-chart", chartId: $this->chartId, chartData: $this->chartData);
    }

    public function render() {
        return view('backend.dashboard.users-registered');
    }
}
