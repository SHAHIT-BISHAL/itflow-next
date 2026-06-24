# Production scheduler (systemd)

The VM has no `cron` package, so the Laravel scheduler runs via a systemd timer
instead of the usual crontab line. These two units run `php artisan schedule:run`
every minute as `www-data`, which drives `mail:poll` (email-to-ticket) and
`invoices:generate-recurring` (daily 06:00).

## Install

```bash
sudo cp itflow-schedule.service /etc/systemd/system/
sudo cp itflow-schedule.timer   /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now itflow-schedule.timer
```

## Verify

```bash
systemctl list-timers itflow-schedule.timer   # next/last run
journalctl -u itflow-schedule.service --no-pager | tail   # run output
```

Adjust `ExecStart` PHP path (`/usr/bin/php`) if the VM uses a versioned binary
(e.g. `/usr/bin/php8.3`).
