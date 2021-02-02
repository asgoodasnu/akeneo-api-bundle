<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApi;
use Asgoodasnew\AkeneoApiBundle\CachedSymfonyHttpClientAkeneoApi;
use Asgoodasnew\AkeneoApiBundle\Model\CategoryItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

class CachedSymfonyHttpClientAkeneoApiTest extends TestCase
{
    protected CachedSymfonyHttpClientAkeneoApi $cachedSymfonyHttpClientAkeneoApi;

    /**
     * @var MockObject
     */
    protected $decorated;

    /**
     * @var MockObject
     */
    protected $cache;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->decorated = $this->createMock(AkeneoApi::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->cachedSymfonyHttpClientAkeneoApi = new CachedSymfonyHttpClientAkeneoApi($this->decorated, $this->cache);
    }

    public function testGetProductCached(): void
    {
        $sku = 'AN12345';

        $productArray = [
            'key' => 'value',
        ];

        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with($sku)
            ->willReturn($cacheItem);

        $cacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);

        $cacheItem
            ->expects($this->once())
            ->method('get')
            ->willReturn($productArray);

        $this->assertSame($productArray, $this->cachedSymfonyHttpClientAkeneoApi->getProduct($sku));
    }

    public function testGetProductNotCached(): void
    {
        $sku = 'AN12345';

        $productArray = [
            'key' => 'value',
        ];

        $this->decorated
            ->expects($this->once())
            ->method('getProduct')
            ->with($sku)
            ->willReturn($productArray);

        $cacheItem = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->once())
            ->method('getItem')
            ->with($sku)
            ->willReturn($cacheItem);

        $cacheItem
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $cacheItem
            ->expects($this->once())
            ->method('set')
            ->with($productArray);

        $cacheItem
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(3600);

        $this->cache
            ->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        $this->assertSame($productArray, $this->cachedSymfonyHttpClientAkeneoApi->getProduct($sku));
    }

    public function testGetCategories(): void
    {
        $item = new CategoryItem('code', 'title');

        $this->decorated
            ->expects($this->once())
            ->method('getCategories')
            ->with('root')
            ->willReturn($item);

        $this->assertSame($item, $this->cachedSymfonyHttpClientAkeneoApi->getCategories('root'));
    }

    public function testTriggerUpdate(): void
    {
        $this->decorated
            ->expects($this->once())
            ->method('triggerUpdate')
            ->with('identifier');

        $this->cachedSymfonyHttpClientAkeneoApi->triggerUpdate('identifier');
    }
}
