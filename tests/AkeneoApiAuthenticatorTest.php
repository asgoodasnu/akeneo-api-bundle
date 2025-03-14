<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiException;
use Asgoodasnew\AkeneoApiBundle\Model\Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AkeneoApiAuthenticatorTest extends TestCase
{
    /**
     * @var AkeneoApiAuthenticator
     */
    private $akeneoApiAuthenticator;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $apiUser;

    /**
     * @var string
     */
    private $apiPassword;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var MockObject
     */
    private $client;

    public function setUp(): void
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
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(
                [
                    'access_token' => 'access_token',
                    'expires_in' => 3600,
                    'token_type' => 'token_type',
                    'scope' => 'scope',
                    'refresh_token' => 'refresh_token',
                ]
            );

        $this->client->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'http://url/api/oauth/v1/token',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic token',
                    ],
                    'body' => '{"grant_type":"password","username":"user","password":"password"}',
                ]
            )
            ->willReturn($responseMock);

        $token = $this->akeneoApiAuthenticator->getToken();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('access_token', $token->getAccessToken());
        $this->assertEquals(time() + 3600, $token->getExpiresAt());

        // Check that second iteration outputs the same token
        $token = $this->akeneoApiAuthenticator->getToken();

        $this->assertEquals('access_token', $token->getAccessToken());
    }

    public function testGetTokenWithExpiredToken(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->exactly(2))
            ->method('toArray')
            ->willReturn(
                [
                    'access_token' => 'access_token',
                    'expires_in' => -1, // In production this would be the time() function that is smaller than the expiry time
                    'token_type' => 'token_type',
                    'refresh_token' => 'refresh_token',
                    'scope' => 'scope',
                ],
                [
                    'access_token' => 'access_token',
                    'expires_in' => 3600,
                    'token_type' => 'token_type',
                    'refresh_token' => 'refresh_token',
                    'scope' => 'scope',
                ]
            );

        $this->client
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    'POST',
                    'http://url/api/oauth/v1/token',
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Basic token',
                        ],
                        'body' => '{"grant_type":"password","username":"user","password":"password"}',
                    ],
                ],
                [
                    'POST',
                    'http://url/api/oauth/v1/token',
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Basic token',
                        ],
                        'body' => '{"grant_type":"refresh_token","refresh_token":"refresh_token"}',
                    ],
                ]
            )->willReturn($responseMock, $responseMock);

        $this->akeneoApiAuthenticator->getToken();

        $token = $this->akeneoApiAuthenticator->getToken();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertEquals('access_token', $token->getAccessToken());
        $this->assertEquals(time() + 3600, $token->getExpiresAt());
    }

    public function testGetTokenWillThrowClientException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects($this->exactly(3))
            ->method('getInfo')
            ->withConsecutive(['http_code'], ['url'], ['response_headers'])
            ->willReturn(404, 'http://api/oauth/v1/token', []);

        $response->expects($this->once())
            ->method('toArray')
            ->willThrowException(new ClientException($response));

        $this->client
            ->expects($this->exactly(1))
            ->method('request')
            ->willReturn($response);

        $this->expectException(AkeneoApiException::class);

        $this->akeneoApiAuthenticator->getToken();
    }

    public function testGetTokenWillThrowTransportException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('toArray')
            ->willThrowException(new TransportException('Transport exception'));

        $this->client
            ->expects($this->exactly(1))
            ->method('request')
            ->willReturn($response);

        $this->expectException(AkeneoApiException::class);
        $this->expectExceptionMessage('Transport exception');

        $this->akeneoApiAuthenticator->getToken();
    }
}
