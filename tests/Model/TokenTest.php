<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests\Model;

use Asgoodasnew\AkeneoApiBundle\Model\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testGet(): void
    {
        $token = new Token(
            'accessToken',
            123456,
            'tokenType',
            'refreshToken',
            'scope'
        );

        self::assertSame('accessToken', $token->getAccessToken());
        self::assertSame(123456, $token->getExpiresAt());
        self::assertSame('tokenType', $token->getTokenType());
        self::assertSame('scope', $token->getScope());
        self::assertSame('refreshToken', $token->getRefreshToken());
    }
}
