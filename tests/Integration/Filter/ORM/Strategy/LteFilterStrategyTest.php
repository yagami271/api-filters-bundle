<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LteFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithLteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', 28)]),
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
            new Filters([new Filter('age', 'lte', [25, 30])]),
            ['age' => 'u.age'],
        );
    }

    #[Test]
    public function itFiltersWithLteIncludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', 25)]),
            ['age' => 'u.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Jane', $results[0]->getFirstname());
    }
}
