<?php

namespace App\Console\Commands;

use App\Models\CronLog;
use App\Models\Message;
use App\Models\ReceivedEmail;
use App\Models\TempEmail;
use App\Services\TMail;
use App\Services\Util;
use Carbon\Carbon;
use Ddeboer\Imap\Search\Date\Before;
use Illuminate\Console\Command;

class CleanOldEmails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tmail:clean-old-emails';

    /**
     * The console command description.
     */
    protected $description = 'Delete all emails (temp_emails, received_emails, messages) older than 10 minutes';

    /**
     * The retention period in minutes.
     */
    protected int $retentionMinutes = 10;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = Carbon::now()->subMinutes($this->retentionMinutes);
        $totalDeleted = 0;

        // 1. Delete expired TempEmail records (cascades to ReceivedEmail via FK constraint)
        $expiredTemps = TempEmail::where('created_at', '<', $cutoff)
            ->orWhere(function ($query) use ($cutoff) {
                $query->where('expires_at', '<', $cutoff)
                    ->where('expires_at', '!=', null);
            })
            ->get();

        $tempCount = $expiredTemps->count();
        if ($tempCount > 0) {
            // Bulk delete - foreign key cascade will handle ReceivedEmail
            TempEmail::whereIn('id', $expiredTemps->pluck('id'))->delete();
            $totalDeleted += $tempCount;
            $this->info("Deleted {$tempCount} expired temp email records.");
        }

        // 2. Clean up any orphaned ReceivedEmail records (safety net)
        $orphanedReceived = ReceivedEmail::where('created_at', '<', $cutoff)->delete();
        if ($orphanedReceived > 0) {
            $totalDeleted += $orphanedReceived;
            $this->info("Deleted {$orphanedReceived} orphaned received email records.");
        }

        // 3. Handle delivery engine - delete old Message records
        if (config('app.settings.engine') === 'delivery') {
            $oldMessages = Message::where('created_at', '<', $cutoff)->get();
            $messageCount = $oldMessages->count();

            foreach ($oldMessages as $message) {
                $directory = './tmp/attachments/' . $message->id . '/';
                if (is_dir($directory)) {
                    Util::rrmdir($directory);
                }
                $message->delete();
            }

            if ($messageCount > 0) {
                $totalDeleted += $messageCount;
                $this->info("Deleted {$messageCount} delivery engine messages.");
            }
        }

        // 4. Handle IMAP engine - delete old messages from IMAP server
        if (config('app.settings.engine') !== 'delivery') {
            try {
                $connection = TMail::connectMailBox();
                $mailbox = $connection->getMailbox('INBOX');
                $today = new \DateTimeImmutable($cutoff->format('Y-m-d'));
                $messages = $mailbox->getMessages(new Before($today));

                $imapCount = 0;
                $limit = 50; // Process in batches to avoid timeouts
                foreach ($messages as $message) {
                    $message->delete();
                    $imapCount++;
                    if ($imapCount >= $limit) {
                        break;
                    }
                }
                try {
                    $connection->expunge();
                } catch (\Throwable $e) {
                    \Log::warning('IMAP expunge failed in cleanup (non-fatal): ' . $e->getMessage());
                }


                // Clean up attachment directory
                $directory = './tmp/attachments/';
                if (is_dir($directory)) {
                    Util::rrmdir($directory);
                }

                if ($imapCount > 0) {
                    $totalDeleted += $imapCount;
                    $this->info("Deleted {$imapCount} IMAP messages.");
                }
            } catch (\Throwable $e) {
                $this->error("IMAP cleanup failed: " . $e->getMessage());
                \Log::error('IMAP cleanup failed', ['message' => $e->getMessage()]);
            }
        }

        // Log the cleanup action
        if ($totalDeleted > 0) {
            CronLog::add("Auto-clean: Deleted {$totalDeleted} emails older than {$this->retentionMinutes} minutes");
            $this->info("Total emails deleted: {$totalDeleted}");
        } else {
            $this->info("No emails older than {$this->retentionMinutes} minutes found.");
        }

        return Command::SUCCESS;
    }
}
