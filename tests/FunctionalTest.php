<?php

namespace Asgoodasnew\AkeneoApiBundle\Tests;

use Asgoodasnew\AkeneoApiBundle\AkeneoApi;
use Asgoodasnew\AkeneoApiBundle\AsgoodasnewAkeneoApiBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class FunctionalTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $kernel = new AkeneoApiTestingKernel([
            'url' => 'url',
            'user' => 'user',
            'password' => 'password',
            'token' => 'token',
            'cached' => true,
        ]);

        $kernel->boot();

        $container = $kernel->getContainer();

        $akeneoApi = $container->get('Asgoodasnew\AkeneoApiBundle\AkeneoApi');

        $this->assertInstanceOf(AkeneoApi::class, $akeneoApi);
    }
}

class AkeneoApiTestingKernel extends Kernel
{
    /**
     * @var array<string, mixed>
     */
    private array $akeneoApiConfig;

    /**
     * @param array<string, mixed> $akeneoApiConfig
     */
    public function __construct(array $akeneoApiConfig)
    {
        parent::__construct('test', true);

        $this->akeneoApiConfig = $akeneoApiConfig;
    }

    /**
     * @return AsgoodasnewAkeneoApiBundle[]
     */
    public function registerBundles(): array
    {
        return [
            new AsgoodasnewAkeneoApiBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $containerBuilder) {
            $containerBuilder->register(
                'Symfony\Contracts\HttpClient\HttpClientInterface',
                'Symfony\Component\HttpClient\NativeHttpClient'
            );

            $containerBuilder->register(
                'Psr\Cache\CacheItemPoolInterface',
                'Symfony\Component\Cache\Adapter\FilesystemAdapter'
            );

            $containerBuilder->loadFromExtension('asgoodasnew_akeneo_api', $this->akeneoApiConfig);
        });
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../var/'.spl_object_hash($this);
    }
}
