<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\UserStatus;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeqFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithNeqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', 'John')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertNotSame('John', $user->getFirstname());
        }
    }

    #[Test]
    public function itFiltersWithNeqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', ['John', 'Jane'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('Alice', $firstnames);
        self::assertContains('Bob', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithNeq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itFiltersWithNeqEnum(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'neq', UserStatus::Active)]),
            ['status' => 'u.status'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertNotSame(UserStatus::Active, $user->getStatus());
        }
    }
}
