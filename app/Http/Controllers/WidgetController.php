<?php

namespace App\Http\Controllers;

use App\Mail\ContactForm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class WidgetController extends Controller {
    public function contact(Request $request) {
        if (config('app.settings.captcha') == 'hcaptcha') {
            $response = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'response' => $request->get('h-captcha-response'),
                'secret' => config('app.settings.hcaptcha.secret_key')
            ])->object();
            if (!$response->success) {
                Session::flash('error', __('Invalid Captcha. Please try again'));
                return redirect()->back();
            }
        } else if (config('app.settings.captcha') == 'recaptcha2') {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'response' => $request->get('g-captcha-response'),
                'secret' => config('app.settings.recaptcha2.secret_key')
            ])->object();
            if (!$response->success) {
                Session::flash('error', __('Invalid Captcha. Please try again'));
                return redirect()->back();
            }
        }
        $emails = User::where('role', 7)->pluck('email');
        if ($emails->isNotEmpty()) {
            try {
                Mail::to($emails)->send(new ContactForm($request->all()));
                Session::flash('success', __('Email sent successfully! We will get back to you shortly.'));
            } catch (\Throwable $e) {
                \Log::error('Contact form email failed: ' . $e->getMessage());
                Session::flash('error', __('Unable to deliver email at this moment. Please try again later.'));
            }
        } else {
            Session::flash('error', __('No admin recipient configured.'));
        }
        return redirect()->back();
    }
}
