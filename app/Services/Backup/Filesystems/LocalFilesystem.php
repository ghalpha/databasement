<?php

namespace App\Services\Backup\Filesystems;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalFilesystem implements FilesystemInterface
{
    public function handles(?string $type): bool
    {
        return strtolower($type ?? '') === 'local';
    }

    /**
     * @param  array{root?: string, path?: string}  $config
     */
    public function get(array $config): Filesystem
    {
        // Support both 'root' (from config/backup.php) and 'path' (from Volume database)
        $root = $config['root'] ?? $config['path'] ?? null;

        if ($root === null) {
            throw new \InvalidArgumentException('Local filesystem requires either "root" or "path" in config');
        }

        return new Filesystem(new LocalFilesystemAdapter($root));
    }
}
