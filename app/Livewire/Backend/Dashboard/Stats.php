<?php

namespace App\Livewire\Backend\Dashboard;

use App\Models\Page;
use App\Models\Post;
use App\Models\Stat;
use App\Models\User;
use Livewire\Component;

class Stats extends Component {

    public $messagesReceived = 0, $emailsCreated = 0, $pagesCreated = 0, $blogPostsCreated = 0, $usersRegistered = 0;

    public function mount() {
        $this->messagesReceived = number_format(Stat::where('type', 'messages_received')->sum('count'));
        $this->emailsCreated = number_format(Stat::where('type', 'emails_created')->sum('count'));
        $this->pagesCreated = number_format(Page::count());
        $this->blogPostsCreated = number_format(Post::count());
        $this->usersRegistered = number_format(User::count());
    }

    public function render() {
        return view('backend.dashboard.stats');
    }
}
