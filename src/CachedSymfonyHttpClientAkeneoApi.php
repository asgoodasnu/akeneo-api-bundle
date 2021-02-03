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
        $cacheItem = $this->cache->getItem($identifier);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $productArray = $this->decorated->getProduct($identifier);

        $this->saveToCache($cacheItem, $productArray);

        return $productArray;
    }

    /**
     * @throws AkeneoApiException
     * @throws InvalidArgumentException
     */
    public function getCategories(string $rootCode): CategoryItem
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY_CATEGORIES);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $item = $this->decorated->getCategories($rootCode);

        $this->saveToCache($cacheItem, $item);

        return $item;
    }

    public function triggerUpdate(string $identifier, ?string $message = null): void
    {
        $this->decorated->triggerUpdate($identifier);
    }

    /**
     * @param array<mixed>|CategoryItem $value
     */
    private function saveToCache(CacheItemInterface $cacheItem, $value): void
    {
        $cacheItem->expiresAfter(self::A_HOUR);
        $cacheItem->set($value);
        $this->cache->save($cacheItem);
    }
}
