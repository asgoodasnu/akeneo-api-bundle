<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthorizationFailedException;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AkeneoApiAuthenticatorTest extends TestCase
{
    /**
     * @var AkeneoApiAuthenticator
     */
    protected $akeneoApiAuthenticator;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $apiUser;

    /**
     * @var string
     */
    protected $apiPassword;

    /**
     * @var string
     */
    protected $authToken;

    /**
     * @var MockObject
     */
    protected $client;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://url';
        $this->apiUser = 'user';
        $this->apiPassword = 'password';
        $this->authToken = 'token';
        $this->client = $this->createMock(HttpClientInterface::class);

        $this->akeneoApiAuthenticator = new AkeneoApiAuthenticator(
            $this->baseUrl,
            $this->apiUser,
            $this->apiPassword,
            $this->authToken,
            $this->client
        );
    }

    public function testGetToken(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://url/api/oauth/v1/token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic token',
                ],
                'body' => '{"grant_type":"password","username":"user","password":"password"}',
            ])
            ->willReturn($response);

        $response
            ->method('getContent')
            ->willReturn('{"access_token": "xyz"}');

        $this->assertSame('xyz', $this->akeneoApiAuthenticator->getToken());

        // check that getToken in second call is taken from stored value
        $this->assertSame('xyz', $this->akeneoApiAuthenticator->getToken());
    }

    public function testGetTokenClientException(): void
    {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://url/api/oauth/v1/token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic token',
                ],
                'body' => '{"grant_type":"password","username":"user","password":"password"}',
            ])
            ->willThrowException(new ClientException(new MockResponse()));

        self::expectException(AkeneoApiAuthorizationFailedException::class);

        $this->akeneoApiAuthenticator->getToken();
    }

    public function testGetTokenException(): void
    {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('POST', 'http://url/api/oauth/v1/token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic token',
                ],
                'body' => '{"grant_type":"password","username":"user","password":"password"}',
            ])
            ->willThrowException(new \Exception());

        self::expectException(AkeneoApiException::class);

        $this->akeneoApiAuthenticator->getToken();
    }
}
