<?php

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthorizationFailedException;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiException;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiProductNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\MockResponse;

class AkeneoApiExceptionTest extends TestCase
{
    public function testCreateFailed(): void
    {
        $exception = AkeneoApiException::createFailed(new \Exception());

        self::assertInstanceOf(AkeneoApiAuthorizationFailedException::class, $exception);
    }

    public function testCreateProductNotFound(): void
    {
        $exception = AkeneoApiException::createProductNotFound(new \Exception());

        self::assertInstanceOf(AkeneoApiProductNotFoundException::class, $exception);
    }

    public function testFromExceptionClientException(): void
    {
        $response = new MockResponse();

        $exception = AkeneoApiException::fromException(new ClientException($response));

        self::assertInstanceOf(AkeneoApiException::class, $exception);
        self::assertSame('ClientException', $exception->getMessage());
    }

    public function testFromExceptionRedirectionException(): void
    {
        $response = new MockResponse();

        $exception = AkeneoApiException::fromException(new RedirectionException($response));

        self::assertInstanceOf(AkeneoApiException::class, $exception);
        self::assertSame('RedirectionException', $exception->getMessage());
    }

    public function testFromExceptionServerErrorException(): void
    {
        $response = new MockResponse();

        $exception = AkeneoApiException::fromException(new ServerException($response));

        self::assertInstanceOf(AkeneoApiException::class, $exception);
        self::assertSame('Server error', $exception->getMessage());
    }

    public function testFromExceptionTransportException(): void
    {
        $exception = AkeneoApiException::fromException(new TransportException());

        self::assertInstanceOf(AkeneoApiException::class, $exception);
        self::assertSame('Transport exception', $exception->getMessage());
    }

    public function testFromExceptionAkeneoException(): void
    {
        $akeneoApiException = AkeneoApiException::fromException(new \Exception());

        $exception = AkeneoApiException::fromException($akeneoApiException);

        self::assertInstanceOf(AkeneoApiException::class, $akeneoApiException);
        self::assertInstanceOf(AkeneoApiException::class, $exception);
        self::assertSame('Global error', $akeneoApiException->getMessage());
        self::assertSame('Global error', $exception->getMessage());
    }
}
