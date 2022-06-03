<?php

declare(strict_types=1);

/*
 * This file is part of the gilbertsoft/typo3-config-handling-extensions package.
 *
 * Copyright (C) 2022  Gilbertsoft LLC (gilbertsoft.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit;

use Gilbertsoft\TYPO3\ConfigHandling\Extensions;
use Gilbertsoft\TYPO3\ConfigHandling\Extensions\Extension;
use Gilbertsoft\TYPO3\ConfigHandling\Extensions\ExtensionManager;
use Helhum\ConfigLoader\ConfigurationReaderFactory;
use Helhum\ConfigLoader\InvalidConfigurationFileException;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Extensions
 */
final class ExtensionsTest extends TestCase
{
    public function testHasConfig(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        // Test no extensions
        $extensionManagerProphecy->getExtensions()->willReturn([]);

        self::assertFalse($extensions->hasConfig());

        // Test one extension
        $extensionManagerProphecy->getExtensions()->willReturn([
            'extensions1' => new Extension('extensions1', [
                'install_path' => '',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        self::assertTrue($extensions->hasConfig());
    }

    public function testNoExtensions(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([]);

        self::assertSame([], $extensions->readConfig());
    }

    public function testConfigProvider(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\ConfigProvider1::class,
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        self::assertSame([
            'TEST' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key3' => 'ConfigProvider',
                'Key4' => 'ConfigProvider',
            ],
            'TEST_CLASS' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key3' => 'ConfigProvider',
            ],
        ], $extensions->readConfig());
    }

    public function testConfigProviderAndConfigFiles(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\ConfigProvider1::class,
                'force_config_dir' => true,
                'options' => [],
            ]),
        ]);

        self::assertSame([
            'TEST' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key5' => 'config.yaml',
                'Key7' => 'config.php',
                'Key3' => 'ConfigProvider',
                'Key6' => 'config.yaml',
                'Key4' => 'ConfigProvider',
            ],
            'TEST_PHP' => [
                'Key1' => 'config.php',
                'Key2' => 'config.php',
                'Key3' => 'config.php',
            ],
            'TEST_YAML' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.yaml',
                'Key3' => 'config.yaml',
            ],
            'TEST_CLASS' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key3' => 'ConfigProvider',
            ],
        ], $extensions->readConfig());
    }

    public function testNoConfigProvider(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        self::assertSame([
            'TEST' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.php',
                'Key5' => 'config.yaml',
                'Key7' => 'config.php',
                'Key3' => 'config.yaml',
                'Key6' => 'config.yaml',
            ],
            'TEST_PHP' => [
                'Key1' => 'config.php',
                'Key2' => 'config.php',
                'Key3' => 'config.php',
            ],
            'TEST_YAML' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.yaml',
                'Key3' => 'config.yaml',
            ],
        ], $extensions->readConfig());
    }

    public function testLoadingOrder(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test1' => new Extension('test1', [
                'install_path' => __DIR__ . '/../extensions/test1',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
                'before' => [],
                'after' => [],
            ]),
            'test2' => new Extension('test2', [
                'install_path' => __DIR__ . '/../extensions/test2',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
                'before' => [],
                'after' => [],
            ]),
            'test3' => new Extension('test3', [
                'install_path' => __DIR__ . '/../extensions/test3',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
                'before' => [],
                'after' => [],
            ]),
        ]);

        self::assertSame([
            'TEST' => [
                'Key1' => 'k1.test1',
                'Key2' => 'k2.test2',
                'Key3' => 'k3.test3',
            ],
            'TEST3' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.yaml',
                'Key3' => 'config.yaml',
            ],
            'TEST2' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.yaml',
                'Key3' => 'config.yaml',
            ],
            'TEST1' => [
                'Key1' => 'config.yaml',
                'Key2' => 'config.yaml',
                'Key3' => 'config.yaml',
            ],
        ], $extensions->readConfig());
    }

    public function testConfigProviderWithNoConfig(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\ConfigProvider2::class,
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        self::assertSame([], $extensions->readConfig());
    }

    public function testConfigFilesWithNoConfig(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test-empty' => new Extension('test-empty', [
                'install_path' => __DIR__ . '/../extensions/test-empty',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        self::assertSame([], $extensions->readConfig());
    }

    public function testThrowsOnMissingClass(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => 'Gilbertsoft\\TYPO3\\ConfigHandling\\Extension\\Test\\MissingConfigProvider',
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Class "Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\MissingConfigProvider"'
            . ' not found in extension "test".'
        );

        $extensions->readConfig();
    }

    public function testThrowsOnInvalidClass(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test' => new Extension('test', [
                'install_path' => __DIR__ . '/../extensions/test',
                'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\InvalidConfigProvider::class,
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Class "Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\InvalidConfigProvider"'
            . ' does not implement ConfigProviderInterface in extension "test".'
        );

        $extensions->readConfig();
    }

    public function testThrowsOnInvalidConfig(): void
    {
        $configurationReaderFactory = new ConfigurationReaderFactory();
        $extensionManagerProphecy = $this->prophesize(ExtensionManager::class);

        $extensions = new Extensions(
            $configurationReaderFactory,
            [],
            $extensionManagerProphecy->reveal()
        );

        $extensionManagerProphecy->getExtensions()->willReturn([
            'test-invalid' => new Extension('test-invalid', [
                'install_path' => __DIR__ . '/../extensions/test-invalid',
                'class' => '',
                'force_config_dir' => false,
                'options' => [],
            ]),
        ]);

        $this->expectException(InvalidConfigurationFileException::class);
        $this->expectExceptionMessage('Configuration reader did not return an array for extension "test-invalid".');

        $extensions->readConfig();
    }
}
