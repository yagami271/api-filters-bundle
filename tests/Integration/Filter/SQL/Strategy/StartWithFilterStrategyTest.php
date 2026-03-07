<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StartWithFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithStartWithScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'start_with', 'Jo')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertStringStartsWith('Jo', $row['firstname']);
        }
    }

    #[Test]
    public function itFiltersWithStartWithArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'start_with', ['Jo', 'Al'])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }
}
