<?php

namespace App\Services;

use App\Models\Volume;
use App\Services\Backup\Filesystems\FilesystemProvider;

readonly class VolumeConnectionTester
{
    public function __construct(
        private FilesystemProvider $filesystemProvider
    ) {}

    /**
     * Test if a volume is accessible by creating and deleting a test file.
     *
     * @return array{success: bool, message: string}
     */
    public function test(Volume $volume): array
    {
        $testFilename = '.databasement-test-'.uniqid();
        $testContent = 'test-'.uniqid();

        try {
            $filesystem = $this->filesystemProvider->getForVolume($volume);

            // Try to write test file
            $filesystem->write($testFilename, $testContent);

            // Try to read test file
            $retrieved = $filesystem->read($testFilename);
            if ($retrieved !== $testContent) {
                $filesystem->delete($testFilename);

                return [
                    'success' => false,
                    'message' => 'Failed to verify test file content.',
                ];
            }

            // Delete test file
            $filesystem->delete($testFilename);

            return [
                'success' => true,
                'message' => 'Connection successful!',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
