<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\CheckRole;
use App\Models\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CheckRoleMiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    public function test_allows_user_with_matching_role(): void
    {
        $user = $this->makeUser(['role' => UserRole::EMPLOYEE->value]);
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckRole();
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), UserRole::EMPLOYEE->value);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_blocks_user_with_non_matching_role(): void
    {
        $user = $this->makeUser(['role' => UserRole::CANDIDATE->value]);
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckRole();
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), UserRole::HR_ADMIN->value);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertStringContainsString('Forbidden', $response->getContent());
    }

    public function test_allows_user_with_one_of_multiple_roles(): void
    {
        $user = $this->makeUser(['role' => UserRole::EMPLOYEE->value]);
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => $user);

        $middleware = new CheckRole();
        $response = $middleware->handle(
            $request,
            fn () => response()->json(['ok' => true]),
            UserRole::CANDIDATE->value,
            UserRole::EMPLOYEE->value,
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_returns_401_for_unauthenticated_user(): void
    {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(fn () => null);

        $middleware = new CheckRole();
        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), UserRole::HR_ADMIN->value);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertStringContainsString('Unauthorized', $response->getContent());
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone_number' => '0100000000',
            'password' => Hash::make('Password123!'),
            'role' => UserRole::CANDIDATE->value,
        ], $overrides));
    }
}
