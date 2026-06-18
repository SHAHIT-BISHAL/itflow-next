<?php

use App\Http\Controllers\InvoicePdfController;
use App\Livewire\Admin\AuditLogs\Index as AuditLogsIndex;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Reports\Overview as ReportsOverview;
use App\Livewire\Reports\Revenue as ReportsRevenue;
use App\Livewire\Reports\Tickets as ReportsTickets;
use App\Livewire\Reports\Expenses as ReportsExpenses;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Tags\Index as TagsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\MailAccounts\Index as MailAccountsIndex;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\Invoices\Create as InvoicesCreate;
use App\Livewire\Invoices\Index as InvoicesIndex;
use App\Livewire\Invoices\Show as InvoicesShow;
use App\Livewire\Admin\Pipelines\Index as PipelinesIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Deals\Index as DealsIndex;
use App\Livewire\Deals\Show as DealsShow;
use App\Livewire\Assets\Index as AssetsIndex;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientsShow;
use App\Livewire\Dashboard;
use App\Livewire\Domains\Index as DomainsIndex;
use App\Livewire\Tickets\Index as TicketsIndex;
use App\Livewire\Tickets\Show as TicketsShow;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::post('/logout', function (\App\Livewire\Actions\Logout $logout) {
    $logout();

    return redirect('/');
})->name('logout')->middleware('auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    // Clients (+ nested Contacts / Locations / Assets / Documents / Passwords / Domains tabs)
    Route::get('clients', ClientsIndex::class)->name('clients.index')->middleware('permission:view clients');
    Route::get('clients/{client}', ClientsShow::class)->name('clients.show')->middleware('permission:view clients');

    // IT Documentation - global pages
    Route::get('assets', AssetsIndex::class)->name('assets.index')->middleware('permission:view assets');
    Route::get('domains', DomainsIndex::class)->name('domains.index')->middleware('permission:view domains');

    // Billing
    Route::get('invoices', InvoicesIndex::class)->name('invoices.index')->middleware('permission:view invoices');
    Route::get('invoices/create', InvoicesCreate::class)->name('invoices.create')->middleware('permission:manage invoices');
    Route::get('invoices/{invoice}/edit', InvoicesCreate::class)->name('invoices.edit')->middleware('permission:manage invoices');
    Route::get('invoices/{invoice}', InvoicesShow::class)->name('invoices.show')->middleware('permission:view invoices');
    Route::get('expenses', ExpensesIndex::class)->name('expenses.index')->middleware('permission:view expenses');

    // CRM - Deals
    Route::get('deals', DealsIndex::class)->name('deals.index')->middleware('permission:view deals');
    Route::get('deals/{deal}', DealsShow::class)->name('deals.show')->middleware('permission:view deals');

    // Support - Tickets
    Route::get('tickets', TicketsIndex::class)->name('tickets.index')->middleware('permission:view tickets');
    Route::get('tickets/{ticket}', TicketsShow::class)->name('tickets.show')->middleware('permission:view tickets');

    // Invoice PDF
    Route::get('invoices/{invoice}/pdf', InvoicePdfController::class)->name('invoices.pdf')->middleware('permission:view invoices');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', ReportsOverview::class)->name('index')->middleware('permission:view reports');
        Route::get('revenue', ReportsRevenue::class)->name('revenue')->middleware('permission:view reports');
        Route::get('tickets', ReportsTickets::class)->name('tickets')->middleware('permission:view reports');
        Route::get('expenses', ReportsExpenses::class)->name('expenses')->middleware('permission:view reports');
    });

    // Administration
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('users', UsersIndex::class)->name('users.index')->middleware('permission:manage users');
        Route::get('roles', RolesIndex::class)->name('roles.index')->middleware('permission:manage roles');
        Route::get('tags', TagsIndex::class)->name('tags.index')->middleware('permission:manage tags');
        Route::get('categories', CategoriesIndex::class)->name('categories.index')->middleware('permission:manage categories');
        Route::get('mail-accounts', MailAccountsIndex::class)->name('mail-accounts.index')->middleware('permission:manage mail accounts');
        Route::get('pipelines', PipelinesIndex::class)->name('pipelines.index')->middleware('permission:manage pipelines');
        Route::get('audit-logs', AuditLogsIndex::class)->name('audit-logs.index')->middleware('permission:view audit logs');
        Route::get('settings', SettingsIndex::class)->name('settings.index')->middleware('permission:manage settings');
    });

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';
