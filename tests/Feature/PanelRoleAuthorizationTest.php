<?php

declare(strict_types=1);

use AIArmada\FilamentPermissions\Http\Middleware\AuthorizePanelRoles;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    config(['filament-permissions.features.panel_role_authorization' => true]);
    config(['filament-permissions.panel_roles' => [
        'admin' => ['Super Admin', 'Admin'],
        'member' => ['Super Admin', 'Member'],
    ]]);
    config(['filament-permissions.super_admin_role' => 'Super Admin']);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('allows Super Admin to access admin panel', function () {
    $user = User::factory()->create();
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user->assignRole($superAdminRole);

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});

it('allows Super Admin to access member panel', function () {
    $user = User::factory()->create();
    $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user->assignRole($superAdminRole);

    // Set current panel to member
    Filament::setCurrentPanel(Filament::getPanel('member'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});

it('allows Admin role to access admin panel', function () {
    $user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $user->assignRole($adminRole);

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});

it('denies Member role access to admin panel', function () {
    $user = User::factory()->create();
    $memberRole = Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
    $user->assignRole($memberRole);

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();

    expect(fn () => $middleware->handle($request, fn ($req) => new Response('OK', 200)))
        ->toThrow(Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
});

it('allows Member role to access member panel', function () {
    $user = User::factory()->create();
    $memberRole = Role::firstOrCreate(['name' => 'Member', 'guard_name' => 'web']);
    $user->assignRole($memberRole);

    // Set current panel to member
    Filament::setCurrentPanel(Filament::getPanel('member'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});

it('denies Admin role access to member panel', function () {
    $user = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $user->assignRole($adminRole);

    // Set current panel to member
    Filament::setCurrentPanel(Filament::getPanel('member'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();

    expect(fn () => $middleware->handle($request, fn ($req) => new Response('OK', 200)))
        ->toThrow(Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
});

it('denies access when user has no roles', function () {
    $user = User::factory()->create();

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();

    expect(fn () => $middleware->handle($request, fn ($req) => new Response('OK', 200)))
        ->toThrow(Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class);
});

it('bypasses panel role check when feature is disabled', function () {
    config(['filament-permissions.features.panel_role_authorization' => false]);

    $user = User::factory()->create();

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});

it('allows access when panel has no configured roles', function () {
    config(['filament-permissions.panel_roles' => []]);

    $user = User::factory()->create();

    // Set current panel to admin
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new AuthorizePanelRoles();
    $response = $middleware->handle($request, fn ($req) => new Response('OK', 200));

    expect($response->getStatusCode())->toBe(200);
});
