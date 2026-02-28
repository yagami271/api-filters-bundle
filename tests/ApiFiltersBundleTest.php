<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests;

use Isma\ApiFiltersBundle\ApiFiltersBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiFiltersBundleTest extends TestCase
{
    public function testBundleIsInstantiable(): void
    {
        $bundle = new ApiFiltersBundle();

        $this->assertInstanceOf(AbstractBundle::class, $bundle);
    }

    public function testGetPathReturnsParentDirectory(): void
    {
        $bundle = new ApiFiltersBundle();

        $this->assertSame(\dirname(__DIR__), $bundle->getPath());
    }
}
