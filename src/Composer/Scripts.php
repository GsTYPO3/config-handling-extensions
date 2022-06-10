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

namespace Gilbertsoft\TYPO3\ConfigHandling\Composer;

use Composer\Script\Event;
use Composer\Semver\VersionParser;
use RuntimeException;
use UnexpectedValueException;

/**
 * @internal
 */
final class Scripts
{
    /**
     * @throws UnexpectedValueException
     */
    private static function extractVersions(string $rawVersion, string &$version, string &$branchVersion): void
    {
        if ($rawVersion === '') {
            throw new UnexpectedValueException(
                'A valid version number must be provided as argument e.g. `composer set-version 1.2.3`.',
                1_654_777_706
            );
        }

        $normalizedVersion = (new VersionParser())->normalize($rawVersion);

        if (preg_match('#^(\d+)\.(\d+)\.(\d+)#', $normalizedVersion, $matches) === false) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException(sprintf('"%s" is no valid version number.', $rawVersion), 1_654_777_707);
            // @codeCoverageIgnoreEnd
        }

        $version = sprintf('%d.%d.%d', $matches[1], $matches[2], $matches[3]);
        $branchVersion = sprintf('%d.%d.x-dev', $matches[1], $matches[2]);
    }

    /**
     * @throws RuntimeException
     */
    private static function replaceVersion(string $filename, string $pattern, string $version): void
    {
        if (($currentContent = file_get_contents($filename)) === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf('"%s" could not be read.', $filename), 1_654_777_708);
            // @codeCoverageIgnoreEnd
        }

        $content = preg_replace($pattern, '${1}' . $version . '${2}', $currentContent);

        if ($currentContent === $content) {
            return;
        }

        if (file_put_contents($filename, $content) === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf('"%s" could not be written.', $filename), 1_654_777_709);
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public static function setVersion(Event $event): void
    {
        $version = '';
        $branchVersion = '';

        self::extractVersions($event->getArguments()[0] ?? '', $version, $branchVersion);

        self::replaceVersion(
            __DIR__ . '/../../README.md',
            '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
            $version
        );

        self::replaceVersion(
            __DIR__ . '/../../tests/extensions/test/composer.json',
            '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
            $version
        );

        self::replaceVersion(
            __DIR__ . '/../../tests/unit/Fixtures/composer.json',
            '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
            $version
        );

        self::replaceVersion(
            __DIR__ . '/../../.ddev/config.yaml',
            '/(- COMPOSER_ROOT_VERSION=)\d+.\d+.\d+()/',
            $version
        );

        self::replaceVersion(
            __DIR__ . '/../../.github/workflows/continuous-integration.yml',
            '/(COMPOSER_ROOT_VERSION: )\d+.\d+.\d+()/',
            $version
        );

        self::replaceVersion(
            __DIR__ . '/../../composer.json',
            '/("dev-main": ")\d+.\d+.x-dev(")/',
            $branchVersion
        );
    }
}
