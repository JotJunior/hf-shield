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
use DateTime;
use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;

class Base64ImageHandler
{
    private array $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class)->get('hf_shield', []);
    }

    /**
     * Upload base64 image to S3 bucket.
     *
     * @param string $base64Image Base64 encoded image
     * @param null|string $customFilename Custom filename (optional)
     * @return array Returns array with url and path of uploaded image
     * @throws Exception
     */
    public function uploadToS3(string $base64Image, ?string $customFilename = null): array
    {
        try {
            // Decode base64 image to binary
            $binaryImage = $this->decodeBase64ImageToBinary($base64Image);

            // Get image extension and generate filename
            $extension = $this->getImageExtension($base64Image);
            $filename = $customFilename ?? $this->generateUniqueFilename($extension);

            // Generate directory path in YYYY/MM/DD format
            $directoryPath = $this->generateDirectoryPath();

            // Full path for the file in the bucket
            $s3Path = $directoryPath . '/' . $filename;

            // Create S3 client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $this->config['s3_bucket_region'],
                'endpoint' => $this->config['s3_bucket_url'],
                'credentials' => [
                    'key' => $this->config['s3_bucket_access_key'],
                    'secret' => $this->config['s3_bucket_secret_key'],
                ],
                'use_path_style_endpoint' => true, // Required for DigitalOcean Spaces
            ]);

            // Upload file to S3
            $s3Client->putObject([
                'Bucket' => $this->config['s3_bucket_name'],
                'Key' => $s3Path,
                'Body' => $binaryImage,
                'ACL' => 'public-read',
                'ContentType' => 'image/' . $extension,
            ]);

            // Generate public URL
            $imageUrl = $this->config['s3_bucket_url'] . '/' . $this->config['s3_bucket_name'] . '/' . $s3Path;

            return [
                'url' => $imageUrl,
                'path' => $s3Path,
                'bucket' => $this->config['s3_bucket_name'],
            ];
        } catch (S3Exception $e) {
            throw new Exception('Error uploading image to S3: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Error processing image: ' . $e->getMessage());
        }
    }

    private function decodeBase64ImageToBinary(string $base64Image): string
    {
        $cleanedBase64 = $this->removeBase64Prefix($base64Image);
        $normalizedBase64 = $this->normalizeBase64String($cleanedBase64);

        return base64_decode($normalizedBase64);
    }

    private function removeBase64Prefix(string $base64Image): string
    {
        $knownPrefixes = [
            'data:image/png;base64,',
            'data:image/jpeg;base64,',
            'data:image/jpg;base64,',
            'data:image/gif;base64,',
        ];

        return str_replace($knownPrefixes, '', $base64Image);
    }

    private function normalizeBase64String(string $base64String): string
    {
        return str_replace(' ', '+', $base64String);
    }

    /**
     * Get image extension from base64 string.
     */
    private function getImageExtension(string $base64Image): string
    {
        if (strpos($base64Image, 'data:image/png;base64,') !== false) {
            return 'png';
        }
        if (strpos($base64Image, 'data:image/jpeg;base64,') !== false || strpos($base64Image, 'data:image/jpg;base64,') !== false) {
            return 'jpg';
        }
        if (strpos($base64Image, 'data:image/gif;base64,') !== false) {
            return 'gif';
        }

        return 'jpg'; // Default extension
    }

    /**
     * Generate a unique filename for the image.
     */
    private function generateUniqueFilename(string $extension): string
    {
        return uniqid('img_', true) . '.' . $extension;
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
