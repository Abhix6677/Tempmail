<?php

namespace App\Livewire\Backend\Domains;

use App\Models\Domain;
use Livewire\Component;

class Manage extends Component {

    public $domains, $domain, $showDomainModal = false;

    public function mount() {
        $this->updateDomains();
        $this->clearDomainObject();
    }

    public function clearDomainObject() {
        $this->domain = [
            'domain' => '',
            'type' => 'open',
            'is_active' => false,
        ];
    }

    public function updateDomains() {
        $this->domains = Domain::all();
    }

    public function openDomainModal($domainId = null) {
        if ($domainId) {
            $this->domain = Domain::findOrFail($domainId)->toArray();
            if (isset($this->domain['is_active'])) {
                $this->domain['is_active'] = (bool) $this->domain['is_active'];
            }
        } else {
            $this->clearDomainObject();
        }
        $this->showDomainModal = true;
    }

    public function save() {
        $this->validate([
            'domain.domain' => 'required|unique:domains,domain,' . ($this->domain['id'] ?? 'NULL') . '|max:255',
            'domain.type' => 'required|in:open,member,premium',
            'domain.is_active' => 'boolean',
        ], [
            'domain.domain.required' => 'The domain field is required.',
            'domain.domain.unique' => 'This domain already exists.',
            'domain.type.required' => 'The type field is required.',
            'domain.type.in' => 'The selected type is invalid.',
            'domain.is_active.boolean' => 'The active status must be true or false.',
        ]);

        Domain::updateOrCreate(
            ['id' => $this->domain['id'] ?? null],
            $this->domain
        );

        $this->clearDomainObject();
        $this->updateDomains();
        $this->dispatch('saved');
        $this->showDomainModal = false;
    }

    public function delete($domainId) {
        $domain = Domain::findOrFail($domainId);
        $domain->delete();
        $this->updateDomains();
        $this->dispatch('saved');
    }

    public function render() {
        return view('backend.domains.manage');
    }
}
