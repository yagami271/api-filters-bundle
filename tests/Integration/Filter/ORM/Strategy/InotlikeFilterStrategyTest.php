<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InotlikeFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithInotlikeScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', '@EXAMPLE.COM')]),
            ['email' => 'u.email'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertStringNotContainsStringIgnoringCase('@example.com', $user->getEmail());
        }
    }

    #[Test]
    public function itFiltersWithInotlikeArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', ['JOHN', 'ALICE'])]),
            ['email' => 'u.email'],
        );

        self::assertCount(2, $results);
    }

    #[Test]
    public function itEscapesInotlikeWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', '%')]),
            ['email' => 'u.email'],
        );

        self::assertCount(5, $results);
    }
}
