<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CachedSymfonyHttpClientAkeneoApi implements AkeneoApi
{
    const A_HOUR = 3600;

    private AkeneoApi $decorated;
    private CacheItemPoolInterface $cache;

    public function __construct(AkeneoApi $decorated, CacheItemPoolInterface $cache)
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    public function getProduct(string $identifier): array
    {
        $cacheItem = $this->createCacheItem($identifier);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $productArray = $this->getDecoratedResult($identifier);

        $this->saveToCache($cacheItem, $productArray);

        return $productArray;
    }

    public function triggerUpdate(string $identifier, ?string $message = null): void
    {
        $this->decorated->triggerUpdate($identifier);
    }

    /**
     * @param array<mixed> $productArray
     */
    private function saveToCache(CacheItemInterface $cacheItem, array $productArray): void
    {
        $cacheItem->expiresAfter(self::A_HOUR);
        $cacheItem->set($productArray);
        $this->cache->save($cacheItem);
    }

    /**
     * @return array<mixed>
     */
    private function getDecoratedResult(string $identifier): array
    {
        return $this->decorated->getProduct($identifier);
    }

    private function createCacheItem(string $identifier): CacheItemInterface
    {
        return $this->cache->getItem($identifier);
    }

    public function getCategories(string $rootCode): CategoryItem
    {
        return $this->decorated->getCategories($rootCode);
    }
}
