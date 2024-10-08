<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Message;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_messages()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user); // Generate JWT

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/messages');

        $response->assertOk();
        $response->assertJsonStructure([
            'messages'
        ]);
    }

    public function test_user_can_create_message()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $messageData = ['content' => 'Hello World!', 'recipient_id' => $user->id,  'sender_id' => auth('api')->id()];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/messages', $messageData);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Message Sent Successfully'
        ]);
    }
}
