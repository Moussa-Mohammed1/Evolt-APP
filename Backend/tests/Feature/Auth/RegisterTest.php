<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    public function test_user_can_register()
    {

        $response = $this->postJson('/api/register', [
            'name' => 'moussa mohammed',
            'email' => 'moussa@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(201)->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token'
        ]);
        $this->assertDatabaseHas(
            'users',
            [
                'email' => 'moussa@gmail.com',
            ]
        );
    }
    
}
