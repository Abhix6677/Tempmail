<?php

namespace App\Models;

use App\Services\Util;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'link',
        'target',
        'parent_id',
        'order',
        'status',
        'location',
    ];

    public function getNameAttribute($value) {
        return Util::translateModelAttribute($this, $this->getTable(), 'name', $value);
    }

    public function hasChildAll() {
        if (Menu::where('parent_id', $this->id)->count() > 0) {
            return true;
        }
        return false;
    }

    public function hasChild() {
        if (Menu::where('parent_id', $this->id)->where('status', true)->count() > 0) {
            return true;
        }
        return false;
    }

    public function getChildAll() {
        return Menu::where('parent_id', $this->id)->orderBy('order', 'asc')->get();
    }

    public function getChild() {
        return Menu::where('parent_id', $this->id)->orderBy('order', 'asc')->where('status', true)->get();
    }
}
