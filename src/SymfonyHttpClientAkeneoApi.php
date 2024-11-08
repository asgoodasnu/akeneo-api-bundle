<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyHttpClientAkeneoApi implements AkeneoApi
{
    private string $baseUrl;

    protected ?string $token = null;

    private HttpClientInterface $client;
    private AkeneoApiAuthenticator $akeneoApiAuthenticator;
    private CategoryTreeBuilder $categoryTreeBuilder;

    public function __construct(
        string $baseUrl,
        HttpClientInterface $client,
        AkeneoApiAuthenticator $akeneoApiAuthenticator,
        CategoryTreeBuilder $categoryTreeBuilder,
    ) {
        $this->baseUrl = $baseUrl;
        $this->client = $client;
        $this->akeneoApiAuthenticator = $akeneoApiAuthenticator;
        $this->categoryTreeBuilder = $categoryTreeBuilder;
    }

    /**
     * @return array<string,mixed>
     *
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     */
    public function getProduct(string $identifier): array
    {
        $url = $this->buildUrl(\sprintf('/api/rest/v1/products/%s', $identifier));

        try {
            $response = $this->client->request(Request::METHOD_GET, $url, $this->getDefaultHeaders());
        } catch (ClientExceptionInterface $e) {
            throw AkeneoApiException::createProductNotFound($e);
        } catch (\Exception $e) {
            throw AkeneoApiException::fromException($e);
        }

        return json_decode($response->getContent(), true);
    }

    public function getCategories(string $rootCode): CategoryItem
    {
        $nextUrl = $this->buildUrl('/api/rest/v1/categories?limit=100');

        $items = [];

        while ($nextUrl) {
            try {
                $response = $this->client->request(Request::METHOD_GET, $nextUrl, $this->getDefaultHeaders());
            } catch (\Exception $e) {
                throw AkeneoApiException::fromException($e);
            }

            $json = json_decode($response->getContent(), true);

            $nextUrl = $json['_links']['next']['href'] ?? null;

            $newItems = $json['_embedded']['items'];

            $items = array_merge($items, $newItems);
        }

        return $this->categoryTreeBuilder->build($rootCode, $items);
    }

    /**
     * @throws AkeneoApiException
     */
    public function triggerUpdate(string $identifier, ?string $message = null): void
    {
        $this->assertProductExists($identifier);

        $options = $this->getDefaultHeaders();
        $options['body'] = $this->getTriggerUpdateBody($identifier, $message);

        try {
            $response = $this->client->request(
                Request::METHOD_PATCH,
                $this->buildUrl(\sprintf('/api/rest/v1/products/%s', $identifier)),
                $options
            );

            $response->getContent();
        } catch (\Exception $exception) {
            throw AkeneoApiException::fromException($exception);
        }
    }

    /**
     * @return array<string,mixed>
     *
     * @throws AkeneoApiException
     */
    private function getDefaultHeaders(): array
    {
        $this->token = $this->token ?? $this->akeneoApiAuthenticator->getToken();

        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => \sprintf('Bearer %s', $this->token),
            ],
        ];
    }

    private function buildUrl(string $endpoint): string
    {
        return \sprintf('%s%s', $this->baseUrl, $endpoint);
    }

    /**
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     */
    private function assertProductExists(string $identifier): void
    {
        try {
            $this->getProduct($identifier);
        } catch (\Exception $exception) {
            throw AkeneoApiException::fromException($exception);
        }
    }

    private function getTriggerUpdateBody(string $identifier, ?string $message): string
    {
        $value = \sprintf(
            '%s - %s',
            (new \DateTime())->format('Y-m-d H:i:s'),
            $message ?? 'update from AkeneoApiBundle'
        );

        return <<<EOBODY
{
  "values": {
    "agan_pattribut_check_field": [
      {
        "data": "$value",
        "locale": null,
        "scope": null
      }
    ]
  }
}
EOBODY;
    }
}
