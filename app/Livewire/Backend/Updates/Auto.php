<?php

namespace App\Livewire\Backend\Updates;

use App\Models\Setting;
use App\Services\Util;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Auto extends Component {

    public $status = [
        'available' => false,
        'disabled' => false,
        'message' => 'No Update Available'
    ];

    public $progress = '';

    protected $listeners = ['apply'];

    public function mount() {
        $status = Cache::get('app-update');
        if ($status && !isset($status['error'])) {
            $this->status = $status;
        }
    }

    public function apply($step = 0) {
        if ($this->status['available'] == false && $this->status['disabled'] == false) {
            $this->progress .= '<div class="text-white">No Update Available</div>';
        } else {
            if ($step === 0) {
                $this->progress .= '<div class="text-white">Fetching Files from Server</div>';
                $this->dispatch('apply', 1);
            } else if ($step === 1) {
                try {
                    $request = Http::get(base64_decode('aHR0cHM6Ly9wb3J0YWwudGhlaHAuaW4vYXBpL3VwZGF0ZS90bWFpbA') . '8', [
                        'purchase_code' => config('app.settings.license_key'),
                        'domain' => $_SERVER['HTTP_HOST'],
                        'version' => config('app.settings.version')
                    ]);
                    Storage::put('files.zip', $request->getBody());
                    $zip = new \ZipArchive;
                    if ($zip->open(Storage::path('files.zip')) === TRUE) {
                        $this->progress .= '<div class="text-green-500">Extracting Files</div>';
                        $zip->extractTo(base_path());
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $item = $zip->getNameIndex($i);
                            $this->progress .= '<div class="text-white">/' . $item . '</div>';
                        }
                        $zip->close();
                        if (file_exists(base_path() . '/vendor_new')) {
                            File::deleteDirectory(base_path('vendor'));
                            rename(base_path('vendor_new'), base_path('vendor'));
                        }
                    } else {
                        throw new Exception('Not able to Open ZIP file');
                    }
                    $this->progress .= '<div class="text-green-500">Files Received and Updated Successfully</div>';
                    Storage::delete('files.zip');
                    $this->progress .= '<div class="text-white">Preparing Database Changes</div>';
                    $this->dispatch('apply', 2);
                } catch (Exception $e) {
                    $this->progress .= '<div class="text-red-600">Encountered Error' . $e->getMessage() . '</div>';
                }
            } else if ($step === 2) {
                try {
                    Artisan::call('migrate', ["--force" => true]);
                    Artisan::call('db:seed', ["--force" => true]);
                    Artisan::call('view:clear');
                    Setting::put('version', $this->status['version']);
                    Util::updateLangJsonFiles();
                    $this->progress .= '<div class="text-green-500">Database Changes Completed Successfully</div>';
                    $this->progress .= '<br><div class="text-green-500 font-bold">Version Upgrade Completed</div>';
                    $this->status = [
                        'available' => false,
                        'disabled' => false,
                        'message' => 'No Update Available',
                    ];
                } catch (Exception $e) {
                    $this->progress .= '<div class="text-red-600">Encountered Error' . $e->getMessage() . '</div>';
                }
            }
        }
    }

    public function render() {
        return view('backend.updates.auto');
    }
}
