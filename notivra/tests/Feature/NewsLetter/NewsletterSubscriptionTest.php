<?php

namespace Tests\Feature\Newsletter;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_subscribe_to_newsletter(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'reader@example.com',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => 'Terima kasih telah berlangganan!',
            ]);

        $this->assertDatabaseHas('subscribers', [
            'email' => 'reader@example.com',
            'is_active' => true,
        ]);
    }

    public function test_newsletter_subscription_requires_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_newsletter_subscription_requires_valid_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'invalid-email',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_newsletter_subscription_rejects_duplicate_email(): void
    {
        Subscriber::create([
            'email' => 'reader@example.com',
        ]);

        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'reader@example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('subscribers', 1);
    }
}
