<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GteFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithGteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', 30)]),
            ['age' => 'u.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', [25, 30])]),
            ['age' => 'u.age'],
        );
    }

    #[Test]
    public function itFiltersWithGteExcludesLower(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', 29)]),
            ['age' => 'u.age'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertGreaterThanOrEqual(29, $user->getAge());
        }
    }
}
