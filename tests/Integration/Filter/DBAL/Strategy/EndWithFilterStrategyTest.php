<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EndWithFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithEndWithScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', '@example.com')]),
            ['email' => 't.email'],
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
            ['email' => 't.email'],
        );

        self::assertCount(5, $results);
        $emails = array_column($results, 'email');
        self::assertContains('john@example.com', $emails);
        self::assertContains('bob@test.org', $emails);
    }

    #[Test]
    public function itEscapesWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', '%')]),
            ['email' => 't.email'],
        );

        self::assertCount(0, $results);
    }
}
