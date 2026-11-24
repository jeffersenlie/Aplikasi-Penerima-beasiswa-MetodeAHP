<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** Tamu yang membuka aplikasi diarahkan ke halaman login. */
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/criteria')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }
}
