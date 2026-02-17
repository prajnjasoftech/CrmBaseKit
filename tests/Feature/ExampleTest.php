<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the login page is accessible.
     */
    public function test_the_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_are_redirected(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
