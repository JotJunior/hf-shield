<?php

declare(strict_types=1);

namespace Jot\HfShield\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Jot\HfRepository\Command\HfFriendlyLinesTrait;
use Jot\HfShield\Repository\ClientRepository;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class OAuthKeyPairsCommand extends AbstractCommand
{
    use HfFriendlyLinesTrait;

    private const USER_PROMPT_DEFAULT = 'n';
    private const USER_PROMPT_CONFIRM = 'y';
    private const KEY_CONFIG = [
        'digest_alg' => 'sha256',
        'private_key_bits' => 4096,
        'private_key_type' => OPENSSL_KEYTYPE_RSA
    ];
    private const KEY_FILES = [
        'private' => 'private.key',
        'public' => 'public.pem'
    ];
    private const DIRECTORY_PERMISSIONS = 0755;

    #[Inject]
    protected ClientRepository $repository;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('oauth:keys');
    }

    /**
     * Configures the command for creating OAuth token encryption key pairs.
     * @return void
     */
    public function configure(): void
    {
        parent::configure();
        $this->setDescription('Create an OAuth token encryption key pairs');
        $this->addOption('force', 'F', InputArgument::OPTIONAL, 'Replace current keys');
        $this->addOption('keys-path', 'P', InputArgument::OPTIONAL, BASE_PATH . '/storage/keys');
        $this->configureUsageExamples();
    }

    /**
     * Configures the usage examples for a specific command.
     * @return void
     */
    private function configureUsageExamples(): void
    {
        $this->addUsage('oauth:keys');
        $this->addUsage('oauth:keys --keys-path=/path/to/keys');
        $this->addUsage('oauth:keys --force');
    }

    /**
     * Handles the execution of the key generation process.
     * @return void
     */
    public function handle(): void
    {
        $forceOverwrite = $this->input->getArgument('force');
        $keysPath = $this->input->getArgument('keys-path');

        if (!$this->shouldProceedWithKeyGeneration($keysPath, $forceOverwrite)) {
            return;
        }

        $this->generateKeyPair($keysPath);
    }

    /**
     * Determines whether the key generation process should proceed based on the existence
     * of keys in the given directory and a force flag.
     * @param string $directory The directory in which to check for existing keys.
     * @param bool $force A flag to force key generation regardless of existing keys.
     * @return bool Returns true if key generation should proceed, otherwise false.
     */
    private function shouldProceedWithKeyGeneration(string $directory, bool $force): bool
    {
        if (!$this->keysExist($directory) || $force) {
            return true;
        }

        return $this->confirmOverwrite();
    }

    /**
     * Checks if the required key files exist in the specified directory.
     * @param string $directory The directory to check for the existence of key files.
     * @return bool Returns true if the key files exist in the specified directory, otherwise false.
     */
    private function keysExist(string $directory): bool
    {
        return file_exists(sprintf('%s/%s', $directory, self::KEY_FILES['private']));
    }

    /**
     * Prompts the user for confirmation to overwrite existing keys.
     * @return bool Returns true if the user confirms the overwrite, otherwise false.
     */
    private function confirmOverwrite(): bool
    {
        $response = $this->ask(
            'Keys already exist, overwrite? [y/n]',
            self::USER_PROMPT_DEFAULT
        );
        return $response === self::USER_PROMPT_CONFIRM;
    }

    /**
     * Generates a key pair, ensures the specified directory exists, and saves the keys to the given path.
     * @param string $path The directory path where the generated key pair will be saved.
     * @return void
     */
    private function generateKeyPair(string $path): void
    {
        $keyPair = $this->createKeyPair();
        $this->ensureDirectoryExists($path);
        $this->saveKeyPair($path, $keyPair);
    }

    /**
     * Generates a new key pair consisting of a private key and a public key.
     * @return array Returns an associative array containing the generated keys:
     *               - 'private': The private key as a string.
     *               - 'public': The public key as a string.
     */
    private function createKeyPair(): array
    {
        $resource = openssl_pkey_new(self::KEY_CONFIG);
        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource)['key'];

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }

    /**
     * Ensures that the specified directory exists, creating it if necessary.
     * @param string $path The path of the directory to check or create.
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, self::DIRECTORY_PERMISSIONS, true);
        }
    }

    /**
     * Saves a key pair to the specified path by writing the private and public keys
     * to their respective files.
     * @param string $path The directory path where the key files will be saved.
     * @param array $keyPair An associative array containing the keys with 'private'
     * and 'public' as keys.
     * @return void This method does not return a value.
     */
    private function saveKeyPair(string $path, array $keyPair): void
    {
        file_put_contents($path . '/' . self::KEY_FILES['private'], $keyPair['private']);
        file_put_contents($path . '/' . self::KEY_FILES['public'], $keyPair['public']);
    }
}
