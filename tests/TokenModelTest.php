<?php

use Illuminate\Contracts\Support\DeferrableProvider;
use Wearepixel\QuickBooks\Client;
use Wearepixel\QuickBooks\Providers\ClientServiceProvider;
use Wearepixel\QuickBooks\Token;

describe('Token model structure', function () {
    it('uses the quickbooks_tokens table', function () {
        expect((new Token)->getTable())->toBe('quickbooks_tokens');
    });

    it('casts expiration fields to datetime', function () {
        $casts = (new Token)->getCasts();

        expect($casts)
            ->toHaveKey('access_token_expires_at', 'datetime')
            ->toHaveKey('refresh_token_expires_at', 'datetime');
    });

    it('has all expected fillable attributes', function () {
        $fillable = (new Token)->getFillable();

        expect($fillable)->toEqualCanonicalizing([
            'access_token',
            'access_token_expires_at',
            'realm_id',
            'refresh_token',
            'refresh_token_expires_at',
            'user_id',
        ]);
    });
});

describe('ClientServiceProvider structure', function () {
    it('implements DeferrableProvider', function () {
        expect(ClientServiceProvider::class)
            ->toImplement(DeferrableProvider::class);
    });

    it('declares Client in provides array', function () {
        $app = Mockery::mock(\Illuminate\Contracts\Foundation\Application::class);
        $app->shouldIgnoreMissing();

        $provider = new ClientServiceProvider($app);

        expect($provider->provides())->toBe([Client::class]);
    });
});
