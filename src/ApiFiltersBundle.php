<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle;

use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterApplier;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\FilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\ORM\OrmFilterApplier;
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterApplier;
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiFiltersBundle extends AbstractBundle
{
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
        $services->load('Isma\\ApiFiltersBundle\\Filter\\', '../src/Filter/');

        $services->alias(FilterApplierInterface::class, OrmFilterApplier::class);

        $builder->registerForAutoconfiguration(DbalFilterStrategyInterface::class)
            ->addTag('isma_api_filters.dbal_strategy');

        $services->alias(DbalFilterApplierInterface::class, DbalFilterApplier::class);

        $builder->registerForAutoconfiguration(SqlFilterStrategyInterface::class)
            ->addTag('isma_api_filters.sql_strategy');

        $services->alias(SqlFilterApplierInterface::class, SqlFilterApplier::class);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
