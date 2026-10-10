<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;

class NavigationAssetsTest extends DatabaseTestCase
{
    public static function rolePages(): array
    {
        return [
            ['admin', '/admin/dashboard'],
            ['organizer', '/organizer/aktivitas'],
            ['volunteer', '/volunteer/aktivitas'],
        ];
    }

    #[DataProvider('rolePages')]
    public function test_role_layout_does_not_wait_for_an_external_font_stylesheet(string $role, string $path): void
    {
        $user = User::factory()->create(['role' => $role, 'organizer_status' => 'active']);
        $response = $this->actingAs($user)->get($path)->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//link[@rel="stylesheet" and (starts-with(@href,"https://") or starts-with(@href,"http://")) and contains(@href,"fonts.")]')->length);
        $this->assertSame(1, $xpath->query('//link[@rel="preload" and @as="font" and @href="'.asset('fonts/inter/inter-latin-400-normal.woff2').'"]')->length);
        // Verify deployable local files rather than accepting a preload that points to a missing asset.
        foreach ([400, 500, 600, 700] as $weight) {
            $file = public_path("fonts/inter/inter-latin-$weight-normal.woff2");
            $this->assertFileIsReadable($file);
            $this->assertSame('wOF2', file_get_contents($file, false, null, 0, 4));
        }
        $this->assertFileIsReadable(public_path('fonts/inter/OFL.txt'));
    }

    public function test_public_and_auth_pages_use_local_fonts_too(): void
    {
        foreach (['/', '/login', '/register'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee(asset('fonts/inter/inter-latin-400-normal.woff2'), false)
                ->assertDontSee('fonts.bunny.net');
        }
    }
}
