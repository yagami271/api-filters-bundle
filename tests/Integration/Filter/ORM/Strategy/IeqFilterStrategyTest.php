<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IeqFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithIeqScalarCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', 'john')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertSame('John', $user->getFirstname());
        }
    }

    #[Test]
    public function itFiltersWithIeqArrayCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', ['JOHN', 'alice'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithIeq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itThrowsOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', 123)]),
            ['firstname' => 'u.firstname'],
        );
    }
}
