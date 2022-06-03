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

use Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\RootConfig;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\RootConfig
 */
final class RootConfigTest extends TestCase
{
    public function testConfig(): void
    {
        $rootConfig = new RootConfig([
            'gilbertsoft/typo3-config-handling-extensions' => [
                'extensions' => [
                    'package1' => [
                        'options' => [
                            'option1' => 'test',
                            'option2' => true,
                            'option3' => 1,
                        ],
                        'before' => ['package1', 'package2'],
                        'after' => ['package3', 'package4'],
                    ],
                    'package2' => [
                        'after' => ['package1', 'package2'],
                        'before' => ['package3', 'package4'],
                    ],
                ],
            ],
        ]);

        self::assertSame([
            'option1' => 'test',
            'option2' => true,
            'option3' => 1,
        ], $rootConfig->getOptions('package1'));
        self::assertSame(['package1', 'package2'], $rootConfig->getBefore('package1'));
        self::assertSame(['package3', 'package4'], $rootConfig->getAfter('package1'));

        self::assertSame([], $rootConfig->getOptions('package2'));
        self::assertSame(['package3', 'package4'], $rootConfig->getBefore('package2'));
        self::assertSame(['package1', 'package2'], $rootConfig->getAfter('package2'));
    }
}
