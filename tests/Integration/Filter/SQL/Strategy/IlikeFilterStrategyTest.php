<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IlikeFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithIlikeScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'ilike', '@EXAMPLE.COM')]),
            ['email' => 'email'],
        );

        self::assertCount(3, $results);
        foreach ($results as $row) {
            self::assertStringContainsStringIgnoringCase('@example.com', $row['email']);
        }
    }

    #[Test]
    public function itFiltersWithIlikeArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'ilike', ['JOHN', 'ALICE'])]),
            ['email' => 'email'],
        );

        self::assertCount(3, $results);
        $emails = array_column($results, 'email');
        self::assertContains('john@example.com', $emails);
        self::assertContains('john.smith@test.org', $emails);
        self::assertContains('alice@example.com', $emails);
    }

    #[Test]
    public function itEscapesIlikeWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'ilike', '%')]),
            ['email' => 'email'],
        );

        self::assertCount(0, $results);
    }
}
