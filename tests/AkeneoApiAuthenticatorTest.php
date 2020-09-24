<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
        parent::setUp();

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
    }
}
