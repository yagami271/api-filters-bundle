<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LikeFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithLikeScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', '@example.com')]),
            ['email' => 'email'],
        );

        self::assertCount(3, $results);
        foreach ($results as $row) {
            self::assertStringContainsString('@example.com', $row['email']);
        }
    }

    #[Test]
    public function itFiltersWithLikeArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', ['john', 'alice'])]),
            ['email' => 'email'],
        );

        self::assertCount(3, $results);
        $emails = array_column($results, 'email');
        self::assertContains('john@example.com', $emails);
        self::assertContains('john.smith@test.org', $emails);
        self::assertContains('alice@example.com', $emails);
    }

    #[Test]
    public function itEscapesLikeWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', '%')]),
            ['email' => 'email'],
        );

        self::assertCount(0, $results);
    }
}
