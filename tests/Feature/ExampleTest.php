<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSeeText('Punya skill.')
            ->assertSeeText('Bikin berarti.')
            ->assertSee('Buat akun SkillMatch')
            ->assertSee('Sebelum')
            ->assertSee('Lihat semua event')
            ->assertSee(route('events.index'))
            ->assertSee(route('register'));
    }
}
