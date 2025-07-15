<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        // El registro público está deshabilitado en este sistema
        // Los usuarios se autentican vía Active Directory
        $this->markTestSkipped('Registration is disabled - users authenticate via Active Directory');
        
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // El registro público está deshabilitado en este sistema
        // Los usuarios se autentican vía Active Directory
        $this->markTestSkipped('Registration is disabled - users authenticate via Active Directory');
        
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
