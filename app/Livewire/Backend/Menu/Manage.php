<?php

namespace App\Livewire\Backend\Menu;

use App\Models\Menu;
use Livewire\Component;

class Manage extends Component {

    public $location, $menus, $addMenuItem, $updateMenuItem, $menu, $showParent, $translations;

    public function mount() {
        $this->clearMenuObject();
        $this->addMenuItem = false;
        $this->updateMenuItem = false;
        $this->updateMenus();
    }

    public function updateMenus() {
        $this->menus = Menu::where('location', $this->location)->where('parent_id', null)->orderBy('order')->get();
    }

    public function moveUp($menu) {
        $menu = Menu::where('location', $this->location)->where('id', $menu['id'])->firstOrFail();
        $swap = Menu::where('location', $this->location)->where('order', '<', $menu->order)->where('parent_id', $menu->parent_id)->orderBy('order', 'desc')->first();
        if ($swap) {
            $order = $menu->order;
            $menu->order = $swap->order;
            $swap->order = $order;
            $swap->save();
            $menu->save();
            $this->updateMenus();
        }
    }

    public function moveDown($menu) {
        $menu = Menu::where('location', $this->location)->where('id', $menu['id'])->firstOrFail();
        $swap = Menu::where('location', $this->location)->where('order', '>', $menu->order)->where('parent_id', $menu->parent_id)->orderBy('order', 'asc')->first();
        if ($swap) {
            $order = $menu->order;
            $menu->order = $swap->order;
            $swap->order = $order;
            $swap->save();
            $menu->save();
            $this->updateMenus();
        }
    }

    public function toggleStatus($menu) {
        $menu = Menu::where('location', $this->location)->where('id', $menu['id'])->firstOrFail();
        $menu->status = !$menu->status;
        $menu->save();
        $childs = $menu->getChildAll();
        if (count($childs) > 0) {
            foreach ($childs as $child) {
                $child->status = $menu->status;
                $child->save();
            }
        }
        $this->updateMenus();
    }

    public function clearMenuObject() {
        $this->menu = [
            'name' => '',
            'link' => '',
            'target' => '',
            'parent_id' => null,
            'location' => $this->location,
        ];
        $this->showParent = true;
    }

    public function clearAddUpdate() {
        if ($this->translations) {
            $this->translations = null;
        } else {
            $this->addMenuItem = false;
            $this->updateMenuItem = false;
            $this->updateMenus();
            $this->clearMenuObject();
        }
    }

    public function showTranslations() {
        $menu = Menu::where('location', $this->location)->where('id', $this->menu['id'])->firstOrFail();
        $this->translations = [];
        if ($menu->translations) {
            $this->translations = unserialize($menu->translations);
        }
        foreach (config('app.settings.languages') as $language => $details) {
            if (!isset($this->translations[$language])) {
                $this->translations[$language] = '';
            }
        }
    }

    public function saveTranslations() {
        $menu = Menu::where('location', $this->location)->where('id', $this->menu['id'])->firstOrFail();
        $menu->translations = serialize($this->translations);
        $menu->save();
        $this->dispatch('saved');
    }

    public function showUpdate($menu) {
        $this->updateMenuItem = true;
        $this->menu = $menu;
        $this->menu['target'] = $this->menu['target'] === '_self' ? false : true;
        if (Menu::where('location', $this->location)->where('parent_id', $menu['id'])->count() > 0) {
            $this->showParent = false;
        }
    }

    public function saveMenu() {
        $this->validate(
            [
                'menu.name' => 'required',
                'menu.link' => 'required',
            ],
            [
                'menu.name.required' => 'Menu Name is Required',
                'menu.link.required' => 'Menu Link is Required',
            ]
        );

        // Determine if this is an add or update operation
        $menu = isset($this->menu['id'])
            ? Menu::where('location', $this->location)->where('id', $this->menu['id'])->firstOrFail()
            : new Menu;

        // Handle parent_id
        $this->menu['parent_id'] = $this->menu['parent_id'] == 0 ? null : $this->menu['parent_id'];

        // Handle order if parent_id changes or for new menu items
        if (!isset($this->menu['id']) || $menu->parent_id != $this->menu['parent_id']) {
            $order = Menu::select('order')
                ->where('location', $this->location)
                ->where('parent_id', $this->menu['parent_id'])
                ->orderBy('order', 'desc')
                ->first();
            $this->menu['order'] = (($order) ? $order->order : 0) + 1;
        }

        // Handle target
        $this->menu['target'] = $this->menu['target'] ? '_blank' : '_self';

        // Save the menu
        $menu->fill($this->menu);
        $menu->save();

        // Restore target for the form
        $this->menu['target'] = $this->menu['target'] === '_self' ? false : true;

        // Dispatch saved event and update menus
        $this->dispatch('saved');
        $this->updateMenus();
        $this->clearAddUpdate();
    }

    public function delete($menuId) {
        $menu = Menu::where('location', $this->location)->where('id', $menuId)->firstOrFail();
        $childs = $menu->getChildAll();
        if (count($childs) > 0) {
            $order = Menu::select('order')->where('location', $this->location)->where('parent_id', null)->orderBy('order', 'desc')->first();
            $next = (($order) ? $order->order : 0) + 1;
            foreach ($childs as $child) {
                $child->order = $next;
                $child->parent_id = null;
                $child->save();
                $next = $next + 1;
            }
        }
        $menu->delete();
        $this->updateMenus();
    }

    public function render() {
        return view('backend.menu.manage');
    }
}
