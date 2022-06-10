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

use Composer\Composer;
use Composer\Config;
use Composer\Config\ConfigSourceInterface;
use Composer\Config\JsonConfigSource;
use Composer\Script\Event;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\Scripts;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;
use InvalidArgumentException;
use Iterator;
use RuntimeException;
use UnexpectedValueException;

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
        $eventProphecy->getArguments()->willReturn(['1.2.3']);

        Scripts::setVersion($eventProphecy->reveal());

        self::assertStringContainsString(
            '"gilbertsoft/typo3-config-handling-extensions": "^1.2.3"',
            $this->getFileContents('README.md')
        );
        self::assertStringContainsString(
            '"gilbertsoft/typo3-config-handling-extensions": "^1.2.3"',
            $this->getFileContents('tests/extensions/test/composer.json')
        );
        self::assertStringContainsString(
            '"gilbertsoft/typo3-config-handling-extensions": "^1.2.3"',
            $this->getFileContents('tests/unit/Fixtures/composer.json')
        );
        self::assertStringContainsString(
            '- COMPOSER_ROOT_VERSION=1.2.3',
            $this->getFileContents('.ddev/config.yaml')
        );
        self::assertStringContainsString(
            'COMPOSER_ROOT_VERSION: 1.2.3',
            $this->getFileContents('.github/workflows/continuous-integration.yml')
        );
        self::assertStringContainsString(
            '"dev-main": "1.2.x-dev"',
            $this->getFileContents('composer.json')
        );
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

    public function testGetFilesystem(): void
    {
        self::assertSame(TestScripts::testGetFilesystem(), TestScripts::testGetFilesystem());
    }

    public function testGetRootPath(): void
    {
        $jsonConfigSourceProphecy = $this->prophesize(JsonConfigSource::class);
        $jsonConfigSourceProphecy->willImplement(ConfigSourceInterface::class);
        $jsonConfigSourceProphecy->getName()->willReturn(
            self::getComposerFilesystem()->normalizePath(__DIR__ . '/../../../composer.json')
        );

        $configProphecy = $this->prophesize(Config::class);
        $configProphecy->getConfigSource()->willReturn($jsonConfigSourceProphecy->reveal());

        $composerProphecy = $this->prophesize(Composer::class);
        $composerProphecy->getConfig()->willReturn($configProphecy->reveal());

        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getComposer()->willReturn($composerProphecy->reveal());

        self::assertSame(
            self::getComposerFilesystem()->normalizePath(__DIR__ . '/../../..'),
            TestScripts::testGetRootPath($eventProphecy->reveal(), false)
        );

        self::assertSame(
            self::getComposerFilesystem()->normalizePath(__DIR__ . '/../../..'),
            TestScripts::testGetRootPath($eventProphecy->reveal(), true)
        );
    }

    public function testGetAbsoluteFilename(): void
    {
        $eventProphecy = $this->prophesize(Event::class);

        self::assertSame(
            self::getComposerFilesystem()->normalizePath(__DIR__ . '/../../../test'),
            TestScripts::testGetAbsoluteFilename($eventProphecy->reveal(), 'test')
        );
    }

    public function testGetAbsoluteFilenameThrowsOnAbsoluteFilename(): void
    {
        $jsonConfigSourceProphecy = $this->prophesize(JsonConfigSource::class);
        $jsonConfigSourceProphecy->willImplement(ConfigSourceInterface::class);
        $jsonConfigSourceProphecy->getName()->willReturn(__DIR__ . '/../../../composer.json');

        $configProphecy = $this->prophesize(Config::class);
        $configProphecy->getConfigSource()->willReturn($jsonConfigSourceProphecy->reveal());

        $composerProphecy = $this->prophesize(Composer::class);
        $composerProphecy->getConfig()->willReturn($configProphecy->reveal());

        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getComposer()->willReturn($composerProphecy->reveal());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1_654_777_710);
        $this->expectExceptionMessage(
            'The parameter filename should be relative to the root composer.json, "/tmp/test" was given.'
        );

        TestScripts::testGetAbsoluteFilename($eventProphecy->reveal(), '/tmp/test');
    }

    public function testFileGetContents(): void
    {
        foreach (['README.md', 'src/Extensions.php'] as $filename) {
            self::assertStringEqualsFile($filename, TestScripts::testFileGetContents($filename));
        }
    }

    public function testFileGetContentsThrowsOnInvalidFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1_654_777_708);
        $this->expectExceptionMessage(
            'Failed to read file "invalid".'
        );

        TestScripts::testFileGetContents('invalid');
    }

    public function testFilePutContents(): void
    {
        foreach (
            [
                'test.txt' => 'test.txt content',
                'src/test.txt' => 'src/test.txt content',
            ] as $filename => $content
        ) {
            self::assertFileDoesNotExist($filename);
            TestScripts::testFilePutContents($filename, $content);
            self::assertFileExists($filename);
            self::assertStringEqualsFile($filename, $content);
        }
    }

    public function testFilePutContentsThrowsOnInvalidFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1_654_777_709);
        $this->expectExceptionMessage(
            'Failed to write file "invalid/invalid".'
        );

        TestScripts::testFilePutContents('invalid/invalid', '');
    }

    /**
     * @dataProvider versionProvider
     */
    public function testExtractVersions(
        string $rawVersion,
        string $expectedVersion,
        string $expectedBranchVersion
    ): void {
        $version = '';
        $branchVersion = '';

        TestScripts::testExtractVersions($rawVersion, $version, $branchVersion);

        self::assertSame($expectedVersion, $version);
        self::assertSame($expectedBranchVersion, $branchVersion);
    }

    /**
     * @return Iterator<string, array<string, string>>
     */
    public function versionProvider(): Iterator
    {
        yield 'simple version' => [
            'rawVersion' => '1.2.3',
            'expectedVersion' => '1.2.3',
            'expectedBranchVersion' => '1.2',
        ];
        yield 'sem ver with prefix' => [
            'rawVersion' => 'v1.2.3-alpha',
            'expectedVersion' => '1.2.3',
            'expectedBranchVersion' => '1.2',
        ];
        yield 'high version' => [
            'rawVersion' => '999999.999999.999999',
            'expectedVersion' => '999999.999999.999999',
            'expectedBranchVersion' => '999999.999999',
        ];
    }

    public function testExtractVersionsThrowsOnMissingVersion(): void
    {
        $version = '';
        $branchVersion = '';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1_654_777_706);
        $this->expectExceptionMessage(
            'The parameter rawVersion must be not be empty.'
        );

        TestScripts::testExtractVersions('', $version, $branchVersion);
    }

    public function testExtractVersionsThrowsOnInvalidVersion(): void
    {
        $version = '';
        $branchVersion = '';

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1_654_777_707);
        $this->expectExceptionMessage(
            '"v20100102" is no valid version number.'
        );

        TestScripts::testExtractVersions('v20100102', $version, $branchVersion);
    }

    public function testReplaceVersion(): void
    {
        $jsonConfigSourceProphecy = $this->prophesize(JsonConfigSource::class);
        $jsonConfigSourceProphecy->willImplement(ConfigSourceInterface::class);
        $jsonConfigSourceProphecy->getName()->willReturn(__DIR__ . '/../../../composer.json');

        $configProphecy = $this->prophesize(Config::class);
        $configProphecy->getConfigSource()->willReturn($jsonConfigSourceProphecy->reveal());

        $composerProphecy = $this->prophesize(Composer::class);
        $composerProphecy->getConfig()->willReturn($configProphecy->reveal());

        $eventProphecy = $this->prophesize(Event::class);
        $eventProphecy->getComposer()->willReturn($composerProphecy->reveal());

        TestScripts::testReplaceVersion(
            $eventProphecy->reveal(),
            'tests/unit/Fixtures/composer.json',
            '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
            '999.888.777'
        );

        self::assertStringContainsString(
            '999.888.777',
            $this->getFileContents('tests/unit/Fixtures/composer.json')
        );

        // Test early return
        TestScripts::testReplaceVersion(
            $eventProphecy->reveal(),
            'tests/unit/Fixtures/composer.json',
            '/("gilbertsoft\/typo3-config-handling-extensions": "\^)\d+.\d+.\d+(")/',
            '999.888.777'
        );
    }

    public function testReplaceVersionThrowsOnInvalidPattern(): void
    {
        /*
        $jsonConfigSourceProphecy = $this->prophesize(JsonConfigSource::class);
        $jsonConfigSourceProphecy->willImplement(ConfigSourceInterface::class);
        $jsonConfigSourceProphecy->getName()->willReturn(__DIR__ . '/../../../composer.json');

        $configProphecy = $this->prophesize(Config::class);
        $configProphecy->getConfigSource()->willReturn($jsonConfigSourceProphecy->reveal());

        $composerProphecy = $this->prophesize(Composer::class);
        $composerProphecy->getConfig()->willReturn($configProphecy->reveal());
        */

        $eventProphecy = $this->prophesize(Event::class);
        //$eventProphecy->getComposer()->willReturn($composerProphecy->reveal());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1_654_777_711);
        $this->expectExceptionMessage(
            'Failed to replace version in "tests/unit/Fixtures/composer.json" with pattern "/.^*/".'
        );

        TestScripts::testReplaceVersion(
            $eventProphecy->reveal(),
            'tests/unit/Fixtures/composer.json',
            '/.^*/',
            ''
        );
    }
}
