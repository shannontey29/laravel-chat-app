<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure Scout uses the database driver during tests
        config(['scout.driver' => 'database']);
    }

    public function test_search_returns_matches_by_name_and_username(): void
    {
        // Users
        $alice = User::factory()->create(['name' => 'Alice Wonder', 'username' => 'alice_wonder']);
        $bob = User::factory()->create(['name' => 'Bob Builder', 'username' => 'bob_builder']);
        $carol = User::factory()->create(['name' => 'Carol Danvers', 'username' => 'carol_d']);

        // Search by partial name (case-insensitive)
        $res1 = $this->getJson('/api/users/search?q=ali')
            ->assertOk()
            ->assertJsonPath('query', 'ali')
            ->json();

        $ids1 = collect($res1['data'])->pluck('id');
        $this->assertTrue($ids1->contains($alice->id));
        $this->assertFalse($ids1->contains($bob->id));

        // Search by partial username
        $res2 = $this->getJson('/api/users/search?q=builder')
            ->assertOk()
            ->assertJsonPath('query', 'builder')
            ->json();

        $ids2 = collect($res2['data'])->pluck('id');
        $this->assertTrue($ids2->contains($bob->id));
        $this->assertFalse($ids2->contains($carol->id));
    }

    public function test_search_returns_empty_when_no_matches(): void
    {
        User::factory()->count(3)->create();

        $this->getJson('/api/users/search?q=nomatchkeyword')
            ->assertOk()
            ->assertJsonPath('query', 'nomatchkeyword')
            ->assertJson(fn ($json) => $json->whereType('data', 'array')->etc())
            ->assertJsonCount(0, 'data');
    }

    public function test_search_works_with_no_query_parameter(): void
    {
        User::factory()->count(2)->create();

        $this->getJson('/api/users/search')
            ->assertOk()
            ->assertJson(fn ($json) => $json
                ->where('query', null)
                ->whereType('data', 'array')
                ->etc()
            );
    }
}
 
