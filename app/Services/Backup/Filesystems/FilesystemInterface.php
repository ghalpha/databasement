<?php

namespace App\Services\Backup\Filesystems;

use League\Flysystem\Filesystem;

interface FilesystemInterface
{
    public function handles(?string $type): bool;

    /**
     * @param  array<string, mixed>  $config
     */
    public function get(array $config): Filesystem;
}
