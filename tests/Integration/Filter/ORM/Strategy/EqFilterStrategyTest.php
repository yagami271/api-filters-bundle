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

final class EqFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithEqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', 'John')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        self::assertSame('John', $results[0]->getFirstname());
        self::assertSame('John', $results[1]->getFirstname());
    }

    #[Test]
    public function itFiltersWithEqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', ['John', 'Alice'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithEq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itFiltersWithEqEnum(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'eq', UserStatus::Active)]),
            ['status' => 'u.status'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertSame(UserStatus::Active, $user->getStatus());
        }
    }

    #[Test]
    public function itFiltersWithEqEnumArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'eq', [UserStatus::Active, UserStatus::Inactive])]),
            ['status' => 'u.status'],
        );

        self::assertCount(4, $results);
        foreach ($results as $user) {
            self::assertContains($user->getStatus(), [UserStatus::Active, UserStatus::Inactive]);
        }
    }
}
