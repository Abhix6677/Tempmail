<?php

namespace App\Livewire\Backend\Themes;

use Livewire\Component;
use Livewire\WithFileUploads;
use DirectoryIterator;
use ZipArchive;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Manage extends Component {

    use WithFileUploads;

    public $themes, $current, $new, $error, $success;

    public function mount() {
        $directory = new DirectoryIterator(base_path('resources/views/frontend/themes'));
        $this->themes = [];
        foreach ($directory as $file) {
            if ($file->isDot()) continue;
            if ($file->isFile()) continue;
            array_push($this->themes, $file->getFilename());
        }
        $this->current = Setting::pick('theme');
        $this->cleanAlerts();
    }

    public function cleanAlerts() {
        $this->error = null;
        $this->success = null;
    }

    public function setTheme($theme) {
        if (in_array($theme, $this->themes)) {
            Setting::put('theme', $theme);
            $this->current = Setting::pick('theme');
        }
        $this->cleanAlerts();
    }

    public function delete($theme) {
        Storage::disk('themes')->deleteDirectory($theme);
        $this->mount();
    }

    public function save() {
        if ($this->new) {
            $filename = Storage::disk('tmp')->put('/', $this->new);
            $this->handle('tmp/' . $filename);
            Storage::disk('tmp')->delete($filename);
        } else {
            $this->error = 'Please upload a Theme ZIP file';
        }
    }

    public function version($theme = '') {
        if ($theme) {
            if (File::exists(public_path('themes/' . $theme . '/package.json'))) {
                $info = json_decode(File::get(public_path('themes/' . $theme . '/package.json')));
                if ($info->version !== '1.0.0') {
                    return ' - v' . $info->version;
                }
            }
        }
    }

    private function handle($filename) {
        $file = new ZipArchive;
        $verify = [
            'app.blade.php',
            'components/actions.blade.php',
            'components/app.blade.php',
            'components/nav.blade.php',
            'components/page.blade.php',
            'components/post.blade.php',
        ];
        if ($file->open($filename) !== TRUE) {
            $this->error = 'Looks like the Theme ZIP file is corrupted';
            return false;
        } else {
            $folder = '';
            for ($i = 0; $i < $file->numFiles; $i++) {
                $item = $file->getNameIndex($i);
                if ($folder === '') {
                    if ($item[strlen($item) - 1] === '/') {
                        $folder = substr($item, 0, -1);
                    }
                } else {
                    $key = array_search(str_replace($folder . '/', '', $item), $verify);
                    if ($key !== FALSE) {
                        unset($verify[$key]);
                    }
                }
            }
            if (count($verify) > 0) {
                $this->error = 'Incompleted Theme Files. Your ZIP is missing below file(s)<br><br>';
                foreach ($verify as $item) {
                    $this->error .= '/' . $item . '<br>';
                }
                return false;
            } else {
                $file->extractTo('themes');
                $this->new = null;
                $count = count($this->themes);
                $this->mount();
                if ($count == count($this->themes)) {
                    $this->success = 'Theme has been successfully updated.';
                } else {
                    $this->success = 'Theme has been successfully added.';
                }
            }
        }
    }

    public function render() {
        return view('backend.themes.manage');
    }
}
