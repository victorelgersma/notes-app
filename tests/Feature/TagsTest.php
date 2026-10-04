<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tag_can_be_renamed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Kusukawa', 'tags' => 'hps']);
        $tag = $user->tags()->first();

        $this->actingAs($user)->put("/tags/{$tag->id}", ['name' => '  History of Science '])
            ->assertRedirect(route('notes.index'));

        $this->assertSame('history of science', $tag->fresh()->name);
        $this->assertSame(['history of science'], $user->notes()->first()->tags->pluck('name')->all());
    }

    public function test_renaming_the_tag_being_viewed_follows_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Kusukawa', 'tags' => 'hps']);
        $tag = $user->tags()->first();

        $this->actingAs($user)->put("/tags/{$tag->id}", ['name' => 'history', 'return_tag' => 'hps'])
            ->assertRedirect(route('notes.index', ['tag' => 'history']));
    }

    public function test_renaming_onto_an_existing_tag_merges_them(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Both', 'tags' => 'hps, history']);
        $this->actingAs($user)->post('/notes', ['title' => 'Only hps', 'tags' => 'hps']);
        $hps = $user->tags()->where('name', 'hps')->first();

        $this->actingAs($user)->put("/tags/{$hps->id}", ['name' => 'History'])
            ->assertRedirect(route('notes.index'));

        $this->assertModelMissing($hps);
        $this->assertSame(['history'], $user->tags()->pluck('name')->all());
        $this->assertSame(2, $user->tags()->first()->notes()->count());
    }

    public function test_a_tag_name_is_required(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Kusukawa', 'tags' => 'hps']);
        $tag = $user->tags()->first();

        $this->actingAs($user)->put("/tags/{$tag->id}", ['name' => '   '])
            ->assertSessionHasErrors('name');

        $this->assertSame('hps', $tag->fresh()->name);
    }

    public function test_users_cannot_rename_each_others_tags(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($owner)->post('/notes', ['title' => 'Private', 'tags' => 'mine']);
        $tag = $owner->tags()->first();

        $this->actingAs($other)->put("/tags/{$tag->id}", ['name' => 'stolen'])->assertNotFound();

        $this->assertSame('mine', $tag->fresh()->name);
    }
}
