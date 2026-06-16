<?php

namespace App\Console\Commands;

use App\Jobs\ProcessInboundEmail;
use App\Models\MailAccount;
use Illuminate\Console\Command;

class PollMailboxes extends Command
{
    protected $signature   = 'mail:poll {--account= : Poll a specific account ID}';
    protected $description = 'Poll IMAP mailboxes and dispatch jobs to process new emails into tickets';

    public function handle(): int
    {
        $query = MailAccount::where('is_active', true);

        if ($id = $this->option('account')) {
            $query->where('id', $id);
        }

        $accounts = $query->get();

        if ($accounts->isEmpty()) {
            $this->info('No active mail accounts configured.');
            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $this->line("Polling {$account->name} ({$account->username})…");

            try {
                $this->pollAccount($account);
                $account->update(['last_polled_at' => now()]);
            } catch (\Throwable $e) {
                $this->error("Failed to poll {$account->name}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    private function pollAccount(MailAccount $account): void
    {
        $encryption = match ($account->encryption) {
            'ssl'  => '/ssl',
            'tls'  => '/tls',
            default => '',
        };

        $mailbox = "{{$account->host}:{$account->port}/imap{$encryption}}{$account->mailbox}";

        $imap = @imap_open($mailbox, $account->username, $account->decrypted_password, 0, 1);

        if (! $imap) {
            throw new \RuntimeException(imap_last_error() ?: 'IMAP connection failed');
        }

        // Fetch UNSEEN messages only
        $uids = imap_search($imap, 'UNSEEN', SE_UID);

        if (! $uids) {
            $this->line("  No new messages.");
            imap_close($imap);
            return;
        }

        $count = 0;

        foreach ($uids as $uid) {
            $header  = imap_fetchheader($imap, $uid, FT_UID);
            $parsed  = imap_rfc822_parse_headers($header);
            $struct  = imap_fetchstructure($imap, $uid, FT_UID);

            $fromEmail = strtolower($parsed->from[0]->mailbox . '@' . $parsed->from[0]->host);
            $fromName  = isset($parsed->from[0]->personal)
                ? imap_utf8($parsed->from[0]->personal)
                : $fromEmail;

            $subject    = isset($parsed->subject) ? imap_utf8($parsed->subject) : '(no subject)';
            $messageId  = trim($parsed->message_id ?? uniqid('email-', true));
            $inReplyTo  = isset($parsed->in_reply_to) ? trim($parsed->in_reply_to) : null;

            $body = $this->extractBody($imap, $uid, $struct);

            ProcessInboundEmail::dispatch(
                $account->id,
                $fromEmail,
                $fromName,
                $subject,
                $body,
                $messageId,
                $inReplyTo,
            );

            // Mark as seen
            imap_setflag_full($imap, (string) $uid, '\\Seen', ST_UID);
            $count++;
        }

        imap_close($imap);
        $this->line("  Dispatched {$count} message(s).");
    }

    private function extractBody($imap, int $uid, object $struct): string
    {
        // Try to get plain-text part first, fall back to HTML stripped of tags
        if ($struct->type === TYPETEXT) {
            $body = imap_fetchbody($imap, $uid, '1', FT_UID | FT_PEEK);
            if ($struct->encoding === ENCBASE64) $body = base64_decode($body);
            if ($struct->encoding === ENCQUOTEDPRINTABLE) $body = quoted_printable_decode($body);
            if (strtolower($struct->subtype ?? '') === 'html') $body = strip_tags($body);
            return trim($body);
        }

        if ($struct->type === TYPEMULTIPART && isset($struct->parts)) {
            $plain = null;
            $html  = null;

            foreach ($struct->parts as $i => $part) {
                $partNum = (string) ($i + 1);
                if ($part->type === TYPETEXT) {
                    $raw = imap_fetchbody($imap, $uid, $partNum, FT_UID | FT_PEEK);
                    if ($part->encoding === ENCBASE64) $raw = base64_decode($raw);
                    if ($part->encoding === ENCQUOTEDPRINTABLE) $raw = quoted_printable_decode($raw);
                    if (strtolower($part->subtype ?? '') === 'plain') $plain = $raw;
                    elseif (strtolower($part->subtype ?? '') === 'html') $html = $raw;
                }
            }

            if ($plain) return trim($plain);
            if ($html) return trim(strip_tags($html));
        }

        return '(no body)';
    }
}
