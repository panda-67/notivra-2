<?php

namespace Tests\Feature\Middleware;

use App\Enums\UserRole;
use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $request = Request::create('/admin', 'GET');

        $response = app(RoleMiddleware::class)->handle(
            $request,
            fn() => response('OK'),
            'super_admin',
            'admin_staff',
        );

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('login'), $response->getTargetUrl());
    }

    public function test_authorized_role_can_continue_to_next_handler(): void
    {
        $user = new User();
        $user->role = UserRole::SUPER_ADMIN;

        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn() => $user);

        $response = app(RoleMiddleware::class)->handle(
            $request,
            fn() => response('OK', 200),
            'super_admin',
            'admin_staff',
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    public function test_unauthorized_role_receives_forbidden_response(): void
    {
        $user = new User();
        $user->role = UserRole::RESEARCHER;

        $request = Request::create('/admin', 'GET');
        $request->setUserResolver(fn() => $user);

        try {
            app(RoleMiddleware::class)->handle(
                $request,
                fn() => response('OK'),
                'super_admin',
                'admin_staff',
            );

            $this->fail('Expected a 403 HTTP exception.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
            $this->assertSame(
                'Anda tidak memiliki hak akses untuk halaman ini.',
                $exception->getMessage(),
            );
        }
    }

    public function test_each_configured_role_is_authorized(): void
    {
        foreach ([UserRole::SUPER_ADMIN, UserRole::ADMIN_STAFF] as $role) {
            $user = new User();
            $user->role = $role;

            $request = Request::create('/admin', 'GET');
            $request->setUserResolver(fn() => $user);

            $response = app(RoleMiddleware::class)->handle(
                $request,
                fn() => response('OK', 200),
                'super_admin',
                'admin_staff',
            );

            $this->assertSame(
                200,
                $response->getStatusCode(),
                "Role {$role->value} seharusnya diizinkan.",
            );
        }
    }
}
