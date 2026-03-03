<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LtFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithLtScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', 30)]),
            ['age' => 'u.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('Jane', $firstnames);   // age 25
        self::assertContains('Alice', $firstnames);   // age 28
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', [25, 30])]),
            ['age' => 'u.age'],
        );
    }

    #[Test]
    public function itFiltersWithLtExcludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', 25)]),
            ['age' => 'u.age'],
        );

        self::assertCount(0, $results);
    }
}
