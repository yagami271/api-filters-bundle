<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LtFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithLtScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', 30)]),
            ['age' => 't.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('Jane', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', [25, 30])]),
            ['age' => 't.age'],
        );
    }

    #[Test]
    public function itFiltersWithLtExcludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lt', 25)]),
            ['age' => 't.age'],
        );

        self::assertCount(0, $results);
    }
}
