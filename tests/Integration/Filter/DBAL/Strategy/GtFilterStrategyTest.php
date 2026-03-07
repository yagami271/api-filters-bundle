<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GtFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithGtScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', 28)]),
            ['age' => 't.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('John', $firstnames);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', [25, 30])]),
            ['age' => 't.age'],
        );
    }

    #[Test]
    public function itFiltersWithGtExcludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', 30)]),
            ['age' => 't.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('John', $results[0]['firstname']);
        self::assertSame(35, $results[0]['age']);
    }
}
