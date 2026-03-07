<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EndWithFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithEndWithScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', '@example.com')]),
            ['email' => 'email'],
        );

        self::assertCount(3, $results);
        foreach ($results as $row) {
            self::assertStringEndsWith('@example.com', $row['email']);
        }
    }

    #[Test]
    public function itFiltersWithEndWithArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', ['@example.com', '@test.org'])]),
            ['email' => 'email'],
        );

        self::assertCount(5, $results);
    }
}
