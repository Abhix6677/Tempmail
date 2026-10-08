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
        $this->emails = TMail::getEmails();
        if (count($this->emails) >= TMail::MAX_USER_EMAILS) {
            return $this->showAlert('error', __('You can keep a maximum of 3 mailboxes at a time. Please delete an existing mailbox to create a new one.'));
        }

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
        if (is_array(config('app.settings.forbidden_ids')) && in_array($this->user, config('app.settings.forbidden_ids'), true)) {
            return $this->showAlert('error', __('Username not allowed'));
        }
        if (!$this->checkUsedEmail()) {
            return $this->showAlert('error', __('Sorry! That email is already been used by someone else. Please try a different email address.'));
        }
        if (!$this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
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
        $this->emails = TMail::getEmails();
        if (count($this->emails) >= TMail::MAX_USER_EMAILS) {
            return $this->showAlert('error', __('You can keep a maximum of 3 mailboxes at a time. Please delete an existing mailbox to create a new one.'));
        }

        if (!$this->validateCaptcha()) {
            return $this->showAlert('error', __('Invalid Captcha. Please try again'));
        }

        // Generate a fresh randomized Gmail dot-alias
        $this->email = TMail::generateDotAliasEmail();
        $this->emails = TMail::getEmails();

        // Mark fresh inbox start time
        session(['email_start_time' => now()]);

        // Notify the App component about the new email
        $this->dispatch('emailGenerated', email: $this->email);

        $this->showAlert('success', __('Random email created'));
        $this->redirect(Util::localizeRoute('mailbox'));
    }

    public function deleteEmail() {
        $oldEmail = $this->email;
        TMail::removeEmail($this->email);
        $this->emails = TMail::getEmails();

        if (count($this->emails) == 0 && config('app.settings.after_last_email_delete') == 'redirect_to_homepage') {
            $this->redirect(Util::localizeRoute('home'));
            return;
        }

        if (count($this->emails) == 0) {
            // Generate a fresh randomized Gmail dot-alias if no emails remain
            $this->email = TMail::generateDotAliasEmail();
            $this->emails = TMail::getEmails();
        } else {
            $this->email = TMail::getEmail();
        }

        // Notify the App component about email change so it refreshes
        $this->dispatch('emailGenerated', email: $this->email);

        $this->showAlert('success', __('Mailbox deleted'));
    }

    public function render() {
        // Enforce max 3 active emails per user session and clean up expired emails
        $this->emails = TMail::getEmails();
        $this->email = TMail::getEmail();

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
            return isset($response->success) && $response->success;
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
            if (is_array($this->domains) && in_array($parts[1], $this->domains, true)) {
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
            $imapParts = explode('@', $imapUser, 2);
            $imapDomain = $imapParts[1] ?? '';
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
