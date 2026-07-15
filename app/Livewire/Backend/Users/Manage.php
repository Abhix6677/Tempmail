<?php

namespace App\Livewire\Backend\Users;

use Livewire\WithPagination;
use App\Models\User;
use Livewire\Component;

class Manage extends Component {

    use WithPagination;

    public $filters = [
        'search' => '',
        'role' => '',
    ];

    public function clearFilters() {
        $this->filters = [
            'search' => '',
            'role' => '',
        ];
    }

    public function search() {
        //nothing to do here, the search is handled in the render method
    }

    public function userAction($user_id, $action) {
        $user = User::find($user_id);
        if ($user) {
            if ($action == 'suspend') {
                $user->role = 0;
                $user->save();
            } else if ($action == 'unsuspend') {
                $user->role = 1;
                $user->save();
            } else if ($action == 'delete') {
                $user->delete();
            }
            return;
        } else {
            session()->flash('error', __('User not found.'));
        }
    }

    public function render() {
        $query = User::query();

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($this->filters['role'] !== '' && $this->filters['role'] !== null) {
            $query->where('role', (int)$this->filters['role']);
        }

        $users = $query->paginate(10);
        return view('backend.users.manage', [
            'users' => $users,
        ]);
    }
}
