<?php

declare(strict_types=1);

use Asgoodasnew\AkeneoApiBundle\AkeneoApi;
use Asgoodasnew\AkeneoApiBundle\AkeneoApiAuthenticator;
use Asgoodasnew\AkeneoApiBundle\CategoryTreeBuilder;
use Asgoodasnew\AkeneoApiBundle\SymfonyHttpClientAkeneoApi;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
            ->set('asgoodasnew_akeneo_api.symfony_http_client_akeneo_api', SymfonyHttpClientAkeneoApi::class)
            ->arg(0, '')
            ->arg(1, service(HttpClientInterface::class))
            ->arg(2, service('asgoodasnew_akeneo_api.akeneo_api_authenticator'))
            ->arg(3, service('asgoodasnew_akeneo_api.category_tree_builder'));

    $services
            ->alias(AkeneoApi::class, 'asgoodasnew_akeneo_api.symfony_http_client_akeneo_api')
            ->public();

    $services
            ->set('asgoodasnew_akeneo_api.category_tree_builder', CategoryTreeBuilder::class);

    $services
            ->set('asgoodasnew_akeneo_api.akeneo_api_authenticator', AkeneoApiAuthenticator::class)
            ->arg(0, '')
            ->arg(1, '')
            ->arg(2, '')
            ->arg(3, '')
            ->arg(4, service(HttpClientInterface::class));
};
