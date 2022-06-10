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
use Composer\Util\Filesystem;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\Scripts;

/**
 * @internal
 */
final class TestScripts extends Scripts
{
    public static function testGetFilesystem(): Filesystem
    {
        return self::getFilesystem();
    }

    public static function testGetRootPath(Event $event, bool $forceConfig): string
    {
        return self::getRootPath($event, $forceConfig);
    }

    public static function testGetAbsoluteFilename(Event $event, string $filename): string
    {
        return self::getAbsoluteFilename($event, $filename);
    }

    public static function testFileGetContents(string $filename): string
    {
        return self::fileGetContents($filename);
    }

    public static function testFilePutContents(string $filename, string $content): void
    {
        self::filePutContents($filename, $content);
    }

    public static function testExtractVersions(string $rawVersion, string &$version, string &$branchVersion): void
    {
        self::extractVersions($rawVersion, $version, $branchVersion);
    }

    public static function testReplaceVersion(Event $event, string $filename, string $pattern, string $version): void
    {
        self::replaceVersion($event, $filename, $pattern, $version);
    }
}
