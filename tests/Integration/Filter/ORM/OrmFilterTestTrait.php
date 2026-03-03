<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Isma\ApiFiltersBundle\Filter\ORM\OrmFilterApplier;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\EndWithFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\GteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\GtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\IsNullFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\LikeFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\LteFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\LtFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\NeqFilterStrategy;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\StartWithFilterStrategy;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\UserStatus;
use Isma\ApiFiltersBundle\ValueObject\Filters;

trait OrmFilterTestTrait
{
    private EntityManagerInterface $em;
    private OrmFilterApplier $applier;

    protected function setUp(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__.'/Entity'],
            isDevMode: true,
        );

        if (\PHP_VERSION_ID >= 80400) {
            $config->enableNativeLazyObjects(true);
        }

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
            new GtFilterStrategy(),
            new GteFilterStrategy(),
            new LtFilterStrategy(),
            new LteFilterStrategy(),
            new IsNullFilterStrategy(),
            new StartWithFilterStrategy(),
            new EndWithFilterStrategy(),
        ]);
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
            new TestUser('John', 'Doe', 'john@example.com', UserStatus::Active, 30),
            new TestUser('Jane', 'Doe', 'jane@example.com', UserStatus::Active, 25),
            new TestUser('John', 'Smith', 'john.smith@test.org', UserStatus::Inactive, 35),
            new TestUser('Alice', 'Johnson', 'alice@example.com', UserStatus::Active, 28),
            new TestUser('Bob', 'Williams', 'bob@test.org', UserStatus::Banned, null),
        ];

        foreach ($users as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}
