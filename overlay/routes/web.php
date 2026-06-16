<?php

use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Tags\Index as TagsIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\MailAccounts\Index as MailAccountsIndex;
use App\Livewire\Admin\Pipelines\Index as PipelinesIndex;
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
    Route::get('clients', ClientsIndex::class)->name('clients.index');
    Route::get('clients/{client}', ClientsShow::class)->name('clients.show');

    // IT Documentation — global pages
    Route::get('assets', AssetsIndex::class)->name('assets.index');
    Route::get('domains', DomainsIndex::class)->name('domains.index');

    // CRM — Deals
    Route::get('deals', DealsIndex::class)->name('deals.index');
    Route::get('deals/{deal}', DealsShow::class)->name('deals.show');

    // Support — Tickets
    Route::get('tickets', TicketsIndex::class)->name('tickets.index');
    Route::get('tickets/{ticket}', TicketsShow::class)->name('tickets.show');

    // Administration
    Route::middleware('permission:manage users')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', UsersIndex::class)->name('users.index');
        Route::get('roles', RolesIndex::class)->name('roles.index');
        Route::get('tags', TagsIndex::class)->name('tags.index');
        Route::get('categories', CategoriesIndex::class)->name('categories.index');
        Route::get('mail-accounts', MailAccountsIndex::class)->name('mail-accounts.index');
        Route::get('pipelines', PipelinesIndex::class)->name('pipelines.index');
    });

    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

require __DIR__.'/auth.php';
