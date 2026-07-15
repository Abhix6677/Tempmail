<?php

namespace App\Http\Controllers;

use App\Models\CronLog;
use App\Models\Log;
use App\Models\Message;
use App\Models\Page;
use App\Models\Post;
use App\Services\TMail;
use App\Services\Util;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Date\Before;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller {

    public function cron($password) {
        if ($password == config('app.settings.cron_password')) {
            if (now()->format('H:i') === '00:00') { // Runs once every day
                Util::updateLangJsonFiles();
                CronLog::where('created_at', '<', Carbon::now()->subDays(3))->delete(); // Delete cron logs older than 3 days
            }
            if (now()->format('i') === '00') { //Runs once every hour
                $this->deleteContentForDeletedLanguages();
                $this->deleteLogs();
                $this->checkForAppUpdate();
            }
            $this->deleteMessages();
        } else {
            return abort(404);
        }
    }

    private function deleteContentForDeletedLanguages() {
        $codes = array_keys(config('app.settings.languages'));

        // Delete translations for languages that no longer exist
        \App\Models\Translation::where('translatable_type', 'page')
            ->whereNotIn('language', $codes)
            ->delete();

        \App\Models\Translation::where('translatable_type', 'post')
            ->whereNotIn('language', $codes)
            ->delete();
    }

    private function checkForAppUpdate() {
        $status = Util::checkForAppUpdate();
        Cache::forever('app-update', $status);
    }

    private function deleteLogs() {
        Log::where('created_at', '<', Carbon::now()->subDays(config('app.settings.allow_reuse_email_in_days')))->delete();
    }

    private function deleteMessages() {
        $before = null;
        if (config('app.settings.engine') == 'delivery' && config('app.settings.delete.key') == 'm') {
            $before = Carbon::now()->subMinutes(config('app.settings.delete.value'));
        } else if (config('app.settings.engine') == 'delivery' && config('app.settings.delete.key') == 'h') {
            $before = Carbon::now()->subHours(config('app.settings.delete.value'));
        } else if (config('app.settings.delete.key') == 'd') {
            $before = Carbon::now()->subDays(config('app.settings.delete.value'));
        } else if (config('app.settings.delete.key') == 'w') {
            $before = Carbon::now()->subWeeks(config('app.settings.delete.value'));
        } else {
            $before = Carbon::now()->subMonths(config('app.settings.delete.value'));
        }
        if (config('app.settings.engine') == 'delivery') {
            $messages = Message::where('created_at', '<', $before)->get();
            foreach ($messages as $message) {
                $directory = './tmp/attachments/' . $message->id . '/';
                Util::rrmdir($directory);
                $message->delete();
            }
            CronLog::add('Deleted ' . $messages->count() . ' messages older than ' . $before->diffForHumans());
            return;
        }
        $limit = 50;
        $today = new \DateTimeImmutable($before);
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $messages = $mailbox->getMessages(new Before($today));
        $count = 0;
        foreach ($messages as $message) {
            $message->delete();
            $count++;
            if ($count >= $limit) {
                break;
            }
        }
        $connection->expunge();
        $directory = './tmp/attachments/';
        Util::rrmdir($directory);
        CronLog::add('Deleted ' . $messages->count() . ' messages older than ' . $before->diffForHumans());
        return;
    }
}
