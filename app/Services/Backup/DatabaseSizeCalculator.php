<?php

namespace App\Services\Backup;

use App\Models\DatabaseServer;
use PDO;
use PDOException;

class DatabaseSizeCalculator
{
    /**
     * Get the size of a database in bytes
     */
    public function calculate(DatabaseServer $databaseServer): ?int
    {
        try {
            $dsn = $this->buildDsn($databaseServer);
            $pdo = new PDO(
                $dsn,
                $databaseServer->username,
                $databaseServer->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $query = $this->getSizeQuery($databaseServer->database_type, $databaseServer->database_name);
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int) $result['size_bytes'] : null;
        } catch (PDOException $e) {
            // Log error but don't fail the backup
            logger()->warning('Failed to calculate database size', [
                'database_server_id' => $databaseServer->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildDsn(DatabaseServer $databaseServer): string
    {
        return match ($databaseServer->database_type) {
            'mysql', 'mariadb' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s',
                $databaseServer->host,
                $databaseServer->port,
                $databaseServer->database_name
            ),
            'postgresql' => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $databaseServer->host,
                $databaseServer->port,
                $databaseServer->database_name
            ),
            default => throw new \RuntimeException("Unsupported database type: {$databaseServer->database_type}"),
        };
    }

    private function getSizeQuery(string $databaseType, ?string $databaseName): string
    {
        return match ($databaseType) {
            'mysql', 'mariadb' => sprintf(
                'SELECT SUM(data_length + index_length) as size_bytes
                FROM information_schema.TABLES
                WHERE table_schema = %s',
                $this->quote($databaseName)
            ),
            'postgresql' => sprintf(
                'SELECT pg_database_size(%s) as size_bytes',
                $this->quote($databaseName)
            ),
            default => throw new \RuntimeException("Unsupported database type: {$databaseType}"),
        };
    }

    private function quote(?string $value): string
    {
        return $value ? "'".addslashes($value)."'" : 'NULL';
    }
}
