<?php

namespace Asgoodasnew\AkeneoApiBundle;

interface AkeneoApi
{
    /**
     * @return array<string,mixed>
     *
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     */
    public function getProduct(string $identifier): array;

    /**
     * @throws AkeneoApiException
     */
    public function triggerUpdate(string $identifier, ?string $message = null): void;
}
