<?php

namespace App\Livewire\Frontend;

use Livewire\Component;

class Post extends Component {

    public $post;

    public function mount($post = null) {
        $this->post = $post;
    }

    public function render() {
        $theme = config('app.settings.theme') ?: 'default';

        if (!view()->exists("frontend.themes.$theme.components.post")) {
            $theme = 'default';
        }

        return view("frontend.themes.$theme.components.post");
    }
}
