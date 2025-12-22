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
        $client = $this->createClient($config);

        // Support both 'root' (from config/backup.php) and 'prefix' (from Volume database)
        $root = $config['root'] ?? $config['prefix'] ?? '';

        return new Filesystem(new AwsS3V3Adapter($client, $config['bucket'], $root));
    }

    /**
     * Generate a presigned URL for downloading a file from S3
     */
    public function getPresignedUrl(array $config, string $path, int $expiresInMinutes = 60): string
    {
        $client = $this->createClient($config);

        $command = $client->getCommand('GetObject', [
            'Bucket' => $config['bucket'],
            'Key' => $path,
        ]);

        $request = $client->createPresignedRequest($command, "+{$expiresInMinutes} minutes");

        return (string) $request->getUri();
    }

    private function createClient(array $config): S3Client
    {
        return new S3Client([
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
            'region' => $config['region'],
            'version' => $config['version'] ?? 'latest',
            'endpoint' => $config['endpoint'] ?? null,
            'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
        ]);
    }
}
