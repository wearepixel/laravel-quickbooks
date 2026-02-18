<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Wearepixel\QuickBooks\Stubs\TokenStub;
use Wearepixel\QuickBooks\Stubs\User;
use Wearepixel\QuickBooks\Token;

beforeEach(function () {
    $this->token = new TokenStub;
});

it('can be constructed', function () {
    expect($this->token)->toBeInstanceOf(Token::class);
});

it('has accessor for valid access token', function () {
    expect($this->token->getHasValidAccessTokenAttribute())->not->toBeNull();
});

it('has accessor for valid refresh token', function () {
    expect($this->token->getHasValidRefreshTokenAttribute())->not->toBeNull();
});

it('knows that the access token expires at has to be valid', function () {
    expect($this->token->getHasValidAccessTokenAttribute())->toBeFalse();
});

it('knows that the refresh token expires at has to be valid', function () {
    expect($this->token->getHasValidRefreshTokenAttribute())->toBeFalse();
});

it('knows if access token expires at is less than now it is not expired', function () {
    Carbon::setTestNow(Carbon::now());

    $this->token->access_token_expires_at = Carbon::now()->addSecond();

    expect($this->token->getHasValidAccessTokenAttribute())->toBeTrue();
});

it('knows if refresh token expires at is less than now it is not expired', function () {
    Carbon::setTestNow(Carbon::now());

    $this->token->refresh_token_expires_at = Carbon::now()->addSecond();

    expect($this->token->getHasValidRefreshTokenAttribute())->toBeTrue();
});

it('knows if access token expires at is greater than or equal to now it is expired', function () {
    Carbon::setTestNow(Carbon::now());

    $this->token->access_token_expires_at = Carbon::now();
    expect($this->token->getHasValidAccessTokenAttribute())->toBeFalse();

    $this->token->access_token_expires_at->subSecond();
    expect($this->token->getHasValidAccessTokenAttribute())->toBeFalse();
});

it('knows if refresh token expires at is greater than or equal to now it is expired', function () {
    Carbon::setTestNow(Carbon::now());

    $this->token->refresh_token_expires_at = Carbon::now();
    expect($this->token->getHasValidRefreshTokenAttribute())->toBeFalse();

    $this->token->refresh_token_expires_at->subSecond();
    expect($this->token->getHasValidRefreshTokenAttribute())->toBeFalse();
});

it('stores the oauth token parts in expected properties', function () {
    $oauth_token_mock = Mockery::mock(QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken::class);

    $oauth_token_mock->shouldReceive('getAccessToken')->once()->withNoArgs()->andReturn('access_token');
    $oauth_token_mock->shouldReceive('getAccessTokenExpiresAt')->once()->withNoArgs()->andReturn('2025-06-15 12:00:00');
    $oauth_token_mock->shouldReceive('getRealmID')->once()->withNoArgs()->andReturn('realm_id');
    $oauth_token_mock->shouldReceive('getRefreshToken')->once()->withNoArgs()->andReturn('refresh_token');
    $oauth_token_mock->shouldReceive('getRefreshTokenExpiresAt')->once()->withNoArgs()->andReturn('2025-09-24 12:00:00');

    $result = $this->token->parseOauthToken($oauth_token_mock);

    expect($result)->toBeInstanceOf(Token::class)
        ->and($this->token->access_token)->toBe('access_token')
        ->and($this->token->realm_id)->toBe('realm_id')
        ->and($this->token->refresh_token)->toBe('refresh_token')
        ->and($this->token->access_token_expires_at)->toBeInstanceOf(Carbon::class)
        ->and($this->token->refresh_token_expires_at)->toBeInstanceOf(Carbon::class);
});

it('allows itself to be deleted and returns new token', function () {
    $this->token->user = Mockery::mock(User::class);

    $has_one_mock = Mockery::mock(HasOne::class);
    $token_mock = Mockery::mock(Token::class);

    $has_one_mock->shouldReceive('make')->once()->withNoArgs()->andReturn($token_mock);

    $this->token->user
        ->shouldReceive('quickBooksToken')
        ->once()
        ->withNoArgs()
        ->andReturn($has_one_mock);

    $this->token->user->id = 1;

    expect($this->token->remove())->toBe($token_mock);
});

it('gets related user model from configuration', function () {
    expect($this->token->user()->getModel())->toBeInstanceOf(User::class);
});
