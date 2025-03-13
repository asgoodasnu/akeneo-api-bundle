<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\Token;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AkeneoApiAuthenticator
{
    private string $baseUrl;
    private string $apiUser;
    private string $apiPassword;
    private string $authToken;
    private HttpClientInterface $client;
    private ?Token $token = null;

    public function __construct(
        string $baseUrl,
        string $apiUser,
        string $apiPassword,
        string $authToken,
        HttpClientInterface $client,
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
    public function getToken(): Token
    {
        if ($this->token && time() < $this->token->getExpiresOn()) {
            return $this->token;
        }

        try {
            // To offset network latency we set the time before the request
            $time = time();
            $response = $this->client->request(
                'POST',
                $this->buildUrl('/api/oauth/v1/token'), [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => \sprintf('Basic %s', $this->authToken),
                    ],
                    'body' => $this->getBody(),
                ]
            );

            $this->token = $this->createToken($response->toArray(), $time);
        } catch (ClientExceptionInterface $e) {
            throw AkeneoApiException::createFailed($e);
        } catch (DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            throw AkeneoApiException::fromException($e);
        }

        return $this->token;
    }

    private function buildUrl(string $endpoint): string
    {
        return \sprintf('%s%s', $this->baseUrl, $endpoint);
    }

    /**
     * @throws \JsonException
     */
    private function getBody(): string
    {
        if ($this->token && time() >= $this->token->getExpiresOn()) {
            return json_encode([
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->token->getRefreshToken(),
            ], JSON_THROW_ON_ERROR);
        } else {
            return json_encode([
                'grant_type' => 'password',
                'username' => $this->apiUser,
                'password' => $this->apiPassword,
            ], JSON_THROW_ON_ERROR);
        }
    }

    /**
     * @param array<string,mixed> $tokenData
     */
    private function createToken(array $tokenData, int $time): Token
    {
        return new Token(
            $tokenData['access_token'],
            $time + $tokenData['expires_in'], // TODO: calculate expiry before sending
            $tokenData['token_type'],
            $tokenData['scope'],
            $tokenData['refresh_token'],
        );
    }
}
