<?php

namespace Tests\Feature\Authentication;

use App\Enums\UserRole;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Models\HireDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function mockGoogleUser(
        string $email = 'google@example.com',
        string $name = 'Google Test User',
    ): void {
        $googleUser = Mockery::mock();

        $googleUser->shouldReceive('getEmail')
            ->andReturn($email);

        $googleUser->shouldReceive('getName')
            ->andReturn($name);

        $driver = Mockery::mock();

        $driver->shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($driver);
    }

    private function prepareSession(): void
    {
        $this->app['request']->setLaravelSession(
            $this->app['session.store'],
        );
    }

    public function test_redirect_stores_pending_draft_id(): void
    {
        $draftId = 'draft-test-123';

        $driver = Mockery::mock();

        $driver->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($driver);

        $request = Request::create(
            '/auth/google?d=' . $draftId,
            'GET',
        );

        $request->setLaravelSession($this->app['session.store']);

        $response = app(GoogleAuthController::class)
            ->redirect($request);

        $this->assertSame(
            $draftId,
            $request->session()->get('pending_draft_id'),
        );

        $this->assertSame(
            'https://accounts.google.com',
            $response->getTargetUrl(),
        );
    }

    public function test_callback_redirects_to_login_when_google_authentication_fails(): void
    {
        $driver = Mockery::mock();

        $driver->shouldReceive('user')
            ->once()
            ->andThrow(new \Exception('OAuth failed'));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->once()
            ->andReturn($driver);

        $response = app(GoogleAuthController::class)->callback();

        $this->assertSame(route('login'), $response->getTargetUrl());
        $this->assertSame(
            'Gagal login dengan Google.',
            session('error'),
        );
    }

    public function test_callback_registers_a_new_user_and_verifies_email(): void
    {
        $this->prepareSession();

        $this->mockGoogleUser(
            'new-google@example.com',
            'New Google User',
        );

        $response = app(GoogleAuthController::class)->callback();

        $user = User::where(
            'email',
            'new-google@example.com',
        )->first();

        $this->assertNotNull($user);
        $this->assertSame('New Google User', $user->name);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Auth::check());
        $this->assertSame($user->id, Auth::id());

        $this->assertNotSame(
            'new-google@example.com',
            $user->password,
        );

        $this->assertTrue(
            Hash::needsRehash($user->password) === false,
        );

        $this->assertSame(
            route('dashboard'),
            $response->getTargetUrl(),
        );
    }

    public function test_callback_verifies_an_existing_unverified_user(): void
    {
        $this->prepareSession();

        $user = User::factory()->create([
            'email' => 'existing-google@example.com',
            'email_verified_at' => null,
            'role' => UserRole::RESEARCHER,
        ]);

        $this->mockGoogleUser(
            'existing-google@example.com',
            $user->name,
        );

        app(GoogleAuthController::class)->callback();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame($user->id, Auth::id());
    }

    public function test_callback_claims_an_anonymous_draft(): void
    {
        $this->prepareSession();

        $user = User::factory()->create([
            'role' => UserRole::RESEARCHER,
        ]);

        $draft = HireDraft::create([
            'data' => [],
            'step' => 1,
            'status' => 'draft',
        ]);

        session(['pending_draft_id' => $draft->id]);

        $this->mockGoogleUser($user->email, $user->name);

        $response = app(GoogleAuthController::class)->callback();

        $this->assertDatabaseHas('hire_drafts', [
            'id' => $draft->id,
            'user_id' => $user->id,
        ]);

        $this->assertFalse(
            session()->has('pending_draft_id'),
        );

        $this->assertSame(
            route('hire.apply', ['d' => $draft->id]),
            $response->getTargetUrl(),
        );

        $this->assertSame($user->id, Auth::id());
    }
}
