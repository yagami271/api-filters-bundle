<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration;

final class DatabaseConnectionFactory
{
    private string $driver;
    private string $host;
    private int $port;
    private string $dbName;
    private string $user;
    private string $password;

    public function __construct()
    {
        $envDriver = getenv('DATABASE_DRIVER') ?: 'sqlite';
        $this->driver = $envDriver;
        $this->host = getenv('DATABASE_HOST') ?: '127.0.0.1';
        $this->port = (int) (getenv('DATABASE_PORT') ?: $this->getDefaultPort());
        $this->dbName = getenv('DATABASE_NAME') ?: 'api_filters_test';
        $this->user = getenv('DATABASE_USER') ?: 'root';
        $this->password = getenv('DATABASE_PASSWORD') ?: 'root';
    }

    /**
     * @return array<string, mixed>
     */
    public function getDoctrineConnectionParams(): array
    {
        if ($this->isSqlite()) {
            return [
                'driver' => 'pdo_sqlite',
                'memory' => true,
            ];
        }

        return [
            'driver' => $this->getDbalDriver(),
            'host' => $this->host,
            'port' => $this->port,
            'dbname' => $this->dbName,
            'user' => $this->user,
            'password' => $this->password,
        ];
    }

    public function getPdoDsn(): string
    {
        return match ($this->driver) {
            'mysql', 'mariadb' => \sprintf('mysql:host=%s;port=%d;dbname=%s', $this->host, $this->port, $this->dbName),
            'pgsql' => \sprintf('pgsql:host=%s;port=%d;dbname=%s', $this->host, $this->port, $this->dbName),
            default => 'sqlite::memory:',
        };
    }

    public function getPdoUser(): ?string
    {
        return $this->isSqlite() ? null : $this->user;
    }

    public function getPdoPassword(): ?string
    {
        return $this->isSqlite() ? null : $this->password;
    }

    public function getCreateTableSql(): string
    {
        $idColumn = match ($this->driver) {
            'mysql', 'mariadb' => 'id INT AUTO_INCREMENT PRIMARY KEY',
            'pgsql' => 'id SERIAL PRIMARY KEY',
            default => 'id INTEGER PRIMARY KEY AUTOINCREMENT',
        };

        return \sprintf(
            'CREATE TABLE test_users (
                %s,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                status VARCHAR(255) NOT NULL,
                age INTEGER DEFAULT NULL
            )',
            $idColumn,
        );
    }

    public function isSqlite(): bool
    {
        return 'sqlite' === $this->driver;
    }

    private function getDbalDriver(): string
    {
        return match ($this->driver) {
            'mysql', 'mariadb' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            default => 'pdo_sqlite',
        };
    }

    private function getDefaultPort(): int
    {
        return match ($this->driver) {
            'mysql', 'mariadb' => 3306,
            'pgsql' => 5432,
            default => 0,
        };
    }
}
