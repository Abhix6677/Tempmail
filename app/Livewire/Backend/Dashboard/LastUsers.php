<?php

namespace App\Livewire\Backend\Dashboard;

use App\Models\User;
use Livewire\Component;

class LastUsers extends Component {

    public $users = [];

    public function mount() {
        $this->users = User::latest()->take(10)->get();
    }

    public function render() {
        return view('backend.dashboard.last-users');
    }
}
