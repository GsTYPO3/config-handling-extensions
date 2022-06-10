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
use Composer\Util\Filesystem;
use Composer\Util\Platform;
use InvalidArgumentException;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * @noRector \Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector
 */
class Scripts
{
    private static ?Filesystem $filesystem = null;

    protected static function getFilesystem(): Filesystem
    {
        if (self::$filesystem === null) {
            self::$filesystem = new Filesystem();
        }

        return self::$filesystem;
    }

    /**
     * @param bool $forceConfig Forces to read the path from the config instead of cwd.
     */
    protected static function getRootPath(Event $event, bool $forceConfig = false): string
    {
        if (!$forceConfig) {
            // @todo replace with Platform::getCwd(true) once Composer lower 2.3 is not supported anymore
            // return Platform::getCwd(true);
            if (($cwd = getcwd()) === false) {
                return '';
            }

            return $cwd;
        }

        return dirname($event->getComposer()->getConfig()->getConfigSource()->getName());
    }

    /**
     * @throws UnexpectedValueException
     */
    protected static function getAbsoluteFilename(Event $event, string $filename): string
    {
        if (self::getFilesystem()->isAbsolutePath($filename)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The parameter filename should be relative to the root composer.json, "%s" was given.',
                    $filename
                ),
                1_654_777_710
            );
        }

        return self::getRootPath($event) . '/' . $filename;
    }

    /**
     * @throws RuntimeException
     */
    protected static function fileGetContents(string $filename): string
    {
        try {
            if (($content = file_get_contents($filename)) === false) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException();
                // @codeCoverageIgnoreEnd
            }

            return $content;
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                sprintf('Failed to read file "%s".', $filename),
                1_654_777_708,
                $throwable
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    protected static function filePutContents(string $filename, string $content): void
    {
        try {
            if (file_put_contents($filename, $content) === false) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException();
                // @codeCoverageIgnoreEnd
            }
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                sprintf('Failed to write file "%s".', $filename),
                1_654_777_709,
                $throwable
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    protected static function extractVersions(string $rawVersion, string &$version, string &$branchVersion): void
    {
        if ($rawVersion === '') {
            throw new InvalidArgumentException(
                'The parameter rawVersion must be not be empty.',
                1_654_777_706
            );
        }

        $normalizedVersion = (new VersionParser())->normalize($rawVersion);

        if (preg_match('#^(\d+)\.(\d+)\.(\d+)#', $normalizedVersion, $matches) === false || count($matches) !== 4) {
            throw new UnexpectedValueException(sprintf('"%s" is no valid version number.', $rawVersion), 1_654_777_707);
        }

        $version = sprintf('%d.%d.%d', $matches[1], $matches[2], $matches[3]);
        $branchVersion = sprintf('%d.%d', $matches[1], $matches[2]);
    }

    /**
     * @param string $filename File name relative to the root composer.json.
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    protected static function replaceVersion(Event $event, string $filename, string $pattern, string $version): void
    {
        $currentContent = self::fileGetContents($filename);

        try {
            $content = preg_replace($pattern, '${1}' . $version . '${2}', $currentContent);

            if ($content === null) {
                throw new UnexpectedValueException();
            }
        } catch (Throwable $throwable) {
            throw new UnexpectedValueException(
                sprintf('Failed to replace version in "%s" with pattern "%s".', $filename, $pattern),
                1_654_777_711,
                $throwable
            );
        }

        if ($currentContent === $content) {
            return;
        }

        self::filePutContents($filename, $content);
    }

    /**
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    public static function setVersion(Event $event): void
    {
        $version = '';
        $branchVersion = '';

        try {
            self::extractVersions($event->getArguments()[0] ?? '', $version, $branchVersion);
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new UnexpectedValueException(
                'A valid version number must be provided as argument e.g. `composer set-version 1.2.3`.',
                1_654_777_706,
                $invalidArgumentException
            );
        }

        foreach (
            [
                'README.md' =>
                    '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
                'tests/extensions/test/composer.json' =>
                    '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
                'tests/unit/Fixtures/composer.json' =>
                    '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
                '.ddev/config.yaml' =>
                    '/(- COMPOSER_ROOT_VERSION=)\d+.\d+.\d+()/',
                '.github/workflows/continuous-integration.yml' =>
                    '/(COMPOSER_ROOT_VERSION: )\d+.\d+.\d+()/',
            ] as $filename => $pattern
        ) {
            self::replaceVersion(
                $event,
                $filename,
                $pattern,
                $version
            );
        }

        foreach (
            [
                'composer.json' =>
                    '/("dev-main": ")\d+.\d+(.x-dev")/',
            ] as $filename => $pattern
        ) {
            self::replaceVersion(
                $event,
                $filename,
                $pattern,
                $branchVersion
            );
        }
    }
}
