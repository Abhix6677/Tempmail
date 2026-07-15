<?php

namespace App\Livewire\Backend\Dashboard;

use App\Models\Stat;
use Livewire\Component;

class EmailsCreated extends Component {

    public $filter = 'last_7_days';
    public $chartId = 'emailsChart';

    public array $chartData = [
        'x' => [],
        'y' => [],
    ];

    public function mount() {
        $this->updateChartData();
    }

    public function updateChartData() {
        $this->chartData = Stat::getChartData('emails_created', $this->filter);
        $this->dispatch("update-chart", chartId: $this->chartId, chartData: $this->chartData);
    }

    public function render() {
        return view('backend.dashboard.emails-created');
    }
}
