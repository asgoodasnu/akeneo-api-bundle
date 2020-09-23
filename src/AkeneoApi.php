<?php

namespace Asgoodasnew\AkeneoApiBundle;

interface AkeneoApi
{
    /**
     * @return array<string,mixed>
     */
    public function getProduct(string $identifier): array;
}
