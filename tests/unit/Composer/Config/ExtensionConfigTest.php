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

namespace Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\Composer\Config;

use Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\ExtensionConfig;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\ExtensionConfig
 */
final class ExtensionConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $extensionConfig = new ExtensionConfig([
            'gilbertsoft/typo3-config-handling-extension' => [
                'class' => 'TestClass',
                'force-config-dir' => true,
                'before' => ['package1', 'package2'],
                'after' => ['package3', 'package4'],
            ],
        ]);

        self::assertSame('TestClass', $extensionConfig->getClass());
        self::assertTrue($extensionConfig->getForceConfigDir());
        self::assertSame(['package1', 'package2'], $extensionConfig->getBefore());
        self::assertSame(['package3', 'package4'], $extensionConfig->getAfter());
    }
}
