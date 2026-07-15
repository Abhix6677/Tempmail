<?php

namespace App\Services;

use App\Models\TempEmail;
use App\Models\ReceivedEmail;
use Carbon\Carbon;

class ImapTempMailService
{
    /**
     * Poll inbox and match emails to active dotted aliases
     */
    public function poll(): void
    {
        $connection = TMail::connectMailBox();
        $mailbox = $connection->getMailbox('INBOX');
        $messages = $mailbox->getMessages();

        foreach ($messages as $message) {

            $headers = $message->getHeaders();

            $possibleRecipients = [];

            // Extract headers safely and preserve dots
            foreach (['to', 'delivered-to', 'x-original-to'] as $key) {
                if (!empty($headers[$key])) {
                    $value = strtolower(trim($headers[$key]));

                    // Extract pure email if header contains name + email
                    if (preg_match('/<(.+?)>/', $value, $matches)) {
                        $value = strtolower($matches[1]);
                    }

                    $possibleRecipients[] = $value;
                }
            }

            if (empty($possibleRecipients)) {
                \Log::warning('IMAP: No recipient headers found for message ID ' . $message->getMessageId());
                continue;
            }

            foreach ($possibleRecipients as $recipient) {

                // STRICT dot-preserving exact match
                $temp = TempEmail::where('generated_address', $recipient)
                    ->where('is_active', true)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

                \Log::info('IMAP Compare', [
                    'header_value' => $recipient,
                    'matched_address' => $temp?->generated_address,
                ]);

                if (!$temp) {
                    continue; // Skip if no exact match
                }

                $alreadyStored = ReceivedEmail::where('temp_email_id', $temp->id)
                    ->where('subject', $message->getSubject())
                    ->where('received_at', Carbon::parse($message->getDate()))
                    ->exists();

                if ($alreadyStored) {
                    continue;
                }

                ReceivedEmail::create([
                    'temp_email_id' => $temp->id,
                    'from_address' => $message->getFrom()[0]->mail ?? 'unknown',
                    'subject' => $message->getSubject(),
                    'body' => $message->getTextBody() ?? $message->getHTMLBody(),
                    'received_at' => Carbon::parse($message->getDate()),
                ]);

                // Stop checking other headers once matched
                break;
            }
        }
    }
}
