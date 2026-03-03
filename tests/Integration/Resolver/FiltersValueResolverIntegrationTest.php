<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Resolver;

use Isma\ApiFiltersBundle\Tests\Integration\TestKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FiltersValueResolverIntegrationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    #[Test]
    public function itResolvesSingleEqFilter(): void
    {
        $this->client->request('GET', '/list?filters[firstname][eq]=John');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(1, $data['count']);
        self::assertSame('firstname', $data['filters'][0]['name']);
        self::assertSame('eq', $data['filters'][0]['type']);
        self::assertSame('John', $data['filters'][0]['value']);
    }

    #[Test]
    public function itResolvesMultipleFilters(): void
    {
        $this->client->request('GET', '/list?filters[firstname][eq]=John&filters[lastname][neq]=Doe');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(2, $data['count']);
    }

    #[Test]
    public function itResolvesEnumFilter(): void
    {
        $this->client->request('GET', '/list?filters[status][eq]=eq');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(1, $data['count']);
        self::assertSame('status', $data['filters'][0]['name']);
        self::assertSame('eq', $data['filters'][0]['type']);
        self::assertSame('eq', $data['filters'][0]['value']);
    }

    #[Test]
    public function itReturns400ForUnknownFilter(): void
    {
        $this->client->request('GET', '/list?filters[unknown][eq]=value');

        self::assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function itReturns400ForDisallowedType(): void
    {
        $this->client->request('GET', '/list?filters[firstname][neq]=John');

        self::assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function itReturns400ForInvalidEnumValue(): void
    {
        $this->client->request('GET', '/list?filters[status][eq]=invalid');

        self::assertResponseStatusCodeSame(400);
    }

    #[Test]
    public function itResolvesEmptyFiltersWhenNoQueryParams(): void
    {
        $this->client->request('GET', '/list');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(0, $data['count']);
        self::assertSame([], $data['filters']);
    }

    #[Test]
    public function itResolvesFiltersForInvokableController(): void
    {
        $this->client->request('GET', '/invokable?filters[firstname][eq]=Jane');

        self::assertResponseIsSuccessful();

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);

        self::assertSame(1, $data['count']);
        self::assertSame('firstname', $data['filters'][0]['name']);
        self::assertSame('eq', $data['filters'][0]['type']);
        self::assertSame('Jane', $data['filters'][0]['value']);
    }
}
