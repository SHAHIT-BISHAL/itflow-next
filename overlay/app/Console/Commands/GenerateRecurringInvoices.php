<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RecurringInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature   = 'invoices:generate-recurring';
    protected $description = 'Generate invoices from active recurring templates that are due today or overdue';

    public function handle(): void
    {
        $due = RecurringInvoice::with('items')
            ->where('is_active', true)
            ->where('next_run_at', '<=', today())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No recurring invoices due.');
            return;
        }

        foreach ($due as $recurring) {
            $invoice = Invoice::create([
                'company_id'     => $recurring->company_id,
                'client_id'      => $recurring->client_id,
                'invoice_number' => Invoice::nextNumber($recurring->company_id),
                'status'         => 'sent',
                'currency'       => $recurring->currency,
                'issue_date'     => today(),
                'due_date'       => today()->addDays($recurring->net_terms),
                'notes'          => $recurring->notes,
                'sent_at'        => now(),
            ]);

            foreach ($recurring->items as $i => $item) {
                $amount = $item->quantity * $item->unit_price;
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'tax_rate'    => $item->tax_rate,
                    'amount'      => $amount,
                    'sort_order'  => $i,
                ]);
            }

            $invoice->recalculate();

            $recurring->update([
                'last_run_at' => today(),
                'next_run_at' => $this->nextDate($recurring->next_run_at, $recurring->frequency),
            ]);

            $this->info("Created {$invoice->invoice_number} for client #{$recurring->client_id}");
        }

        $this->info("Done — {$due->count()} invoice(s) generated.");
    }

    private function nextDate(string $from, string $frequency): Carbon
    {
        $date = Carbon::parse($from);
        return match ($frequency) {
            'weekly'    => $date->addWeek(),
            'quarterly' => $date->addMonths(3),
            'annually'  => $date->addYear(),
            default     => $date->addMonth(), // monthly
        };
    }
}
