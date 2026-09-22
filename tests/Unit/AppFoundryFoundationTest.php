<?php

declare(strict_types=1);

namespace Indiyoin\Tests\Unit;

use App\Core\ConfigValidator;
use App\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class AppFoundryFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        putenv('APP_ENV=testing');
        putenv('DB_DRIVER=sqlite');
        putenv('APP_SECURE_COOKIES=false');
    }

    public function test_csrf_token_is_created_and_validated(): void
    {
        $token = Csrf::token();
        self::assertTrue(Csrf::validate($token));
        self::assertFalse(Csrf::validate('invalid'));
    }

    public function test_testing_configuration_is_accepted(): void
    {
        self::assertSame([], ConfigValidator::problems());
    }
}
