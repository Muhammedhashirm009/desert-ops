<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    /**
     * The authenticated admin user instance.
     *
     * @var \App\Models\User|null
     */
    protected $adminUser;

    /**
     * Whether the test should automatically authenticate an admin user.
     *
     * @var bool
     */
    protected $shouldAuthenticate = true;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->shouldAuthenticate) {
            $this->adminUser = User::factory()->create();
            $this->actingAs($this->adminUser);
        }
    }
}
