<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle;

use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CachedSymfonyHttpClientAkeneoApi implements AkeneoApi
{
    const A_HOUR = 3600;
    const CACHE_KEY_CATEGORIES = 'akeneo-api-bundle-categories';

    private AkeneoApi $decorated;
    private CacheItemPoolInterface $cache;

    public function __construct(AkeneoApi $decorated, CacheItemPoolInterface $cache)
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
    }

    /**
     * @return array<mixed>
     *
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     * @throws InvalidArgumentException
     */
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
     * @param array<mixed>|CategoryItem $productArray
     */
    private function saveToCache(CacheItemInterface $cacheItem, $productArray): void
    {
        $cacheItem->expiresAfter(self::A_HOUR);
        $cacheItem->set($productArray);
        $this->cache->save($cacheItem);
    }

    /**
     * @return array<mixed>
     *
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     */
    private function getDecoratedResult(string $identifier): array
    {
        return $this->decorated->getProduct($identifier);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createCacheItem(string $key): CacheItemInterface
    {
        return $this->cache->getItem($key);
    }

    /**
     * @throws AkeneoApiException
     * @throws InvalidArgumentException
     */
    public function getCategories(string $rootCode): CategoryItem
    {
        $cacheItem = $this->createCacheItem(self::CACHE_KEY_CATEGORIES);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $item = $this->decorated->getCategories($rootCode);

        $this->saveToCache($cacheItem, $item);

        return $item;
    }
}
