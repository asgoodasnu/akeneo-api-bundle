<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AkeneoApiException extends \Exception
{
    protected function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public static function fromException(\Throwable $throwable): self
    {
        if ($throwable instanceof ClientExceptionInterface) {
            return self::create('ClientException', $throwable);
        }

        if ($throwable instanceof RedirectionExceptionInterface) {
            return self::create('RedirectionException', $throwable);
        }

        if ($throwable instanceof ServerExceptionInterface) {
            return self::create('Server error', $throwable);
        }

        if ($throwable instanceof TransportExceptionInterface) {
            return self::create('Transport exception', $throwable);
        }

        if ($throwable instanceof AkeneoApiException) {
            return $throwable;
        }

        return self::create('Global error', $throwable);
    }

    private static function create(string $message, \Throwable $throwable): self
    {
        return new self($message, $throwable);
    }

    public static function createFailed(\Throwable $throwable): self
    {
        return new AkeneoApiAuthorizationFailedException('Authorization failed!', $throwable);
    }

    public static function createProductNotFound(\Throwable $throwable): self
    {
        return new AkeneoApiProductNotFoundException('Product not found!', $throwable);
    }
}
