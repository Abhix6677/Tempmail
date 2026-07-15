<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Menu;

class Nav extends Component {

    public $menus, $current_route;

    public function mount() {
        $this->menus = Menu::where('status', true)->where('location', 'primary')->where('parent_id', null)->orderBy('order')->get();
    }

    public function render() {
        $theme = config('app.settings.theme') ?: 'default';

        if (!view()->exists("frontend.themes.$theme.components.nav")) {
            $theme = 'default';
        }

        return view("frontend.themes.$theme.components.nav");
    }
}
