<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StartWithFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithStartWithScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'start_with', 'Jo')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertStringStartsWith('Jo', $user->getFirstname());
        }
    }

    #[Test]
    public function itFiltersWithStartWithArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'start_with', ['Jo', 'Al'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itEscapesWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'start_with', '%')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(0, $results);
    }
}
