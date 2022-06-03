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

namespace Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\Composer;

use Composer\Autoload\AutoloadGenerator;
use Composer\Composer;
use Composer\Config;
use Composer\Config\ConfigSourceInterface;
use Composer\Config\JsonConfigSource;
use Composer\Installer\InstallationManager;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepository;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\ExtensionsTraitBuilder;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;
use Prophecy\Argument;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Composer\ExtensionsTraitBuilder
 */
final class ExtensionsTraitBuilderTest extends TestCase
{
    public function testRun(): void
    {
        $jsonConfigSourceProphecy = $this->prophesize(JsonConfigSource::class);
        $jsonConfigSourceProphecy->willImplement(ConfigSourceInterface::class);
        $jsonConfigSourceProphecy->getName()->willReturn(__DIR__ . '/../Fixtures');

        $configProphecy = $this->prophesize(Config::class);
        $configProphecy->getConfigSource()->willReturn($jsonConfigSourceProphecy->reveal());

        $package1 = new Package('gilbertsoft/typo3-config-handling-test', 'dev-main', 'dev-main');
        $package1->setType('typo3-config-handling-extension');
        $package1->setExtra([
            'gilbertsoft/typo3-config-handling-extension' => [
                'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\ConfigProvider1::class,
            ],
        ]);
        $package2 = new Package('gilbertsoft/typo3-config-handling-dummy', 'dev-main', 'dev-main');

        $autoloadGeneratorProphecy = $this->prophesize(AutoloadGenerator::class);
        $autoloadGeneratorProphecy->buildPackageMap(
            Argument::type(InstallationManager::class),
            Argument::type(RootPackage::class),
            Argument::type('array')
        )->willReturn([
            [
                $package1,
                __DIR__ . '/../../../vendor/gilbertsoft/typo3-config-handling-test',
            ],
            [
                $package2,
                __DIR__ . '/../../../vendor/gilbertsoft/typo3-config-handling-dummy',
            ],
        ]);

        $installationManagerProphecy = $this->prophesize(InstallationManager::class);

        $rootPackageProphecy = $this->prophesize(RootPackage::class);
        $rootPackageProphecy->willImplement(RootPackageInterface::class);
        $rootPackageProphecy->getExtra()->willReturn([
            'gilbertsoft/typo3-config-handling-extensions' => [
                'extensions' => [
                    'gilbertsoft/typo3-config-handling-test' => [
                        'before' => ['gilbertsoft/typo3-config-handling-dummy'],
                    ],
                ],
            ],
        ]);

        $installedRepositoryProphecy = $this->prophesize(InstalledRepository::class);
        $installedRepositoryProphecy->willImplement(InstalledRepositoryInterface::class);
        $installedRepositoryProphecy->getCanonicalPackages()->willReturn([]);

        $repositoryManagerProphecy = $this->prophesize(RepositoryManager::class);
        $repositoryManagerProphecy->getLocalRepository()->willReturn($installedRepositoryProphecy->reveal());

        $composerProphecy = $this->prophesize(Composer::class);
        $composerProphecy->getConfig()->willReturn($configProphecy->reveal());
        $composerProphecy->getAutoloadGenerator()->willReturn($autoloadGeneratorProphecy->reveal());
        $composerProphecy->getInstallationManager()->willReturn($installationManagerProphecy->reveal());
        $composerProphecy->getPackage()->willReturn($rootPackageProphecy->reveal());
        $composerProphecy->getRepositoryManager()->willReturn($repositoryManagerProphecy->reveal());

        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getComposer()->willReturn($composerProphecy->reveal());

        $filesystemProphecy = $this->prophesize(Filesystem::class);
        $filesystemProphecy->findShortestPathCode(
            Argument::type('string'),
            Argument::type('string'),
            Argument::is(true),
            Argument::is(true)
        )->willReturn('/../../vendor/gilbertsoft/typo3-config-handling-test');
        $filesystemProphecy->filePutContentsIfModified(
            Argument::type('string'),
            Argument::type('string')
        )->willReturn(true);

        $extensionsTraitBuilder = new ExtensionsTraitBuilder($filesystemProphecy->reveal());
        self::assertTrue($extensionsTraitBuilder->run($eventProphecy->reveal()));
    }
}
