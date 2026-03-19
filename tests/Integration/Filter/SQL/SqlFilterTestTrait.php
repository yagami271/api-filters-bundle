<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterApplier;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\EndWithFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\GteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\GtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\IeqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\IlikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\InoteqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\InotlikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\IsNullFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\LikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\LteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\LtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\NeqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\OrderFilterStrategy;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\StartWithFilterStrategy;
use Isma\ApiFiltersBundle\Tests\Integration\DatabaseConnectionFactory;
use Isma\ApiFiltersBundle\ValueObject\Filters;

trait SqlFilterTestTrait
{
    private \PDO $pdo;
    private SqlFilterApplier $applier;

    protected function setUp(): void
    {
        $factory = new DatabaseConnectionFactory();

        $this->pdo = new \PDO($factory->getPdoDsn(), $factory->getPdoUser(), $factory->getPdoPassword());
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('DROP TABLE IF EXISTS test_users');
        $this->pdo->exec($factory->getCreateTableSql());

        $this->insertFixtures();

        $this->applier = new SqlFilterApplier([
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
        $context = new SqlQueryContext();
        $this->applier->apply($context, $filters, $mapping);

        $sql = 'SELECT * FROM test_users';
        if ($where = $context->getWhereClause()) {
            $sql .= ' WHERE '.$where;
        }
        if ($orderBy = $context->getOrderByClause()) {
            $sql .= ' ORDER BY '.$orderBy;
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($context->getParameters() as $name => $value) {
            $stmt->bindValue(':'.$name, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

        $stmt = $this->pdo->prepare('INSERT INTO test_users (firstname, lastname, email, status, age) VALUES (:firstname, :lastname, :email, :status, :age)');
        foreach ($users as $user) {
            $stmt->execute($user);
        }
    }
}
