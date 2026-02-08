<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Health check route exists (Laravel 11 default).
     */
    public function test_health_route_returns_successful_response(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
