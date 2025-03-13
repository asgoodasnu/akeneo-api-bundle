<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Model;

class Token
{
    public function __construct(
        private string $accessToken,
        private int $expiresAt,
        private string $tokenType,
        private string $scope,
        private string $refreshToken,
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
