<?php

namespace App\Livewire\Frontend;

use App\Models\Domain;
use App\Models\Log;
use Livewire\Component;
use App\Services\TMail;
use App\Services\Util;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log as FacadesLog;

class Actions extends Component {

    public $in_app = false;
    public $user, $domain, $domains, $email, $emails, $captcha, $memberDomains;

    protected $listeners = ['syncEmail', 'checkReCaptcha3'];

    public function mount() {
        $this->domains = Domain::getDomainsForCurrentUser();
        $this->memberDomains = Domain::getMemberOnlyDomains();
        $this->email = TMail::getEmail();
        $this->emails = TMail::getEmails();

        // Auto-generate an email if none exists, to prevent "Generating Email..." stuck state
        if (!$this->email && !$this->in_app) {
            $this->email = TMail::generateDotAliasEmail();
            $this->emails = TMail::getEmails();
            session(['email_start_time' => now()]);
        }

        // Silently fix invalid domain instead of redirecting (redirects from mount cause loops)
        $this->fixInvalidDomainInEmail();

        if (intval(config('app.settings.default_domain')) && isset($this->domains[intval(config('app.settings.default_domain')) - 1])) {
            $this->domain = $this->domains[intval(config('app.settings.default_domain')) - 1];
        }
    }

    public function syncEmail($email) {
        $this->email = $email;
        if (count($this->emails) == 0) {
            $this->emails = [$email];
        }
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function checkReCaptcha3($token, $action) {
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?secret=' . config('app.settings.recaptcha3.secret_key') . '&response=' . $token);
        $data = $response->json();
        if ($data['success']) {
            $captcha = $data['score'];
            if ($captcha > 0.5) {
                if ($action == 'create') {
                    $this->create();
                } else {
                    $this->random();
                }
            } else {
                return $this->showAlert('error', __('Captcha Failed! Please try again'));
            }
        } else {
            return $this->showAlert('error', __('Captcha Failed! Error: ') . json_encode($data['error-codes']));
        }
    }

    public function create() {
        if (!$this->user) {
            return $this->showAlert('error', __('Please enter Username'));
        }
        $this->checkDomainInUsername();
        if (strlen($this->user) < config('app.settings.custom.min') || strlen($this->user) > config('app.settings.custom.max')) {
            return $this->showAlert('error', __('Username length cannot be less than') . ' ' . config('app.settings.custom.min') . ' ' . __('and greator than') . ' ' . config('app.settings.custom.max'));
        }
        if (!$this->domain) {
            return $this->showAlert('error', __('Please Select a Domain'));
        }
        if (in_array($this->user, config('app.settings.forbidden_ids'))) {
            return $this->showAlert('error', __('Username not allowed'));
        }
        // Email limit disabled for unlimited temp mail generation
        if (!$this->checkUsedEmail()) {
            return $this->showAlert('error', __('Sorry! That email is already been used by someone else. Please try a different email address.'));
        }
        if (!$this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
        }
        // Ensure only one email per user (remove existing emails)
        foreach (TMail::getEmails() as $existingEmail) {
            // Delete inbox messages of old email
            \App\Models\Message::where('to', 'like', '%' . $existingEmail . '%')->delete();
            TMail::removeEmail($existingEmail);
        }

        $this->email = TMail::createCustomEmail($this->user, $this->domain);
        $this->emails = TMail::getEmails();

        // Mark fresh inbox start time
        session(['email_start_time' => now()]);

        // Notify the App component about the new email so it can clear stale messages
        $this->dispatch('emailGenerated', email: $this->email);

        // Close the "New" panel after creation
        $this->in_app = false;

        // Signal success and redirect to mailbox
        $this->showAlert('success', __('Email created successfully'));
        $this->redirect(Util::localizeRoute('mailbox'));
    }

    public function random() {
        // Email limit disabled for unlimited temp mail generation
        if (!$this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
        }

        // Ensure only one email per user (remove existing emails)
        foreach (TMail::getEmails() as $existingEmail) {
            // Delete inbox messages of old email
            \App\Models\Message::where('to', 'like', '%' . $existingEmail . '%')->delete();
            TMail::removeEmail($existingEmail);
        }

        // Use atomic dot-variant allocation
        $this->email = TMail::generateDotAliasEmail();
        $this->emails = TMail::getEmails();

        // Mark fresh inbox start time
        session(['email_start_time' => now()]);

        // Notify the App component about the new email so it can clear stale messages
        $this->dispatch('emailGenerated', email: $this->email);

        // Signal success and redirect to mailbox
        $this->showAlert('success', __('Random email created'));
        $this->redirect(Util::localizeRoute('mailbox'));
    }

    public function deleteEmail() {
        $oldEmail = $this->email;
        TMail::removeEmail($this->email);

        if (count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
            // Need a full redirect for this edge case
            $this->redirect(Util::localizeRoute('home'));
            return;
        }

        // Generate next Gmail dot-alias (variant was already released by removeEmail)
        $this->email = TMail::generateDotAliasEmail();
        $this->emails = TMail::getEmails();

        // Notify the App component about email change so it refreshes
        $this->dispatch('emailGenerated', email: $this->email);

        $this->showAlert('success', __('Email deleted, new one generated'));
    }

    public function render() {
        // Enforce single active email per user session
        $allEmails = TMail::getEmails();
        if (count($allEmails) > 1) {
            $current = TMail::getEmail();
            foreach ($allEmails as $email) {
                if ($email !== $current) {
                    TMail::removeEmail($email);
                }
            }
            $this->emails = TMail::getEmails();
        }

        $theme = config('app.settings.theme') ?: 'default';
        if (!view()->exists("frontend.themes.$theme.components.actions")) {
            $theme = 'default';
        }
        return view("frontend.themes.$theme.components.actions");
    }

    /**
     * Private Functions
     */

    private function showAlert($type, $message) {
        $this->dispatch('showAlert', ['type' => $type, 'message' => $message]);
    }

    /**
     * Don't allow used email
     */
    private function checkUsedEmail() {
        if (config('app.settings.disable_used_email', false)) {
            $check = Log::where('email', $this->user . '@' . $this->domain)->where('ip', '<>', request()->ip())->count();
            if ($check > 0) {
                return false;
            }
            return true;
        }
        return true;
    }

    /**
     * Validate Captcha
     */
    private function validateCaptcha() {
        if (config('app.settings.captcha') == 'hcaptcha') {
            $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'response' => $this->captcha,
                'secret' => config('app.settings.hcaptcha.secret_key')
            ])->object();
            return $response->success;
        } else if (config('app.settings.captcha') == 'recaptcha2') {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'response' => $this->captcha,
                'secret' => config('app.settings.recaptcha2.secret_key')
            ])->object();
            return $response->success;
        }
        return true;
    }

    /**
     * Check if the user is crossing email limit
     */
    private function checkEmailLimit() {
        $logs = Log::select('ip', 'email')->where('ip', request()->ip())->where('created_at', '>', Carbon::now()->subDay())->groupBy('email')->groupBy('ip')->get();
        if (count($logs) >= config('app.settings.email_limit', 5)) {
            return false;
        }
        return true;
    }

    /**
     * Check if Username already consist of Domain
     */
    private function checkDomainInUsername() {
        $parts = explode('@', $this->user);
        if (isset($parts[1])) {
            if (in_array($parts[1], $this->domains)) {
                $this->domain = $parts[1];
            }
            $this->user = $parts[0];
        }
    }

    /**
     * Validate if Domain in Email Exist
     */
    private function validateDomainInEmail() {
        $data = explode('@', $this->email);
        if (isset($data[1])) {
            $domain = $data[1];
            $domains = Domain::getDomainsForCurrentUser();
            if (!in_array($domain, $domains)) {
                $key = array_search($this->email, $this->emails);
                TMail::removeEmail($this->email);
                if ($key == 0 && count($this->emails) == 1 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
                    return redirect(Util::localizeRoute('home'));
                } else {
                    return redirect(Util::localizeRoute('mailbox'));
                }
            }
        }
    }

    /**
     * Silently fix email if its domain is not in the allowed domains list.
     * Unlike validateDomainInEmail(), this never returns a redirect —
     * it regenerates the email with a valid domain instead.
     * If no domains are configured at all, the email is left as-is.
     */
    private function fixInvalidDomainInEmail() {
        if (!$this->email) {
            return;
        }
        // No domains configured — nothing to validate against, accept whatever we have
        if (empty($this->domains)) {
            return;
        }
        $data = explode('@', $this->email);
        if (!isset($data[1])) {
            return;
        }
        $domain = $data[1];
        if (in_array($domain, $this->domains)) {
            return; // Domain is valid, nothing to do
        }

        // If the email is a Gmail dot-variant (domain matches the IMAP username domain),
        // skip domain validation — Gmail variants are always valid as they're controlled
        // by the IMAP username in settings, not the domain list.
        $imapUser = config('app.settings.imap.username');
        if ($imapUser && str_contains($imapUser, '@')) {
            $imapDomain = explode('@', $imapUser)[1];
            if ($domain === $imapDomain) {
                return; // Gmail dot-variant, skip domain list check
            }
        }

        // Domain is invalid — remove the bad email and generate a fresh one
        // using TMail's built-in methods that pick from available domains
        TMail::removeEmail($this->email);

        $this->email = TMail::generateDotAliasEmail();

        $this->emails = TMail::getEmails();
        session(['email_start_time' => now()]);
        $this->dispatch('emailGenerated', email: $this->email);
    }
}
