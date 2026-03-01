<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Isma\ApiFiltersBundle\Exception\DuplicateFilterStrategyException;
use Isma\ApiFiltersBundle\Filter\ORM\OrmFilterApplier;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\LikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\NeqFilterStrategy;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\UserStatus;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrmFilterIntegrationTest extends TestCase
{
    private EntityManagerInterface $em;
    private OrmFilterApplier $applier;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__.'/Entity'],
            isDevMode: true,
        );
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $this->em = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $this->insertFixtures();

        $this->applier = new OrmFilterApplier([
            new EqFilterStrategy(),
            new NeqFilterStrategy(),
            new LikeFilterStrategy(),
        ]);
    }

    #[Test]
    public function itFiltersWithEqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', 'John')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        self::assertSame('John', $results[0]->getFirstname());
        self::assertSame('John', $results[1]->getFirstname());
    }

    #[Test]
    public function itFiltersWithEqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', ['John', 'Alice'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itFiltersWithNeqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', 'John')]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertNotSame('John', $user->getFirstname());
        }
    }

    #[Test]
    public function itFiltersWithNeqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', ['John', 'Jane'])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(2, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('Alice', $firstnames);
        self::assertContains('Bob', $firstnames);
    }

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
    public function itFiltersWithEqEnum(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'eq', UserStatus::Active)]),
            ['status' => 'u.status'],
        );

        self::assertCount(3, $results);
        foreach ($results as $user) {
            self::assertSame(UserStatus::Active, $user->getStatus());
        }
    }

    #[Test]
    public function itFiltersWithEqEnumArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'eq', [UserStatus::Active, UserStatus::Inactive])]),
            ['status' => 'u.status'],
        );

        self::assertCount(4, $results);
        foreach ($results as $user) {
            self::assertContains($user->getStatus(), [UserStatus::Active, UserStatus::Inactive]);
        }
    }

    #[Test]
    public function itFiltersWithNeqEnum(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('status', 'neq', UserStatus::Active)]),
            ['status' => 'u.status'],
        );

        self::assertCount(2, $results);
        foreach ($results as $user) {
            self::assertNotSame(UserStatus::Active, $user->getStatus());
        }
    }

    #[Test]
    public function itCombinesMultipleFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters([
                new Filter('firstname', 'eq', 'John'),
                new Filter('lastname', 'eq', 'Doe'),
            ]),
            [
                'firstname' => 'u.firstname',
                'lastname' => 'u.lastname',
            ],
        );

        self::assertCount(1, $results);
        self::assertSame('John', $results[0]->getFirstname());
        self::assertSame('Doe', $results[0]->getLastname());
    }

    #[Test]
    public function itThrowsOnFilterNotInMapping(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapping defined for filter "nonexistent".');

        $this->applyAndFetch(
            new Filters([new Filter('nonexistent', 'eq', 'value')]),
            ['firstname' => 'u.firstname'],
        );
    }

    #[Test]
    public function itThrowsOnDuplicateStrategyType(): void
    {
        $this->expectException(DuplicateFilterStrategyException::class);

        new OrmFilterApplier([
            new EqFilterStrategy(),
            new EqFilterStrategy(),
        ]);
    }

    #[Test]
    public function itThrowsOnUnknownFilterType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(TestUser::class, 'u');

        $this->applier->apply(
            $qb,
            new Filters([new Filter('firstname', 'unknown', 'value')]),
            ['firstname' => 'u.firstname'],
        );
    }

    #[Test]
    public function itHandlesEmptyArrayWithEq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itHandlesEmptyArrayWithNeq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', [])]),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
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

    #[Test]
    public function itReturnsAllRowsWhenNoFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters(),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }

    /**
     * @param array<string, string> $mapping
     *
     * @return TestUser[]
     */
    private function applyAndFetch(Filters $filters, array $mapping): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(TestUser::class, 'u');

        $this->applier->apply($qb, $filters, $mapping);

        return $qb->getQuery()->getResult();
    }

    private function insertFixtures(): void
    {
        $users = [
            new TestUser('John', 'Doe', 'john@example.com', UserStatus::Active),
            new TestUser('Jane', 'Doe', 'jane@example.com', UserStatus::Active),
            new TestUser('John', 'Smith', 'john.smith@test.org', UserStatus::Inactive),
            new TestUser('Alice', 'Johnson', 'alice@example.com', UserStatus::Active),
            new TestUser('Bob', 'Williams', 'bob@test.org', UserStatus::Banned),
        ];

        foreach ($users as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}
