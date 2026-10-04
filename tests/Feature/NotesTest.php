<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/notes')->assertRedirect(route('login'));
    }

    public function test_a_note_can_be_created_with_rich_text_and_tags(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', [
            'title' => 'Reading list',
            'body' => '<div><strong>Kusukawa</strong> ch. 4</div>',
            'tags' => 'HPS, reading',
        ])->assertRedirect(route('notes.index'));

        $note = $user->notes()->first();
        $this->assertSame('Reading list', $note->title);
        $this->assertSame('<div><strong>Kusukawa</strong> ch. 4</div>', $note->body);
        $this->assertSame(['hps', 'reading'], $note->tags->pluck('name')->all());
    }

    public function test_creating_from_a_tag_view_returns_to_that_tag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['body' => 'first', 'tags' => 'hps']);

        $this->actingAs($user)->post('/notes', [
            'body' => 'second',
            'tags' => 'hps',
            'return_tag' => 'hps',
        ])->assertRedirect(route('notes.index', ['tag' => 'hps']));
    }

    public function test_tag_view_shows_only_that_tags_notes_and_prefills_the_composer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Tagged one', 'tags' => 'hps']);
        $this->actingAs($user)->post('/notes', ['title' => 'Other one', 'tags' => 'dance']);

        $this->actingAs($user)->get('/notes?tag=hps')
            ->assertOk()
            ->assertSee('Tagged one')
            ->assertDontSee('Other one')
            ->assertSee("tagInput(JSON.parse('[\\u0022hps\\u0022]')", false);
    }

    public function test_unknown_tag_redirects_to_all_notes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/notes?tag=nope')->assertRedirect(route('notes.index'));
    }

    public function test_empty_notes_are_discarded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', ['title' => ' ', 'body' => '<div><br></div>', 'tags' => 'x']);

        $this->assertSame(0, $user->notes()->count());
        $this->assertSame(0, $user->tags()->count());
    }

    public function test_html_is_sanitised(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/notes', [
            'body' => '<div onclick="alert(1)">Hi<script>alert(2)</script><img src=x onerror=alert(3)>'
                .'<a href="javascript:alert(4)">bad</a> <a href="https://example.com" style="color:red">good</a>'
                .'<span class="x">kept text</span></div>',
        ]);

        $body = $user->notes()->first()->body;

        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('script', $body);
        $this->assertStringNotContainsString('img', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringNotContainsString('style', $body);
        $this->assertStringNotContainsString('<span', $body);
        $this->assertStringContainsString('kept text', $body);
        $this->assertStringContainsString('<a href="https://example.com" target="_blank" rel="noopener noreferrer nofollow">good</a>', $body);
    }

    public function test_a_note_can_be_updated_and_unused_tags_are_pruned(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Old', 'tags' => 'a, b']);
        $note = $user->notes()->first();

        $this->actingAs($user)->put("/notes/{$note->id}", [
            'title' => 'New',
            'body' => '<ul><li>one</li></ul>',
            'tags' => 'b, c',
        ])->assertRedirect(route('notes.index'));

        $note->refresh();
        $this->assertSame('New', $note->title);
        $this->assertSame(['b', 'c'], $note->tags->pluck('name')->all());
        $this->assertSame(['b', 'c'], $user->tags()->orderBy('name')->pluck('name')->all());
    }

    public function test_a_note_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/notes', ['title' => 'Bye', 'tags' => 'gone']);
        $note = $user->notes()->first();

        $this->actingAs($user)->delete("/notes/{$note->id}")->assertRedirect(route('notes.index'));

        $this->assertModelMissing($note);
        $this->assertSame(0, $user->tags()->count());
    }

    public function test_users_cannot_touch_each_others_notes(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $note = $owner->notes()->create(['title' => 'Private']);

        $this->actingAs($other)->put("/notes/{$note->id}", ['title' => 'Hacked'])->assertNotFound();
        $this->actingAs($other)->delete("/notes/{$note->id}")->assertNotFound();
        $this->actingAs($other)->get('/notes')->assertDontSee('Private');

        $this->assertSame('Private', $note->fresh()->title);
    }
}
