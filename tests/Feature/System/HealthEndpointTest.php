<?php

namespace Tests\Feature\System;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_the_api_health_endpoint_returns_a_successful_response(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_the_application_does_not_expose_a_web_homepage(): void
    {
        $this->get('/')->assertNotFound();
    }
}
