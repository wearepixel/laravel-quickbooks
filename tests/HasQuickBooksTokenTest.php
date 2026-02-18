<?php

namespace Wearepixel\QuickBooks;

use PHPUnit\Framework\Attributes\Test;
use Wearepixel\QuickBooks\Stubs\User;

/**
 * Class HasQuickBooksTokenTest
 */
class HasQuickBooksTokenTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User;
    }

    #[Test]
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(User::class, $this->user);
    }

    #[Test]
    public function it_has_a_has_one_relationship_to_token()
    {
        $this->assertInstanceOf(Token::class, $this->user->quickBooksToken()->getModel());
    }
}
