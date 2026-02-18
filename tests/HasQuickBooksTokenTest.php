<?php

use Wearepixel\QuickBooks\Stubs\User;
use Wearepixel\QuickBooks\Token;

beforeEach(function () {
    $this->user = new User;
});

it('can be constructed', function () {
    expect($this->user)->toBeInstanceOf(User::class);
});

it('has a hasOne relationship to token', function () {
    expect($this->user->quickBooksToken()->getModel())->toBeInstanceOf(Token::class);
});
