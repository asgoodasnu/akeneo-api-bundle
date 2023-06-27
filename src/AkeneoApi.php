<?php

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;

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
    public function getCategories(string $rootCode): CategoryItem;

    /**
     * @throws AkeneoApiException
     */
    public function triggerUpdate(string $identifier, string $message = null): void;
}
