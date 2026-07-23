<?php

namespace Tests\Feature;

use App\Models\OriginVisit;
use App\Services\OriginTokenService;
use App\Services\OriginTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OriginTrackingTest extends TestCase
{
    use RefreshDatabase;

    private OriginTokenService $tokens;
    private OriginTrackingService $tracking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokens = app(OriginTokenService::class);
        $this->tracking = app(OriginTrackingService::class);
    }

    public function test_records_touch_and_attributes_pro(): void
    {
        $token = $this->tokens->getActiveToken(10)->token;
        $visit = $this->tracking->recordTouch('visitor-1', $token, '/produit/x', 'https://google.com');

        $this->assertNotNull($visit);
        $this->assertSame(10, $this->tracking->getAttributedProId('visitor-1'));
    }

    public function test_unknown_token_records_nothing(): void
    {
        $visit = $this->tracking->recordTouch('visitor-2', 'a24f0000000000');
        $this->assertNull($visit);
        $this->assertNull($this->tracking->getAttributedProId('visitor-2'));
        $this->assertDatabaseCount('origin_visits', 0);
    }

    public function test_last_click_wins(): void
    {
        $tokenA = $this->tokens->getActiveToken(1)->token;
        $tokenB = $this->tokens->getActiveToken(2)->token;

        $this->tracking->recordTouch('visitor-3', $tokenA);
        $this->tracking->recordTouch('visitor-3', $tokenB); // dernier clic = pro 2

        $this->assertSame(2, $this->tracking->getAttributedProId('visitor-3'));
        $this->assertDatabaseCount('origin_visits', 1); // un seul row par visiteur
    }

    public function test_first_touch_preserved(): void
    {
        $tokenA = $this->tokens->getActiveToken(1)->token;
        $this->tracking->recordTouch('visitor-4', $tokenA);
        $first = OriginVisit::where('visitor_uuid', 'visitor-4')->first()->first_touch_at->timestamp;

        $tokenB = $this->tokens->getActiveToken(2)->token;
        $this->tracking->recordTouch('visitor-4', $tokenB);
        $after = OriginVisit::where('visitor_uuid', 'visitor-4')->first()->first_touch_at->timestamp;

        $this->assertSame($first, $after);
    }

    public function test_expired_attribution_returns_null(): void
    {
        $token = $this->tokens->getActiveToken(5)->token;
        $this->tracking->recordTouch('visitor-5', $token);

        OriginVisit::where('visitor_uuid', 'visitor-5')->update(['expires_at' => now()->subDay()]);
        $this->assertNull($this->tracking->getAttributedProId('visitor-5'));
    }
}
