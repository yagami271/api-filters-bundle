<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterApplier;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\EndWithFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\GteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\GtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\IeqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\IlikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\InoteqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\InotlikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\IsNullFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\LikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\LteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\LtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\NeqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\OrderFilterStrategy;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\StartWithFilterStrategy;
use Isma\ApiFiltersBundle\Tests\Integration\DatabaseConnectionFactory;
use Isma\ApiFiltersBundle\ValueObject\Filters;

trait DbalFilterTestTrait
{
    private Connection $connection;
    private DbalFilterApplier $applier;

    protected function setUp(): void
    {
        $factory = new DatabaseConnectionFactory();

        $this->connection = DriverManager::getConnection($factory->getDoctrineConnectionParams());

        $this->connection->executeStatement('DROP TABLE IF EXISTS test_users');
        $this->connection->executeStatement($factory->getCreateTableSql());

        $this->insertFixtures();

        $this->applier = new DbalFilterApplier([
            new EqFilterStrategy(),
            new NeqFilterStrategy(),
            new LikeFilterStrategy(),
            new IlikeFilterStrategy(),
            new InotlikeFilterStrategy(),
            new IeqFilterStrategy(),
            new InoteqFilterStrategy(),
            new GtFilterStrategy(),
            new GteFilterStrategy(),
            new LtFilterStrategy(),
            new LteFilterStrategy(),
            new IsNullFilterStrategy(),
            new StartWithFilterStrategy(),
            new EndWithFilterStrategy(),
            new OrderFilterStrategy(),
        ]);
    }

    /**
     * @param array<string, string> $mapping
     *
     * @return array<int, array<string, mixed>>
     */
    private function applyAndFetch(Filters $filters, array $mapping): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('test_users', 't');

        $this->applier->apply($qb, $filters, $mapping);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    private function insertFixtures(): void
    {
        $users = [
            ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com', 'status' => 'active', 'age' => 30],
            ['firstname' => 'Jane', 'lastname' => 'Doe', 'email' => 'jane@example.com', 'status' => 'active', 'age' => 25],
            ['firstname' => 'John', 'lastname' => 'Smith', 'email' => 'john.smith@test.org', 'status' => 'inactive', 'age' => 35],
            ['firstname' => 'Alice', 'lastname' => 'Johnson', 'email' => 'alice@example.com', 'status' => 'active', 'age' => 28],
            ['firstname' => 'Bob', 'lastname' => 'Williams', 'email' => 'bob@test.org', 'status' => 'banned', 'age' => null],
        ];

        foreach ($users as $user) {
            $this->connection->insert('test_users', $user);
        }
    }
}
