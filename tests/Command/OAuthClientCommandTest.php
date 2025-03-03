<?php

declare(strict_types=1);

namespace Jot\HfShieldTest\Command;

use Jot\HfShield\Command\OAuthClientCommand;
use Jot\HfShield\Entity\Client\Client;
use Jot\HfShield\Repository\ClientRepository;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use function Hyperf\Support\make;

/**
 * @covers \Jot\HfShield\Command\OAuthClientCommand
 */
class OAuthClientCommandTest extends TestCase
{
    private OAuthClientCommand $sut;
    private ContainerInterface $container;
    private ClientRepository $repository;
    private InputInterface $input;

    /**
     * @return array<string, array<string>>
     */
    public static function provideSubCommands(): array
    {
        return [
            'list command' => ['list'],
            'create command' => ['create'],
        ];
    }

    /**
     * @test
     * @covers ::configure
     */
    public function testConfigureCommandCorrectly(): void
    {
        $this->assertEquals('oauth:client', $this->sut->getName());
        $this->assertEquals('Create an OAuth Client', $this->sut->getDescription());

        $definition = $this->sut->getDefinition();
        $this->assertTrue($definition->hasArgument('sub'));
        $this->assertTrue($definition->getArgument('sub')->isRequired());
    }

    /**
     * @test
     * @covers ::handle
     * @dataProvider provideSubCommands
     */
    public function testHandleSubCommandsCorrectly(string $subCommand): void
    {
        // Arrange
        $this->input->method('getArgument')
            ->with('sub')
            ->willReturn($subCommand);

        $this->sut->setInput($this->input);

        if ($subCommand === 'list') {
            $this->repository->method('paginate')
                ->willReturn(['data' => [
                    ['id' => 'client-1', 'name' => 'Test Client 1']
                ]]);

            $this->sut->expects($this->atLeastOnce())
                ->method('success')
                ->with('%s : %s', ['client-1', 'Test Client 1']);
        } elseif ($subCommand === 'create') {
            $this->sut->method('selectTenant')->willReturn('tenant-1');
            $this->sut->method('ask')->willReturn('Test Client');

            $this->repository->method('createNewClient')
                ->willReturn(['secret123', make(Client::class, ['data' => ['id' => 'new-client']])]);
        }

        // Act
        $this->sut->handle();
    }

    /**
     * @test
     * @covers ::list
     * @covers ::handle
     */
    public function testListClientsCorrectly(): void
    {
        // Arrange
        $clients = [
            'data' => [
                [
                    'id' => 'client-1',
                    'name' => 'Test Client 1'
                ],
                [
                    'id' => 'client-2',
                    'name' => 'Test Client 2'
                ]
            ]
        ];

        $this->repository->method('paginate')
            ->with([], 1, 1000)
            ->willReturn($clients);

        $this->input->method('getArgument')
            ->with('sub')
            ->willReturn('list');

        $this->sut->setInput($this->input);

        // Assert expectations
        $expectedCalls = [
            ['%s : %s', ['client-1', 'Test Client 1']],
            ['%s : %s', ['client-2', 'Test Client 2']]
        ];

        foreach ($expectedCalls as $call) {
            $this->sut->expects($this->once())
                ->method('success')
                ->with(...$call);
        }

        // Act
        $this->sut->handle();
    }

    /**
     * @test
     * @group unit
     * @covers \Jot\HfShield\Command\OAuthClientCommand::create
     *
     * What is being tested:
     * - Client creation process through command
     *
     * Conditions/Scenarios:
     * - Valid tenant provided
     * - Valid client name
     * - Valid redirect URI
     * - Successful client creation
     *
     * Expected results:
     * - Client should be created successfully
     * - Client ID should be displayed
     * - Client secret should be displayed
     * - Warning message about secret should be shown
     */
    public function testCreateClientSuccessfully(): void
    {
        // Arrange
        $tenant = 'tenant-1';
        $name = 'Test Client';
        $redirectUri = 'https://example.com/callback';
        $plainSecret = 'secret123';
        $clientId = 'client-123';

        $this->input->method('getArgument')
            ->with('sub')
            ->willReturn('create');

        $this->sut->setInput($this->input);

        $this->sut->method('selectTenant')
            ->willReturn($tenant);

        $this->sut->method('ask')
            ->willReturnMap([
                ['Name: <fg=yellow>(*)</>', $name],
                ['Redirect URI: <fg=yellow>(*)</>', $redirectUri]
            ]);

        $clientData = ['id' => $clientId];
        $resultClient = make(Client::class, ['data' => $clientData]);

        $this->repository->method('createNewClient')
            ->willReturn([$plainSecret, $resultClient]);

        // Assert
        $expectedMessages = [
            ['Client ID:     <fg=#FFCC00>%s</>', [$clientId]],
            ['Client Secret: <fg=#FFCC00>%s</>', [$plainSecret]],
            ['Save this secret in a safe place. You will not be able to retrieve it again.', []]
        ];

        foreach ($expectedMessages as $index => $messageData) {
            $this->sut->expects($this->at($index))
                ->method('success')
                ->with(...$messageData);
        }

        // Act
        $result = $this->sut->handle();

        // Additional Assert
        $this->assertEquals(0, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);
        $this->repository = $this->createMock(ClientRepository::class);
        $this->input = $this->createMock(InputInterface::class);

        // Configurar container para retornar o repository
        $this->container->method('get')
            ->with(ClientRepository::class)
            ->willReturn($this->repository);

        $this->sut = $this->getMockBuilder(OAuthClientCommand::class)
            ->setConstructorArgs([$this->container])
            ->onlyMethods(['ask', 'selectTenant', 'success', 'failed'])
            ->getMock();

        // Injetar repository
        $reflection = new \ReflectionClass($this->sut);
        $property = $reflection->getProperty('repository');
        $property->setAccessible(true);
        $property->setValue($this->sut, $this->repository);
    }

}