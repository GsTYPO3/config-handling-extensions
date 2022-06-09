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

namespace Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\Extensions;

use Gilbertsoft\TYPO3\ConfigHandling\Extensions\Extension;
use Gilbertsoft\TYPO3\ConfigHandling\Extensions\ExtensionManager;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Extensions\ExtensionManager
 */
final class ExtensionManagerTest extends TestCase
{
    public function testGetExtensions(): void
    {
        $extensionManager = new ExtensionManager();

        self::assertArrayHasKey('gilbertsoft/typo3-config-handling-test', $extensionManager->getExtensions());

        // Repeat testing the cache now
        self::assertArrayHasKey('gilbertsoft/typo3-config-handling-test', $extensionManager->getExtensions());
    }

    public function testGetExtension(): void
    {
        $extensionManager = new ExtensionManager();

        self::assertEquals(new Extension('gilbertsoft/typo3-config-handling-test', [
            'install_path' => dirname(__DIR__, 3)
                . '/src/Extensions/../../vendor/gilbertsoft/typo3-config-handling-test',
            'class' => \Gilbertsoft\TYPO3\ConfigHandling\Extension\Test\ConfigProvider1::class,
            'force_config_dir' => false,
            'options' => [],
        ]), $extensionManager->getExtension('gilbertsoft/typo3-config-handling-test'));
    }

    public function testThrowsOnInvalidExtension(): void
    {
        $extensionManager = new ExtensionManager();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Extension "gilbertsoft/typo3-config-handling-invalid" not found.');

        $extensionManager->getExtension('gilbertsoft/typo3-config-handling-invalid');
    }
}
