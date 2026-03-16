<?php

namespace Tests\Feature\Auth;

use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_can_login()
    {
        $user = User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'moussa@gmail.com',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)->assertJsonStructure(
            [
                'message',
                'user',
                'token',
            ]
        );
    }

    public function test_login_fails_with_wrong_password()
    {
        User::create([
            'name' => 'moussa',
            'email' => 'moussa@gmail.com',
            'password' => Hash::make('password123')
        ]);

        $reponse =  $this->postJson('/api/login', [
            'email' => 'moussa@gmail.com',
            'password' => 'password',
        ]);

        $reponse->assertStatus(422);
    }
    
}
