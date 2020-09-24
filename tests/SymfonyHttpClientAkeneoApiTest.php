<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use Asgoodasnew\AkeneoApiBundle\SymfonyHttpClientAkeneoApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SymfonyHttpClientAkeneoApiTest extends TestCase
{
    protected SymfonyHttpClientAkeneoApi $symfonyHttpClientAkeneoApi;

    protected string $baseUrl;

    /**
     * @var MockObject
     */
    protected $client;

    /**
     * @var MockObject
     */
    protected $akeneoApiAuthenticator;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://url';
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->akeneoApiAuthenticator = $this->createMock(AkeneoApiAuthenticator::class);

        $this->symfonyHttpClientAkeneoApi = new SymfonyHttpClientAkeneoApi(
            $this->baseUrl,
            $this->client,
            $this->akeneoApiAuthenticator
        );
    }

    public function testGetProduct(): void
    {
        $sku = 'AN12345';

        $token = 'token';

        $response = $this->createMock(ResponseInterface::class);

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'http://url/api/rest/v1/products/AN12345', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer token',
                ],
            ])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('{"foo": "bar"}');

        $productArray = ['foo' => 'bar'];

        $this->assertSame($productArray, $this->symfonyHttpClientAkeneoApi->getProduct($sku));
    }
}
