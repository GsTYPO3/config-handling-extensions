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
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Extensions\Extension
 */
final class ExtensionTest extends TestCase
{
    public function testConfig(): void
    {
        $extension = new Extension(
            'package1',
            [
                'install_path' => 'path/to/package',
                'class' => 'TestClass',
                'force_config_dir' => true,
                'options' => [
                    'option1' => 'test',
                    'option2' => true,
                    'option3' => 1,
                ],
                'before' => ['package1', 'package2'],
                'after' => ['package3', 'package4'],
            ]
        );

        self::assertSame('package1', $extension->getName());
        self::assertSame('TestClass', $extension->getClass());
        self::assertSame('path/to/package', $extension->getInstallPath());
        self::assertTrue($extension->getForceConfigDir());
        self::assertSame([
            'option1' => 'test',
            'option2' => true,
            'option3' => 1,
        ], $extension->getOptions());
    }
}
