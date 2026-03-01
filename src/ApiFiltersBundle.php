<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle;

use Isma\ApiFiltersBundle\Filter\FilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\ORM\OrmFilterApplier;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiFiltersBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $builder->registerForAutoconfiguration(FilterStrategyInterface::class)
            ->addTag('isma_api_filters.strategy');

        $services = $container->services();

        $services->defaults()
            ->autowire()
            ->autoconfigure();

        $services->load('Isma\\ApiFiltersBundle\\Resolver\\', '../src/Resolver/');
        $services->load('Isma\\ApiFiltersBundle\\Filter\\ORM\\', '../src/Filter/ORM/');

        $services->alias(FilterApplierInterface::class, OrmFilterApplier::class);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
