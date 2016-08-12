<?php

namespace Test\Sqon\Console\Command;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use Sqon\Builder\ConfigurationInterface;
use Sqon\Console\Command\AbstractBuildCommand;
use Sqon\Console\Helper\ConfigHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Test\Sqon\Test\TempTrait;

/**
 * Verifies that the abstract build command functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \Sqon\Console\Command\AbstractBuildCommand
 */
class AbstractBuildCommandTest extends TestCase
{
    use TempTrait;

    /**
     * The command mock.
     *
     * @var AbstractBuildCommand|MockObject
     */
    private $command;

    /**
     * Verify that the configuration option is added to the command.
     */
    public function testConfigOptionIsAddedToCommandDefinition()
    {
        self::assertTrue(
            $this->command->getDefinition()->hasOption('config'),
            'The configuration option was not added.'
        );
    }

    /**
     * Verify that the configuration manager can be retrieved.
     */
    public function testRetrieveConfigurationHelper()
    {
        $file = $this->createTempFile();
        $input = new ArrayInput(
            [
                '--config' => $file
            ]
        );

        $input->bind($this->command->getDefinition());

        $get = function () use (&$input) {
            return $this->getConfig($input);
        };

        $get = $get->bindTo(
            $this->command,
            AbstractBuildCommand::class
        );

        self::assertInstanceOf(
            ConfigurationInterface::class,
            $get(),
            'The configuration manager was not returned.'
        );
    }

    /**
     * Creates a new command mock.
     */
    protected function setUp()
    {
        $this->command = $this
            ->getMockBuilder(AbstractBuildCommand::class)
            ->setConstructorArgs(['abstract'])
            ->setMethods([])
            ->getMockForAbstractClass()
        ;

        $set = new HelperSet();
        $set->set(new ConfigHelper());

        $this->command->setHelperSet($set);
    }

    /**
     * Deletes the temporary paths.
     */
    protected function tearDown()
    {
        $this->deleteTempPaths();
    }
}
