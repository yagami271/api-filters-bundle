<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InotlikeFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithInotlikeScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', '@EXAMPLE.COM')]),
            ['email' => 'email'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertStringNotContainsStringIgnoringCase('@example.com', $row['email']);
        }
    }

    #[Test]
    public function itFiltersWithInotlikeArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', ['JOHN', 'ALICE'])]),
            ['email' => 'email'],
        );

        self::assertCount(2, $results);
    }

    #[Test]
    public function itEscapesInotlikeWildcards(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('email', 'inotlike', '%')]),
            ['email' => 'email'],
        );

        self::assertCount(5, $results);
    }
}
