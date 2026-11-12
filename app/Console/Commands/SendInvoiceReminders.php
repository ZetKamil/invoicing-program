<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Mail\InvoiceReminderMail;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic email reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting invoice reminder check...');

        // Find invoices that are SENT and past their due date
        // Since we are running in a global console command, we must bypass the bedrijf scope
        // or just rely on the fact that cron runs as system, but HasBedrijf might block it.
        // Let's use `withoutGlobalScopes()` since this is a system job.
        $overdueInvoices = Invoice::withoutGlobalScopes()
            ->where('status', InvoiceStatus::SENT)
            ->whereDate('due_date', '<', Carbon::today())
            ->get();

        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            // Update status to OVERDUE
            $invoice->update(['status' => InvoiceStatus::OVERDUE]);

            // Queue the email to the customer
            if ($invoice->customer && $invoice->customer->email) {
                Mail::to($invoice->customer->email)->queue(new InvoiceReminderMail($invoice));
                $count++;
            }
        }

        $this->info("Successfully sent {$count} reminders and updated invoice statuses.");
    }
}
