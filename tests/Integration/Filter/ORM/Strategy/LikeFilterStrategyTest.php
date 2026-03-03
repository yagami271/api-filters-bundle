<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LikeFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithLikeScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', '@example.com')]),
            ['email' => 'u.email'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertStringContainsString('@example.com', $user->getEmail());
        }
    }

    #[Test]
    public function itFiltersWithLikeArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', ['john', 'alice'])]),
            ['email' => 'u.email'],
        );

        self::assertCount(3, $results);
        $emails = array_map(fn (TestUser $u) => $u->getEmail(), $results);
        self::assertContains('john@example.com', $emails);
        self::assertContains('john.smith@test.org', $emails);
        self::assertContains('alice@example.com', $emails);
    }

    #[Test]
    public function itEscapesLikeWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'like', '%')]),
            ['email' => 'u.email'],
        );

        self::assertCount(0, $results);
    }
}
