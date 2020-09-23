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
    public function testServiceWiring()
    {
        $kernel = new AkeneoApiTestingKernel([
            'url' => 'url',
            'user' => 'user',
            'password' => 'password',
            'token' => 'token',
            'cached' => false,
        ]);

        $kernel->boot();

        $container = $kernel->getContainer();

        $akeneoApi = $container->get('Asgoodasnew\AkeneoApiBundle\AkeneoApi');

        $this->assertInstanceOf(AkeneoApi::class, $akeneoApi);
    }
}

class AkeneoApiTestingKernel extends Kernel
{
    private array $akeneoApiConfig;

    public function __construct(array $akeneoApiConfig)
    {
        parent::__construct('test', true);

        $this->akeneoApiConfig = $akeneoApiConfig;
    }

    public function registerBundles()
    {
        return [
            new AsgoodasnewAkeneoApiBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $containerBuilder) {
            $containerBuilder->register(
                'Symfony\Contracts\HttpClient\HttpClientInterface',
                'Symfony\Component\HttpClient\NativeHttpClient'
            );

            $containerBuilder->loadFromExtension('asgoodasnew_akeneo_api', $this->akeneoApiConfig);
        });
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../var/' . spl_object_hash($this);
    }
}