<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiException;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiProductNotFoundException;
use Asgoodasnew\AkeneoApiBundle\CategoryTreeBuilder;
use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use Asgoodasnew\AkeneoApiBundle\SymfonyHttpClientAkeneoApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
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
            $this->akeneoApiAuthenticator,
            new CategoryTreeBuilder()
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

    /**
     * @param class-string<\Exception> $thrownException
     *
     * @dataProvider dataProviderExceptions
     *
     * @throws AkeneoApiProductNotFoundException
     * @throws AkeneoApiException
     */
    public function testGetProductExceptions(\Exception $exception, $thrownException): void
    {
        $sku = 'AN12345';

        $token = 'token';

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
            ->willThrowException($exception);

        $this->expectException($thrownException);

        $this->symfonyHttpClientAkeneoApi->getProduct($sku);
    }

    /**
     * @return array<mixed>
     */
    public function dataProviderExceptions(): array
    {
        return [
            [new ClientException(new MockResponse()), AkeneoApiProductNotFoundException::class],
            [new \Exception(), AkeneoApiException::class],
        ];
    }

    /**
     * @throws AkeneoApiException
     */
    public function testGetCategories(): void
    {
        $token = 'token';

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $response = $this->createMock(ResponseInterface::class);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'http://url/api/rest/v1/categories?limit=100', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer token',
                ],
            ])
            ->willReturn($response);

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn($this->getCategoriesResponseJson());

        $expectedItem = (new CategoryItem('master', 'Asgoodasnew'))
            ->setChildren([
                new CategoryItem('tablets', 'Tablets'),
            ]);

        self::assertEquals($expectedItem, $this->symfonyHttpClientAkeneoApi->getCategories('master'));
    }

    /**
     * @throws AkeneoApiException
     */
    public function testGetCategoriesException(): void
    {
        $token = 'token';

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'http://url/api/rest/v1/categories?limit=100', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer token',
                ],
            ])
            ->willThrowException(new \Exception());

        self::expectException(AkeneoApiException::class);

        $this->symfonyHttpClientAkeneoApi->getCategories('master');
    }

    /**
     * @throws AkeneoApiException
     */
    public function testTriggerUpdate(): void
    {
        $message = 'message';
        $sku = 'AN12345';
        $token = 'token';

        $response = $this->createMock(ResponseInterface::class);

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->client
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['GET', 'http://url/api/rest/v1/products/AN12345', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer token',
                    ],
                ]],
                ['PATCH', 'http://url/api/rest/v1/products/AN12345', self::anything()]
            )
            ->willReturn($response);

        $response->expects(self::exactly(2))
            ->method('getContent')
            ->willReturn('{"foo": "bar"}');

        $this->symfonyHttpClientAkeneoApi->triggerUpdate($sku, $message);
    }

    /**
     * @throws AkeneoApiException
     */
    public function testTriggerUpdateProductNotExist(): void
    {
        $message = 'message';
        $sku = 'AN12345';
        $token = 'token';

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with(
                'GET', 'http://url/api/rest/v1/products/AN12345', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer token',
                    ],
                ]
            )
            ->willThrowException(new ClientException(new MockResponse()));

        self::expectException(AkeneoApiProductNotFoundException::class);

        $this->symfonyHttpClientAkeneoApi->triggerUpdate($sku, $message);
    }

    /**
     * @throws AkeneoApiException
     */
    public function testTriggerUpdateExceptionAssertProductExist(): void
    {
        $message = 'message';
        $sku = 'AN12345';
        $token = 'token';

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with(
                'GET', 'http://url/api/rest/v1/products/AN12345', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer token',
                    ],
                ]
            )
            ->willThrowException(new \Exception());

        self::expectException(AkeneoApiException::class);

        $this->symfonyHttpClientAkeneoApi->triggerUpdate($sku, $message);
    }

    /**
     * @throws AkeneoApiException
     */
    public function testTriggerUpdateExceptionOnPatch(): void
    {
        $message = 'message';
        $sku = 'AN12345';
        $token = 'token';

        $response = $this->createMock(ResponseInterface::class);

        $this->akeneoApiAuthenticator
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $response->expects($this->once())
            ->method('getContent')
            ->willReturn('{"foo": "bar"}');

        $matcher = self::exactly(2);
        $this->client
            ->expects($matcher)
            ->method('request')
            ->willReturnCallback(function () use ($matcher, $response) {
                if (1 === $matcher->getInvocationCount()) {
                    return $response;
                }

                throw new \Exception();
            });

        self::expectException(AkeneoApiException::class);

        $this->symfonyHttpClientAkeneoApi->triggerUpdate($sku, $message);
    }

    private function getCategoriesResponseJson(): string
    {
        return <<<EOT
{
  "current_page": 1,
  "_embedded": {
    "items": [
      {
        "code": "master",
        "parent": null,
        "labels": {
          "de_DE": "Asgoodasnew"
        }
      },
      {
        "code": "tablets",
        "parent": "master",
        "labels": {
          "de_DE": "Tablets"
        }
      }
    ]
  }
}
EOT;
    }
}
