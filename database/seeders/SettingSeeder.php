<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\Util;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $settings = [
            "name" => "TMail",
            "version" => "8.0.1",
            "logo" => "",
            "favicon" => "",
            "license_key" => "",
            "api_keys" => [],
            "domains" => [],
            "default_domain" => 0,
            "homepage" => 0,
            "app_header" => "",
            "theme" => "default",
            "fetch_seconds" => 20,
            "email_limit" => 5,
            "fetch_messages_limit" => 15,
            "ads" => [
                "one" => "",
                "two" => "",
                "three" => "",
                "four" => "",
                "five" => ""
            ],
            "socials" => [],
            "colors" => [
                "primary" => "#0155b5",
                "secondary" => "#2fc10a",
                "tertiary" => "#d2ab3e"
            ],
            "engine" => "imap",
            "delivery" => [
                "key" => Util::generateRandomString(32)
            ],
            "imap" => [
                "host" => "localhost",
                "port" => 993,
                "encryption" => "ssl",
                "validate_cert" => true,
                "username" => "username",
                "password" => "password",
                "default_account" => "default",
                "protocol" => "imap",
                "cc_check" => false
            ],
            "language" => "en",
            "enable_create_from_url" => false,
            "forbidden_ids" => [
                "admin",
                "catch"
            ],
            "blocked_domains" => [],
            "cron_password" => Util::generateRandomString(16),
            "delete" => [
                "value" => 1,
                "key" => "d"
            ],
            "custom" => [
                "min" => 3,
                "max" => 15
            ],
            "random" => [
                "start" => 0,
                "end" => 0
            ],
            "global" => [
                "css" => "",
                "js" => "",
                "header" => "",
                "footer" => ""
            ],
            "cookie" => [
                "enable" => true,
                "text" => "<p>By using this website you agree to our <a href='#' target='_blank'>Cookie Policy</a></p>"
            ],
            "disable_used_email" => false,
            "allow_reuse_email_in_days" => 7,
            "captcha" => "off",
            "recaptcha2" => [
                "site_key" => "",
                "secret_key" => ""
            ],
            "recaptcha3" => [
                "site_key" => "",
                "secret_key" => ""
            ],
            "hcaptcha" => [
                "site_key" => "",
                "secret_key" => ""
            ],
            "after_last_email_delete" => "redirect_to_homepage",
            "date_format" => "d M Y h:i A",
            "theme_options" => [
                "mailbox_page" => 0,
                "button" => [
                    "text" => "Create your own Temp Mail",
                    "link" => "https://1.envato.market/tmail"
                ]
            ],
            "disable_mailbox_slug" => false,
            "external_link_masker" => "",
            "add_mail_in_title" => true,
            "enable_ad_block_detector" => false,
            "font_family" => [
                "head" => "Kadwa",
                "body" => "Poppins"
            ],
            "lock" => [
                "enable" => false,
                "text" => "",
                "password" => "nh3dukcjs7p9a2bi5emr480wlxyqv1o6tgf"
            ],
            "allowed_file_types" => "csv,doc,docx,xls,xlsx,ppt,pptx,xps,pdf,dxf,ai,psd,eps,ps,svg,ttf,zip,rar,tar,gzip,mp3,mpeg,wav,ogg,jpeg,jpg,png,gif,bmp,tif,webm,mpeg4,3gpp,mov,avi,mpegs,wmv,flx,txt",
            "languages" => [
                'ar' => [
                    'label' => 'Arabic',
                    'type' => 'rtl',
                    'is_active' => true,
                ],
                'de' => [
                    'label' => 'German',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'en' => [
                    'label' => 'English',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'fr' => [
                    'label' => 'French',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'hi' => [
                    'label' => 'Hindi',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'pl' => [
                    'label' => 'Polish',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'ru' => [
                    'label' => 'Russian',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'es' => [
                    'label' => 'Spanish',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'vi' => [
                    'label' => 'Vietnamese',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'tr' => [
                    'label' => 'Turkish',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'no' => [
                    'label' => 'Norwegian',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'id' => [
                    'label' => 'Indonesian',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'it' => [
                    'label' => 'Italian',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'hu' => [
                    'label' => 'Hungarian',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
                'pt' => [
                    'label' => 'Portuguese',
                    'type' => 'ltr',
                    'is_active' => true,
                ],
            ],
            "disqus" => [
                "enable" => false,
                "shortname" => "",
            ],
            "user_registration" => [
                "enabled" => true,
                "require_email_verification" => false,
            ],
            "language_in_url" => false,
            "enable_dark_mode" => false,
            "allowed_domains" => [],
        ];
        foreach ($settings as $key => $value) {
            /** START Remove in v8.0.1 */
            if ($key == 'languages') {
                Setting::where('key', $key)->delete();
            }
            /** END Remove in v8.0.1 */
            if (!Setting::where('key', $key)->exists()) {
                Setting::create([
                    'key' => $key,
                    'value' => serialize($value)
                ]);
            }
        }
    }
}
