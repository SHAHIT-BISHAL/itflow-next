<?php

use Illuminate\Support\Facades\Schedule;

// Poll all active IMAP mailboxes every minute
Schedule::command('mail:poll')->everyMinute()->withoutOverlapping();

// Generate invoices from recurring templates — runs daily at 06:00
Schedule::command('invoices:generate-recurring')->dailyAt('06:00')->withoutOverlapping();
