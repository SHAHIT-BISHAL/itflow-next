<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Deal;
use App\Models\Document;
use App\Models\DocumentRelation;
use App\Models\DocumentVersion;
use App\Models\Domain;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\MailAccount;
use App\Models\NumberingSetting;
use App\Models\Password;
use App\Models\PasswordAccessLog;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\RecurringInvoice;
use App\Models\RecurringInvoiceItem;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketEvent;
use App\Models\TicketReply;
use App\Models\User;
use App\Policies\AssetPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ContactPolicy;
use App\Policies\DealPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\DomainPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LocationPolicy;
use App\Policies\MailAccountPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PipelinePolicy;
use App\Policies\RecurringInvoicePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TagPolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Activity::class, DealPolicy::class);
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(CompanySetting::class, SettingPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(CustomField::class, CategoryPolicy::class);
        Gate::policy(CustomFieldValue::class, CategoryPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(DocumentRelation::class, DocumentPolicy::class);
        Gate::policy(DocumentVersion::class, DocumentPolicy::class);
        Gate::policy(Domain::class, DomainPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(InvoiceItem::class, InvoicePolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(MailAccount::class, MailAccountPolicy::class);
        Gate::policy(NumberingSetting::class, SettingPolicy::class);
        Gate::policy(Password::class, PasswordPolicy::class);
        Gate::policy(PasswordAccessLog::class, PasswordPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Pipeline::class, PipelinePolicy::class);
        Gate::policy(PipelineStage::class, PipelinePolicy::class);
        Gate::policy(RecurringInvoice::class, RecurringInvoicePolicy::class);
        Gate::policy(RecurringInvoiceItem::class, RecurringInvoicePolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(TicketAttachment::class, TicketPolicy::class);
        Gate::policy(TicketEvent::class, TicketPolicy::class);
        Gate::policy(TicketReply::class, TicketPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
