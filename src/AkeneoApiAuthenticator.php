<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkeneoApiAuthenticator
{
    private string $baseUrl;
    private string $apiUser;
    private string $apiPassword;
    private string $authToken;
    private HttpClientInterface $client;
    private string $token = '';

    public function __construct(
        string $baseUrl,
        string $apiUser,
        string $apiPassword,
        string $authToken,
        HttpClientInterface $client
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiUser = $apiUser;
        $this->apiPassword = $apiPassword;
        $this->authToken = $authToken;
        $this->client = $client;
    }

    /**
     * @throws AkeneoApiException
     */
    public function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $body = json_encode([
            'grant_type' => 'password',
            'username' => $this->apiUser,
            'password' => $this->apiPassword,
        ]);

        try {
            $response = $this->client->request(
                'POST',
                $this->buildUrl('/api/oauth/v1/token'), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => sprintf('Basic %s', $this->authToken),
                    ],
                    'body' => $body,
                ]
            );

            if ($response->getContent()) {
                $object = json_decode($response->getContent(), true);
                $this->token = $object['access_token'];
            }
        } catch (ClientExceptionInterface $e) {
            throw AkeneoApiException::createFailed($e);
        } catch (\Exception $e) {
            throw AkeneoApiException::fromException($e);
        }

        return $this->token;
    }

    protected function buildUrl(string $endpoint): string
    {
        return sprintf('%s%s', $this->baseUrl, $endpoint);
    }
}
