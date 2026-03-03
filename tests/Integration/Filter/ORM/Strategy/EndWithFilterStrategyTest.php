<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EndWithFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithEndWithScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', '@example.com')]),
            ['email' => 'u.email'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertStringEndsWith('@example.com', $user->getEmail());
        }
    }

    #[Test]
    public function itFiltersWithEndWithArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', ['@example.com', '@test.org'])]),
            ['email' => 'u.email'],
        );

        self::assertCount(5, $results);
        $emails = array_map(fn (TestUser $u) => $u->getEmail(), $results);
        self::assertContains('john@example.com', $emails);
        self::assertContains('bob@test.org', $emails);
    }

    #[Test]
    public function itEscapesWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'end_with', '%')]),
            ['email' => 'u.email'],
        );

        self::assertCount(0, $results);
    }
}
