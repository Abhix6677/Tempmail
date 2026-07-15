<?php

namespace App\Livewire\Backend\Dashboard;

use App\Models\Stat;
use Livewire\Component;

class MessagesReceived extends Component {

    public $filter = 'last_7_days';
    public $chartId = 'messagesChart';

    public array $chartData = [
        'x' => [],
        'y' => [],
    ];

    public function mount() {
        $this->updateChartData();
    }

    public function updateChartData() {
        $this->chartData = Stat::getChartData('messages_received', $this->filter);
        $this->dispatch("update-chart", chartId: $this->chartId, chartData: $this->chartData);
    }

    public function render() {
        return view('backend.dashboard.messages-received');
    }
}
