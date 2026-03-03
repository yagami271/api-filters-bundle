<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration;

use Isma\ApiFiltersBundle\ApiFiltersBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MonologBundle(),
            new ApiFiltersBundle(),
        ];
    }

    private function getConfigDir(): string
    {
        return __DIR__.'/config';
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/api_filters_bundle_test/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/api_filters_bundle_test/log';
    }
}
