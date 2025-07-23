<?php

declare(strict_types=1);
/**
 * This file is part of the hf_shield module, a package build for Hyperf framework that is responsible for OAuth2 authentication and access control.
 *
 * @author   Joao Zanon <jot@jot.com.br>
 * @link     https://github.com/JotJunior/hf-shield
 * @license  MIT
 */

namespace Jot\HfShield\Helper;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use DateTime;
use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\RequestInterface;

class UploadFileHandler
{
    private S3Client $s3Client;

    public function __construct(
        private S3ClientInterface $s3,
        private ConfigInterface $config
    ) {
    }

    public function upload(RequestInterface $request, ?string $customFilename = null): array
    {
        if (! $request->hasFile('file')) {
            return ['url' => null, 'message' => 'No file found.'];
        }

        try {
            $file = $request->file('file');
            $filename = $customFilename ?? $this->generateUniqueFilename($file->getExtension());
            $directoryPath = $this->generateDirectoryPath();
            $s3Path = $directoryPath . '/' . $filename;
            $config = $this->config->get('hf_shield', []);
            $bucketName = $config['s3_bucket_name'] ?? '';
            $bucketUrl = $config['s3_bucket_nurl'] ?? '';

            $this->s3->putObject([
                'Bucket' => $bucketName,
                'Key' => $s3Path,
                'Body' => $file->getStream(),
                'ACL' => 'public-read',
                'ContentType' => $file->getType(),
            ]);

            $imageUrl = sprintf('%s/%s/%s', $bucketUrl, $bucketName, $s3Path);

            return [
                'url' => $imageUrl,
                'path' => $s3Path,
            ];
        } catch (S3Exception $e) {
            throw new Exception('Error uploading image to S3: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error processing image: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique filename for the file.
     */
    private function generateUniqueFilename(string $extension): string
    {
        return uniqid('file_', true) . '.' . $extension;
    }

    /**
     * Generate directory path in YYYY/MM/DD format.
     */
    private function generateDirectoryPath(): string
    {
        $now = new DateTime();
        return $now->format('Y/m/d');
    }
}
