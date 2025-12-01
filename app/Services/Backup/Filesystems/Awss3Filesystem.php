<?php

namespace App\Services\Backup\Filesystems;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;

class Awss3Filesystem implements FilesystemInterface
{
    public function handles(?string $type): bool
    {
        return in_array(strtolower($type ?? ''), ['s3', 'awss3']);
    }

    public function get(array $config): Filesystem
    {
        $client = new S3Client([
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
            'region' => $config['region'],
            'version' => $config['version'] ?? 'latest',
            'endpoint' => $config['endpoint'] ?? null,
            'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
        ]);

        // Support both 'root' (from config/backup.php) and 'prefix' (from Volume database)
        $root = $config['root'] ?? $config['prefix'] ?? '';

        return new Filesystem(new AwsS3V3Adapter($client, $config['bucket'], $root));
    }
}
