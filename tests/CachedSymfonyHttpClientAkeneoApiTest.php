<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApi;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiException;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiProductNotFoundException;
use Asgoodasnew\AkeneoApiBundle\CachedSymfonyHttpClientAkeneoApi;
use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;

class CachedSymfonyHttpClientAkeneoApiTest extends TestCase
{
    protected CachedSymfonyHttpClientAkeneoApi $cachedSymfonyHttpClientAkeneoApi;

    /** @var MockObject */
    protected $decorated;

    /** @var MockObject */
    protected $cache;

    /** @var MockObject */
    private $cacheItem;

    /** @var MockObject */
    protected $logger;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(AkeneoApi::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(CacheItemInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->cachedSymfonyHttpClientAkeneoApi = new CachedSymfonyHttpClientAkeneoApi($this->decorated, $this->cache);
    }

    /**
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     * @throws InvalidArgumentException
     */
    public function testGetProductCached(): void
    {
        $sku = 'AN12345';

        $productArray = ['key' => 'value'];

        $this->assertCacheHit($sku, $productArray);

        $this->decorated
            ->expects(self::never())
            ->method('getProduct');

        $this->assertSame($productArray, $this->cachedSymfonyHttpClientAkeneoApi->getProduct($sku));
    }

    /**
     * @throws AkeneoApiException
     * @throws AkeneoApiProductNotFoundException
     * @throws InvalidArgumentException
     */
    public function testGetProductNotCached(): void
    {
        $sku = 'AN12345';

        $productArray = ['key' => 'value'];

        $this->assertCacheNotHitAndSaved($sku, $productArray);

        $this->decorated
            ->expects(self::once())
            ->method('getProduct')
            ->with($sku)
            ->willReturn($productArray);

        $this->assertSame($productArray, $this->cachedSymfonyHttpClientAkeneoApi->getProduct($sku));
    }

    public function testGetCategoriesCached(): void
    {
        $item = new CategoryItem('code', 'title');
        $cacheKey = 'akeneo-api-bundle-categories';

        $this->assertCacheHit($cacheKey, $item);

        $this->decorated
            ->expects($this->never())
            ->method('getCategories');

        $this->assertSame($item, $this->cachedSymfonyHttpClientAkeneoApi->getCategories('root'));
    }

    public function testGetCategoriesNotCached(): void
    {
        $item = new CategoryItem('code', 'title');
        $cacheKey = 'akeneo-api-bundle-categories';

        $this->assertCacheNotHitAndSaved($cacheKey, $item);

        $this->decorated
            ->expects($this->once())
            ->method('getCategories')
            ->with('root')
            ->willReturn($item);

        $this->assertSame($item, $this->cachedSymfonyHttpClientAkeneoApi->getCategories('root'));
    }

    /**
     * @throws AkeneoApiException
     */
    public function testTriggerUpdate(): void
    {
        $this->decorated
            ->expects($this->once())
            ->method('triggerUpdate')
            ->with('identifier');

        $this->cachedSymfonyHttpClientAkeneoApi->triggerUpdate('identifier');
    }

    /**
     * @param array<mixed>|object $value
     */
    private function assertCacheNotHitAndSaved(string $cacheKey, $value): void
    {
        $this->assertCacheGetItem($cacheKey);

        $this->assertCacheItemIsHit(false);

        $this->assertCacheItemSet($value);

        $this->assertCacheItemExpiresAfter();

        $this->assertCacheSave();
    }

    /**
     * @param array<mixed>|object $value
     */
    private function assertCacheHit(string $cacheKey, $value): void
    {
        $this->assertCacheGetItem($cacheKey);

        $this->assertCacheItemIsHit(true);

        $this->assertCacheItemGet($value);
    }

    private function assertCacheGetItem(string $cacheKey): void
    {
        $this->cache
            ->expects(self::once())
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($this->cacheItem);
    }

    private function assertCacheItemIsHit(bool $result): void
    {
        $this->cacheItem
            ->expects(self::once())
            ->method('isHit')
            ->willReturn($result);
    }

    /**
     * @param array<mixed>|object $value
     */
    private function assertCacheItemGet($value): void
    {
        $this->cacheItem
            ->expects(self::once())
            ->method('get')
            ->willReturn($value);
    }

    /**
     * @param array<mixed>|object $value
     */
    private function assertCacheItemSet($value): void
    {
        $this->cacheItem
            ->expects(self::once())
            ->method('set')
            ->with($value);
    }

    private function assertCacheItemExpiresAfter(): void
    {
        $this->cacheItem
            ->expects(self::once())
            ->method('expiresAfter')
            ->with(3600);
    }

    private function assertCacheSave(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with($this->cacheItem);
    }
}
