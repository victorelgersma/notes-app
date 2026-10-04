<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\LoginLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_requesting_a_link_emails_it(): void
    {
        Notification::fake();

        $this->post('/login', ['email' => 'Victor@Example.com'])->assertRedirect(route('login'));

        Notification::assertSentTo(User::where('email', 'victor@example.com')->first(), LoginLinkNotification::class);
    }

    public function test_the_signed_link_logs_in_and_lands_on_notes(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute('login.verify', now()->addMinutes(15), ['user' => $user->id]);

        $this->get($url)->assertRedirect(route('notes.index', absolute: false));
        $this->assertAuthenticatedAs($user);
    }
}
