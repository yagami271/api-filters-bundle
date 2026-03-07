<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LteFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithLteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', 28)]),
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
            new Filters([new Filter('age', 'lte', [25, 30])]),
            ['age' => 't.age'],
        );
    }

    #[Test]
    public function itFiltersWithLteIncludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', 25)]),
            ['age' => 't.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Jane', $results[0]['firstname']);
    }
}
