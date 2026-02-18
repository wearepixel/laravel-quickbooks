<?php

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Session\Store;
use QuickBooksOnline\API\DataService\DataService;
use Wearepixel\QuickBooks\Client as QuickBooks;
use Wearepixel\QuickBooks\Http\Controllers\Controller;

beforeEach(function () {
    $this->data_service_mock = Mockery::mock(DataService::class);
    $this->quickbooks_mock = Mockery::mock(QuickBooks::class);
    $this->redirector_mock = Mockery::mock(Redirector::class);
    $this->request_mock = Mockery::mock(Request::class);
    $this->session_mock = Mockery::mock(Store::class);
    $this->url_generator_mock = Mockery::mock(UrlGenerator::class);
    $this->view_factory_mock = Mockery::mock(ViewFactory::class);
    $this->view_mock = Mockery::mock(View::class);

    $this->controller = new Controller;
});

it('can be constructed', function () {
    expect($this->controller)->toBeInstanceOf(Controller::class);
});

it('shows view to disconnect if account linked', function () {
    $this->data_service_mock
        ->shouldReceive('getCompanyInfo')
        ->once()
        ->withNoArgs()
        ->andReturn(['name' => 'Company']);

    $this->quickbooks_mock
        ->shouldReceive('hasValidRefreshToken')
        ->once()
        ->withNoArgs()
        ->andReturnTrue();

    $this->quickbooks_mock
        ->shouldReceive('getDataService')
        ->once()
        ->withNoArgs()
        ->andReturn($this->data_service_mock);

    $this->view_factory_mock
        ->shouldReceive('make')
        ->once()
        ->with('quickbooks::disconnect')
        ->andReturn($this->view_mock);

    $this->view_mock
        ->shouldReceive('with')
        ->once()
        ->withArgs(['company', ['name' => 'Company']])
        ->andReturnSelf();

    $this->controller->connect($this->quickbooks_mock, $this->view_factory_mock);
});

it('shows view to connect if account not linked', function () {
    $this->quickbooks_mock
        ->shouldReceive('hasValidRefreshToken')
        ->once()
        ->withNoArgs()
        ->andReturnFalse();

    $this->quickbooks_mock
        ->shouldReceive('authorizationUri')
        ->once()
        ->withNoArgs()
        ->andReturn('http://uri');

    $this->view_factory_mock
        ->shouldReceive('make')
        ->once()
        ->with('quickbooks::connect')
        ->andReturn($this->view_mock);

    $this->view_mock
        ->shouldReceive('with')
        ->once()
        ->withArgs(['authorization_uri', 'http://uri'])
        ->andReturnSelf();

    $this->controller->connect($this->quickbooks_mock, $this->view_factory_mock);
});

it('disconnects from quickbooks when requested', function () {
    $this->request_mock
        ->shouldReceive('session')
        ->once()
        ->andReturn($this->session_mock);

    $this->session_mock
        ->shouldReceive('flash')
        ->once()
        ->withAnyArgs();

    $this->redirector_mock
        ->shouldReceive('back')
        ->once()
        ->andReturn(new RedirectResponse('/test', 302));

    $this->quickbooks_mock
        ->shouldReceive('deleteToken')
        ->once()
        ->withNoArgs();

    $result = $this->controller->disconnect(
        $this->redirector_mock,
        $this->request_mock,
        $this->quickbooks_mock,
    );

    expect($result)->toBeInstanceOf(RedirectResponse::class);
});

it('finishes connecting to quickbooks when given a valid token', function () {
    $realmId = random_int(1, 9999);

    $this->quickbooks_mock
        ->shouldReceive('exchangeCodeForToken')
        ->once()
        ->withArgs(['code', $realmId]);

    $this->request_mock
        ->shouldReceive('get')
        ->once()
        ->withArgs(['code'])
        ->andReturn('code');

    $this->request_mock
        ->shouldReceive('get')
        ->once()
        ->withArgs(['realmId'])
        ->andReturn($realmId);

    $this->request_mock
        ->shouldReceive('session')
        ->once()
        ->andReturn($this->session_mock);

    $this->session_mock
        ->shouldReceive('flash')
        ->once()
        ->withAnyArgs();

    $this->redirector_mock
        ->shouldReceive('intended')
        ->once()
        ->withAnyArgs()
        ->andReturn(new RedirectResponse('/test', 302));

    $this->url_generator_mock
        ->shouldReceive('route')
        ->withArgs(['quickbooks.connect'])
        ->once();

    $result = $this->controller->token(
        $this->redirector_mock,
        $this->request_mock,
        $this->quickbooks_mock,
        $this->url_generator_mock,
    );

    expect($result)->toBeInstanceOf(RedirectResponse::class);
});
