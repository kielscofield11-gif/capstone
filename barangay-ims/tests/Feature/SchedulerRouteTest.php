<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduler_endpoint_is_disabled_without_a_token(): void
    {
        config(['deploy.scheduler_token' => null]);

        $this->get('/scheduler/run?token=anything')->assertNotFound();
    }

    public function test_scheduler_endpoint_rejects_a_wrong_token(): void
    {
        config(['deploy.scheduler_token' => 'correct-token']);

        $this->get('/scheduler/run?token=wrong-token')->assertNotFound();
    }

    public function test_scheduler_endpoint_runs_with_the_correct_token(): void
    {
        config(['deploy.scheduler_token' => 'correct-token']);

        $this->get('/scheduler/run?token=correct-token')->assertOk();
    }
}
