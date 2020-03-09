<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $user = factory(User::class)->create();

        $response = $this
            ->actingAs($user)
            ->post(route('reaction'), [
                'movie_id' => 2,
                'reaction' => 'like'
            ]);

        

        $response->assertStatus(200);
        // repeating the $response to simulate giving two like reactions, which is not allowed
        $response = $this
        ->actingAs($user)
        ->post(route('reaction'), [
            'movie_id' => 2,
            'reaction' => 'like'
        ]);

    

    $response->assertStatus(200);
    }
}
