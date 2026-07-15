<?php

namespace App\Livewire\Backend\Settings;

use Livewire\Component;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Log;

class Exportimport extends Component {

    protected $listeners = ['upload' => 'import'];

    public function import($settings) {
        $settings = json_decode($settings);
        foreach ($settings as $setting) {
            try {
                unserialize($setting->value);
                Setting::where('key', $setting->key)->update([
                    'value' => $setting->value
                ]);
            } catch (Exception $e) {
                Log::alert($e);
                continue;
            }
        }
        $this->dispatch('imported');
        $this->dispatch('refresh');
    }

    public function export() {
        $exclude = ['version', 'license_key', 'homepage', 'delivery'];
        $settings = Setting::select('key', 'value')->whereNotIn('key', $exclude)->get();
        $this->dispatch('settingsExportFetched', json_encode($settings));
    }

    public function render() {
        return view('backend.settings.exportimport');
    }
}
