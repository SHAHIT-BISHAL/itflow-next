<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Pipeline;
use App\Models\Ticket;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_runs_end_to_end(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $this->assertGreaterThanOrEqual(2, Client::count());
        $this->assertGreaterThanOrEqual(3, Ticket::count());
        $this->assertGreaterThanOrEqual(2, Deal::count());
        $this->assertGreaterThanOrEqual(3, Activity::count());
        $this->assertGreaterThanOrEqual(2, Invoice::count());
        $this->assertGreaterThanOrEqual(1, Payment::count());
        $this->assertGreaterThanOrEqual(3, Expense::count());
        $this->assertGreaterThanOrEqual(1, Pipeline::count());
    }
}
