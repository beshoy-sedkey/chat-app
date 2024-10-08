<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login()
    {

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'name',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/users/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data'
        ]);
    }

       /** @test */
       public function user_can_register()
       {
           $response = $this->postJson('/api/users/register', [
               'email' => 'user@example.com',
               'name' => 'name',
               'password' => 'password',
           ]);

           $response->assertStatus(200);
           $response->assertJsonStructure([
               'message',
               'data'
           ]);
       }
}
