<?php

namespace App\Services;

use App\Enums\DatabaseType;
use Exception;
use PDO;
use PDOException;

class DatabaseConnectionTester
{
    /**
     * Test a database connection with the provided credentials.
     *
     * @param  array{database_type: string, host: string, port: int, username: string, password: string, database_name: ?string}  $config
     * @return array{success: bool, message: string}
     */
    public function test(array $config): array
    {
        try {
            $databaseType = DatabaseType::tryFrom($config['database_type']);

            if ($databaseType === null) {
                return [
                    'success' => false,
                    'message' => "Unsupported database type: {$config['database_type']}",
                ];
            }

            // SQLite: check if file exists and is readable
            if ($databaseType === DatabaseType::SQLITE) {
                return $this->testSqliteConnection($config['host']);
            }

            $dsn = $databaseType->buildDsn(
                $config['host'],
                $config['port'],
                $config['database_name'] ?? null
            );

            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                ]
            );

            // Test the connection by running a simple query
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => 'Successfully connected to the database server!',
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => $this->formatErrorMessage($e),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Test SQLite connection by checking if file exists and is readable.
     *
     * @return array{success: bool, message: string}
     */
    private function testSqliteConnection(string $path): array
    {
        if (empty($path)) {
            return [
                'success' => false,
                'message' => 'Database path is required.',
            ];
        }

        if (! file_exists($path)) {
            return [
                'success' => false,
                'message' => 'Database file does not exist: '.$path,
            ];
        }

        if (! is_readable($path)) {
            return [
                'success' => false,
                'message' => 'Database file is not readable: '.$path,
            ];
        }

        if (! is_file($path)) {
            return [
                'success' => false,
                'message' => 'Path is not a file: '.$path,
            ];
        }

        // Try to open the SQLite database to verify it's valid
        try {
            $pdo = new PDO("sqlite:{$path}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => 'Successfully connected to the SQLite database!',
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Invalid SQLite database file: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Format PDO exception message for user-friendly display.
     */
    private function formatErrorMessage(PDOException $e): string
    {
        $message = $e->getMessage();

        // Common error patterns
        if (str_contains($message, 'Access denied')) {
            return 'Access denied. Please check your username and password.';
        }

        if (str_contains($message, 'Unknown database')) {
            return 'Database not found. Please check the database name.';
        }

        if (str_contains($message, 'Connection refused') || str_contains($message, 'Connection timed out')) {
            return 'Connection refused. Please check the host and port.';
        }

        if (str_contains($message, "Can't connect")) {
            return 'Unable to connect to the database server. Please verify the host and port.';
        }

        // Return sanitized error message
        return 'Connection failed: '.$message;
    }
}
