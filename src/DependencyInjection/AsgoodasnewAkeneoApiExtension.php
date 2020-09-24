<?php

declare(strict_types=1);

namespace Asgoodasnew\AkeneoApiBundle\DependencyInjection;

use Asgoodasnew\AkeneoApiBundle\CachedSymfonyHttpClientAkeneoApi;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class AsgoodasnewAkeneoApiExtension extends Extension
{
    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->handleConfigs($container, $configs);

        $this->addArgumentsToAuthenticator($container, $config);
        $this->addArgumentsToSymfonyHttpClient($container, $config['url']);

        if ($config['cached']) {
            $this->addDecorator($container);
        }
    }

    private function addDecorator(ContainerBuilder $container): void
    {
        $decorated = new Definition('asgoodasnew_akeneo_api.cached_symfony_http_client_akeneo_api');

        $decorated->setClass(CachedSymfonyHttpClientAkeneoApi::class);

        $decorated->setDecoratedService('asgoodasnew_akeneo_api.symfony_http_client_akeneo_api');

        $decorated->setArgument(0, new Reference('asgoodasnew_akeneo_api.cached_symfony_http_client_akeneo_api.inner'));
        $decorated->setArgument(1, new Reference('Psr\Cache\CacheItemPoolInterface'));

        $container->addDefinitions([
            'asgoodasnew_akeneo_api.cached_symfony_http_client_akeneo_api' => $decorated,
        ]);
    }

    /**
     * @param array<string, mixed> $configs
     *
     * @return array<string, mixed>
     */
    private function handleConfigs(ContainerBuilder $container, array $configs): array
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        return $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function addArgumentsToAuthenticator(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition('asgoodasnew_akeneo_api.akeneo_api_authenticator')
            ->setArgument(0, $config['url'])
            ->setArgument(1, $config['user'])
            ->setArgument(2, $config['password'])
            ->setArgument(3, $config['token']);
    }

    private function addArgumentsToSymfonyHttpClient(ContainerBuilder $container, string $url): void
    {
        $container->getDefinition('asgoodasnew_akeneo_api.symfony_http_client_akeneo_api')
            ->setArgument(0, $url);
    }
}
