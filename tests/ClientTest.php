<?php

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Exception\ServiceException;
use Wearepixel\QuickBooks\Client;
use Wearepixel\QuickBooks\Token;

beforeEach(function () {
    $this->configs = [
        'data_service' => [
            'auth_mode' => 'oauth2',
            'base_url' => 'Development',
            'client_id' => 'QUICKBOOKS_CLIENT_ID',
            'client_secret' => 'QUICKBOOKS_CLIENT_SECRET',
            'scope' => 'com.intuit.quickbooks.accounting',
        ],
    ];

    $this->token_mock = Mockery::mock(Token::class);
    $this->client = new Client($this->configs, $this->token_mock);
});

it('can be constructed', function () {
    expect($this->client)->toBeInstanceOf(Client::class);
});

it('returns a data service configured to request oauth token when token is empty', function () {
    $this->token_mock
        ->shouldReceive('getAttribute')
        ->twice()
        ->with('hasValidAccessToken')
        ->andReturnFalse();

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('hasValidRefreshToken')
        ->andReturnFalse();

    expect(
        $this->client
            ->getDataService()
            ->getServiceContext()
            ->IppConfiguration->Security->isAccessTokenSet(),
    )->toBeFalse();
});

it('caches the data service once it is made', function () {
    $this->token_mock
        ->shouldReceive('getAttribute')
        ->twice()
        ->with('hasValidAccessToken')
        ->andReturnFalse();

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('hasValidRefreshToken')
        ->andReturnFalse();

    expect($this->client->getDataService())->toBeInstanceOf(DataService::class);
});

it('returns a data service with oauth token when valid access token exists', function () {
    $this->token_mock
        ->shouldReceive('getAttribute')
        ->twice()
        ->with('hasValidAccessToken')
        ->andReturnTrue();

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('access_token')
        ->andReturn('access_token');

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('realm_id')
        ->andReturn('realm_id');

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('refresh_token')
        ->andReturn('refresh_token');

    expect(
        $this->client
            ->getDataService()
            ->getServiceContext()
            ->IppConfiguration->Security->isAccessTokenSet(),
    )->toBeTrue();
});

it('returns a data service with refreshed token when access token expired but refresh token valid', function () {
    $this->token_mock
        ->shouldReceive('getAttribute')
        ->twice()
        ->with('hasValidAccessToken')
        ->andReturnFalse();

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('hasValidRefreshToken')
        ->andReturnTrue();

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->never()
        ->with('access_token');

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('realm_id')
        ->andReturn('realm_id');

    $this->token_mock
        ->shouldReceive('getAttribute')
        ->once()
        ->with('refresh_token')
        ->andReturn('refresh_token');

    $this->client->getDataService();
})->throws(ServiceException::class);

it('returns a report service using the data service')
    ->skip('Once we figure out how to test around the static DataService::Configure');

it('has logging off by default')
    ->skip('Have to figure out how to test this with the new code in 5.x');

it('allows logging turned on and pointed to expected file')
    ->skip('Have to figure out how to test this with the new code in 5.x');

it('returns self after deleting token', function () {
    $this->token_mock
        ->shouldReceive('remove')
        ->once()
        ->withNoArgs()
        ->andReturnSelf();

    expect($this->client->deleteToken())->toBeInstanceOf(Client::class);
});
