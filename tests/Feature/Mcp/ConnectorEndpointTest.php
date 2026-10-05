<?php

use App\Mcp\Support\ConnectorTokens;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

/**
 * @param  array<string, mixed>  $params
 * @param  array<string, string>  $headers
 */
function mcpCall(string $method, array $params = [], array $headers = []): TestResponse
{
    return test()->withHeaders(['Accept' => 'application/json, text/event-stream', ...$headers])
        ->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => $method, 'params' => $params]);
}

function mcpInitialize(array $headers = []): TestResponse
{
    return mcpCall('initialize', [
        'protocolVersion' => '2025-06-18',
        'capabilities' => (object) [],
        'clientInfo' => ['name' => 'pest', 'version' => '1.0'],
    ], $headers);
}

beforeEach(function () {
    usePassportKeys();
});

test('without a token the endpoint points clients to the OAuth metadata', function () {
    mcpInitialize()
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer realm="mcp", resource_metadata="'.url('/.well-known/oauth-protected-resource/mcp').'"');

    $this->getJson('/.well-known/oauth-protected-resource/mcp')
        ->assertOk()
        ->assertJson([
            'resource' => url('/mcp'),
            'authorization_servers' => [url('/')],
            'scopes_supported' => ['mcp:use'],
        ]);

    $this->getJson('/.well-known/oauth-authorization-server')
        ->assertOk()
        ->assertJson([
            'authorization_endpoint' => url('/oauth/authorize'),
            'token_endpoint' => url('/oauth/token'),
            'registration_endpoint' => url('/oauth/register'),
            'code_challenge_methods_supported' => ['S256'],
        ]);
});

test('a token with the mcp scope reaches the server', function () {
    Passport::actingAs(User::factory()->create(), ['mcp:use']);

    mcpInitialize()
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'Portfolio')
        ->assertJsonPath('result.capabilities.tools', fn (mixed $tools): bool => $tools !== null);

    $tools = collect(mcpCall('tools/list')->assertOk()->json('result.tools'))->pluck('name');

    expect($tools)->toContain('create_project', 'set_project_cover', 'request_image_upload', 'save_skill', 'create_company', 'create_experience', 'update_profile')
        ->and($tools)->toHaveCount(28);

    mcpCall('tools/call', ['name' => 'get_portfolio_overview', 'arguments' => (object) []])
        ->assertOk()
        ->assertJsonPath('result.isError', false);
});

test('a token without the mcp scope is refused', function () {
    Passport::actingAs(User::factory()->create(), ['other']);

    mcpInitialize()->assertForbidden();
});

test('only the site owner may use the connector in production', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('portfolio.admin_emails', ['owner@example.com']);

    Passport::actingAs(User::factory()->create(['email' => 'someone@example.com']), ['mcp:use']);
    mcpInitialize()->assertForbidden();

    Passport::actingAs(User::factory()->create(['email' => 'Owner@Example.com']), ['mcp:use']);
    mcpInitialize()->assertOk();
});

test('the connector can be switched off', function () {
    config()->set('portfolio.mcp.enabled', false);
    Passport::actingAs(User::factory()->create(), ['mcp:use']);

    mcpInitialize()->assertNotFound();
});

test('clients can only register trusted redirect uris', function () {
    $this->postJson('/oauth/register', [
        'client_name' => 'Claude',
        'redirect_uris' => ['https://claude.ai/api/mcp/auth_callback'],
    ])->assertCreated()->assertJsonPath('scope', 'mcp:use');

    $this->postJson('/oauth/register', [
        'client_name' => 'Claude Code',
        'redirect_uris' => ['http://localhost:53682/callback'],
    ])->assertCreated();

    $this->postJson('/oauth/register', [
        'client_name' => 'Phisher',
        'redirect_uris' => ['https://evil.example.com/callback'],
    ])->assertStatus(400)->assertJsonPath('error', 'invalid_redirect_uri');

    expect(Client::query()->count())->toBe(2);
});

test('the full OAuth flow: register, sign in, approve, exchange the code, call the server', function () {
    $redirect = 'https://claude.ai/api/mcp/auth_callback';
    $clientId = $this->postJson('/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [$redirect]])->json('client_id');

    $verifier = Str::random(64);
    $authorize = '/oauth/authorize?'.http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirect,
        'response_type' => 'code',
        'scope' => 'mcp:use',
        'state' => 'state-123',
        'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
        'code_challenge_method' => 'S256',
    ]);

    // Guests sign in on the panel first.
    $this->get($authorize)->assertRedirect(route('filament.admin.auth.login'));

    $user = User::factory()->create(['email' => 'owner@example.com']);
    $this->actingAs($user);

    $this->get($authorize)
        ->assertOk()
        ->assertSee('Connect Claude to')
        ->assertSee('claude.ai')
        ->assertSee('owner@example.com');

    $approved = $this->post('/oauth/authorize', [
        'state' => '',
        'client_id' => $clientId,
        'auth_token' => session('authToken'),
    ]);

    $location = $approved->assertRedirect()->headers->get('Location');
    expect($location)->toStartWith($redirect);
    parse_str((string) parse_url((string) $location, PHP_URL_QUERY), $query);
    expect($query['state'])->toBe('state-123');

    $token = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'redirect_uri' => $redirect,
        'code_verifier' => $verifier,
        'code' => $query['code'],
    ])->assertOk()->json();

    expect($token)->toHaveKeys(['access_token', 'refresh_token', 'expires_in']);

    auth()->forgetGuards();

    mcpInitialize(['Authorization' => 'Bearer '.$token['access_token']])
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'Portfolio');
});

test('the consent screen refuses users who are not the owner', function () {
    app()->detectEnvironment(fn (): string => 'production');
    config()->set('portfolio.admin_emails', ['owner@example.com']);

    $redirect = 'https://claude.ai/api/mcp/auth_callback';
    $clientId = $this->postJson('/oauth/register', ['client_name' => 'Claude', 'redirect_uris' => [$redirect]])->json('client_id');

    $this->actingAs(User::factory()->create(['email' => 'intruder@example.com']))
        ->get('/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirect,
            'response_type' => 'code',
            'scope' => 'mcp:use',
            'code_challenge' => str_repeat('a', 43),
            'code_challenge_method' => 'S256',
        ]))
        ->assertForbidden();
});

test('a personal access token works as a bearer token', function () {
    $user = User::factory()->create();
    $result = ConnectorTokens::create($user, 'Claude Code');

    mcpInitialize(['Authorization' => "Bearer {$result->accessToken}"])->assertOk();

    auth()->forgetGuards();
    mcpInitialize(['Authorization' => 'Bearer not-a-token'])->assertUnauthorized();

    ConnectorTokens::revoke($result->token);
    auth()->forgetGuards();
    mcpInitialize(['Authorization' => "Bearer {$result->accessToken}"])->assertUnauthorized();
});
