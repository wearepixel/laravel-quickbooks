<?php

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Wearepixel\QuickBooks\Client as QuickBooks;
use Wearepixel\QuickBooks\Http\Middleware\Filter;

beforeEach(function () {
    $this->quickbooks_mock = Mockery::mock(QuickBooks::class);
    $this->redirector_mock = Mockery::mock(Redirector::class);
    $this->request_mock = Mockery::mock(Request::class);
    $this->session_mock = Mockery::mock(Session::class);
    $this->url_generator_mock = Mockery::mock(UrlGenerator::class);

    $this->filter = new Filter(
        $this->quickbooks_mock,
        $this->redirector_mock,
        $this->session_mock,
        $this->url_generator_mock,
    );
});

it('can be constructed', function () {
    expect($this->filter)->toBeInstanceOf(Filter::class);
});

it('passes the request to the next middleware if account linked to quickbooks', function () {
    $next_middleware = function ($request) {
        expect($request)->toBe($this->request_mock);
    };

    $this->quickbooks_mock
        ->shouldReceive('hasValidRefreshToken')
        ->once()
        ->withNoArgs()
        ->andReturnTrue();

    $this->filter->handle($this->request_mock, $next_middleware);
});

it('redirects to quickbooks connect route after setting intended session if account not linked', function () {
    $this->request_mock
        ->shouldReceive('path')
        ->once()
        ->withNoArgs()
        ->andReturn('path');

    $this->url_generator_mock
        ->shouldReceive('to')
        ->once()
        ->with('path')
        ->andReturn('http://to/path');

    $this->session_mock
        ->shouldReceive('put')
        ->once()
        ->withArgs(['url.intended', 'http://to/path'])
        ->andReturnNull();

    $this->redirector_mock
        ->shouldReceive('route')
        ->once()
        ->with('quickbooks.connect')
        ->andReturnSelf();

    $next_middleware = function ($request) {
        throw new RuntimeException('Next middleware should not be called');
    };

    $this->quickbooks_mock
        ->shouldReceive('hasValidRefreshToken')
        ->once()
        ->withNoArgs()
        ->andReturnFalse();

    $this->filter->handle($this->request_mock, $next_middleware);
});
