<?php

namespace App\Livewire\Backend\Settings;

use App\Models\Category;
use Livewire\Component;
use App\Models\Setting;
use App\Services\Util;
use Illuminate\Support\Facades\File;

class Languages extends Component {

    public $showLanguageModal = false;
    public $showTranslationModal = false;
    public $disableLanguageCode = false;
    public $form = [
        'label' => '',
        'language' => '',
        'type' => 'ltr'
    ];
    public $jsonToAdd = [];
    public $translations = [
        'language' => '',
        'strings' => []
    ];
    /**
     * Components State
     */
    public $state = [
        'languages' => [],
        'language' => 'en',
        'language_in_url' => false,
    ];

    public function mount() {
        $this->state['languages'] = config('app.settings.languages') ?? [];
        $this->state['language'] = config('app.settings.language') ?? 'en';
        $this->state['language_in_url'] = config('app.settings.language_in_url', false);

        // Ensure languages is always an array
        if (!is_array($this->state['languages'])) {
            $this->state['languages'] = [];
        }
    }

    public function updateLanguageFiles() {
        Util::updateLangJsonFiles();
        $this->dispatch('showSuccessMessageForLanguageFilesUpdated');
    }

    public function clearForm() {
        $this->form = [
            'label' => '',
            'language' => '',
            'type' => 'ltr'
        ];
        $this->disableLanguageCode = false;
    }

    public function addLanguage() {
        $this->validate(
            [
                'form.label' => 'required',
                'form.language' => 'required',
                'form.type' => 'required',
            ],
            [
                'form.label.required' => 'Label is required',
                'form.language.required' => 'Language code is required',
                'form.type.required' => 'Please select a type',
            ]
        );
        $this->state['languages'][$this->form['language']] = [
            'label' => $this->form['label'],
            'type' => ($this->form['type'] == 'ltr' ? 'ltr' : 'rtl'),
            'is_active' => false,
        ];
        $this->clearForm();
        array_push($this->jsonToAdd, $this->form['language']);
        $this->showLanguageModal = false;
        $this->disableLanguageCode = false;
    }

    public function enableLanguage($language) {
        $this->state['languages'][$language]['is_active'] = true;
    }

    public function disableLanguage($language) {
        $this->state['languages'][$language]['is_active'] = false;
    }

    public function editLanguage($language) {
        $this->showLanguageModal = true;
        $this->disableLanguageCode = true;
        $this->form = [
            'label' => $this->state['languages'][$language]['label'],
            'language' => $language,
            'type' => $this->state['languages'][$language]['type']
        ];
    }

    public function deleteLanguage($language) {
        unset($this->state['languages'][$language]);
    }

    public function updatedShowLanguageModal($value) {
        if ($value == false) {
            $this->clearForm();
        }
    }

    public function editTranslations($language) {
        $this->translations['language'] = $language;
        $this->translations['strings'] = $this->getTranslations($language);
        $this->showTranslationModal = true;
    }

    public function getTranslations($language) {
        $path = base_path('lang/' . $language . '.json');
        $strings = [];
        if (File::exists($path)) {
            $strings = json_decode(File::get($path), true);
        }
        $categories = Category::all();
        foreach ($categories as $category) {
            if (!isset($strings[$category->name])) {
                $strings[$category->name] = $category->name;
            }
        }
        return $strings;
    }

    public function saveTranslations() {
        $to = $this->translations['language'];
        $strings = $this->translations['strings'];
        File::put(base_path('lang/' . $to . '.json'), json_encode($strings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->showTranslationModal = false;
    }

    public function save() {
        $settings = Setting::whereIn('key', ['languages', 'language', 'language_in_url'])->get();
        foreach ($settings as $setting) {
            $setting->value = serialize($this->state[$setting->key]);
            $setting->save();
        }
        foreach ($this->jsonToAdd as $json) {
            $path = base_path('lang/' . $json . '.json');
            if (!File::exists($path)) {
                File::put($path, '{}');
            }
        }
        $this->jsonToAdd = [];
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.settings.languages');
    }
}
