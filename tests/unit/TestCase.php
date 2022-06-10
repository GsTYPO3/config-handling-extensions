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

use Composer\Util\Filesystem as ComposerFilesystem;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

abstract class TestCase extends BaseTestCase
{
    use ProphecyTrait;

    private static string $rootPath;

    private static string $fixturePath;

    private static string $testPath;

    private static string $templatePath;

    private static Filesystem $filesystem;

    private static ComposerFilesystem $composerFilesystem;

    public static function setUpBeforeClass(): void
    {
        self::$rootPath = \dirname(__DIR__, 2);
        self::$fixturePath = __DIR__ . '/Fixtures';
        self::$testPath = self::$rootPath . '/var/tests';
        self::$templatePath = self::$rootPath . '/templates';

        self::$filesystem = new Filesystem();
        //unlink(self::$testPath);
        self::$filesystem->mkdir(self::$testPath);

        self::$composerFilesystem = new ComposerFilesystem();
    }

    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        if (self::$filesystem->exists(self::$testPath)) {
            //$this->filesystem->remove(self::$testPath);
        }
    }

    protected static function getRootPath(): string
    {
        return self::$rootPath;
    }

    protected static function getFilename(string $filename): string
    {
        [$prefix, $filename] = \explode(':', $filename, 2);

        switch ($prefix) {
            case 'TPL':
                return self::getTemplateFilename($filename);

            case 'FIX':
                return self::getFixtureFilename($filename);

            default:
                throw new RuntimeException(\sprintf('Invalid prefix (%s).', $prefix), 1_636_451_407);
        }
    }

    protected static function getFixturePath(): string
    {
        return self::$fixturePath;
    }

    protected static function getFixtureFilename(string $filename): string
    {
        return self::$fixturePath . '/' . $filename;
    }

    protected static function getTestPath(?string $subFolder = null): string
    {
        $fs = self::getFilesystem();

        $testPath = $fs->tempnam(self::$testPath, 'test_');

        if ($subFolder !== null) {
            $testPath .= '/' . $subFolder;
        }

        $fs->remove($testPath);
        $fs->mkdir($testPath);

        return $testPath;
    }

    protected static function getTemplatePath(): string
    {
        return self::$templatePath;
    }

    protected static function getTemplateFilename(string $filename): string
    {
        return self::$templatePath . '/' . $filename;
    }

    protected static function getFilesystem(): Filesystem
    {
        return self::$filesystem;
    }

    protected static function getComposerFilesystem(): ComposerFilesystem
    {
        return self::$composerFilesystem;
    }

    /**
     * @param array<string, string> $files
     */
    protected static function createFiles(string $testPath, array $files): void
    {
        $fs = self::getFilesystem();

        foreach ($files as $target => $source) {
            $fs->copy(static::getFilename($source), $testPath . '/' . $target);
        }
    }
}
