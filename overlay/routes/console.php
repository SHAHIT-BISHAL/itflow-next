<?php

use Illuminate\Support\Facades\Schedule;

// Poll all active IMAP mailboxes every minute
Schedule::command('mail:poll')->everyMinute()->withoutOverlapping();
