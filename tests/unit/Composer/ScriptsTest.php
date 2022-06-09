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

use Composer\Script\Event;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\Scripts;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;
use RuntimeException;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Composer\Scripts
 */
final class ScriptsTest extends TestCase
{
    private function getFileContents(string $filename): string
    {
        if (($content = file_get_contents(__DIR__ . '/../../../' . $filename)) === false) {
            self::fail(sprintf('File "%s" not found.', $filename));
        }

        return $content;
    }

    public function testSetVersion(): void
    {
        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getArguments()->willReturn(['v999.999.999-dev']);

        Scripts::setVersion($eventProphecy->reveal());

        self::assertStringContainsString(
            '999.999.999',
            $this->getFileContents('README.md')
        );
        self::assertStringContainsString(
            '999.999.999',
            $this->getFileContents('tests/extensions/test/composer.json')
        );
        self::assertStringContainsString(
            '999.999.999',
            $this->getFileContents('tests/unit/Fixtures/composer.json')
        );
        self::assertStringContainsString(
            '999.999.999',
            $this->getFileContents('.ddev/config.yaml')
        );
        self::assertStringContainsString(
            '999.999.999',
            $this->getFileContents('.github/workflows/continuous-integration.yml')
        );
        self::assertStringContainsString(
            '999.999.x-dev',
            $this->getFileContents('composer.json')
        );

        // Test early return
        Scripts::setVersion($eventProphecy->reveal());
    }

    public function testSetVersionThrowsOnMissingVersion(): void
    {
        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getArguments()->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'A valid version number must be provided as argument e.g. `composer set-version 1.2.3`.'
        );

        Scripts::setVersion($eventProphecy->reveal());
    }
}
