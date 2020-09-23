<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyHttpClientAkeneoApi implements AkeneoApi
{
    private string $baseUrl;

    protected ?string $token = null;

    private HttpClientInterface $client;
    private AkeneoApiAuthenticator $akeneoApiAuthenticator;

    public function __construct(string $baseUrl,
                                HttpClientInterface $client,
                                AkeneoApiAuthenticator $akeneoApiAuthenticator)
    {
        $this->baseUrl = $baseUrl;
        $this->client = $client;
        $this->akeneoApiAuthenticator = $akeneoApiAuthenticator;
    }

    /**
     * @return array<string,mixed>
     */
    public function getProduct(string $identifier): array
    {
        $url = $this->buildUrl(sprintf('/api/rest/v1/products/%s', $identifier));

        $response = $this->client->request('GET', $url, $this->getDefaultHeaders());

        return json_decode($response->getContent(), true);
    }

    /**
     * @return array<string,mixed>
     */
    private function getDefaultHeaders(): array
    {
        $this->token = $this->token ?? $this->akeneoApiAuthenticator->getToken();

        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->token),
            ],
        ];
    }

    private function buildUrl(string $endpoint): string
    {
        return sprintf('%s%s', $this->baseUrl, $endpoint);
    }
}
