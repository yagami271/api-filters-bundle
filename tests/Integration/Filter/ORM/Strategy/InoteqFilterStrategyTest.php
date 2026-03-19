<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InoteqFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithInoteqScalarCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 'john')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertNotSame('John', $user->getFirstname());
        }
    }

    #[Test]
    public function itFiltersWithInoteqArrayCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', ['JOHN', 'alice'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertNotContains($user->getFirstname(), ['John', 'Alice']);
        }
    }

    #[Test]
    public function itHandlesEmptyArrayWithInoteq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itThrowsOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 123)]),
            ['firstname' => 'u.firstname'],
        );
    }
}
