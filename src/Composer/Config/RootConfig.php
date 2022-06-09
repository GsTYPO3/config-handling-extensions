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

namespace Gilbertsoft\TYPO3\ConfigHandling\Composer\Config;

final class RootConfig
{
    /**
     * @var string
     */
    private const EXTRA_KEY = 'gilbertsoft/typo3-config-handling-extensions';

    /**
     * @var string
     */
    private const EXTENSIONS = 'extensions';

    /**
     * @var mixed[]
     */
    private array $extra = [];

    /**
     * @var array{extensions: array<string, array{
     *   options: array<string, string|int|bool>|array{},
     *   before: array<int, string>,
     *   after: array<int, string>
     * }>}|array{}
     */
    private array $cachedComposerConfig = [];

    /**
     * @param mixed[] $extra
     */
    public function __construct(array $extra)
    {
        $this->extra = $extra;
    }

    /**
     * @return array<string, string|int|bool>
     */
    public function getOptions(string $packageName): array
    {
        return $this->getComposerConfig()[self::EXTENSIONS][$packageName]['options'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function getBefore(string $packageName): array
    {
        return $this->getComposerConfig()[self::EXTENSIONS][$packageName]['before'] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function getAfter(string $packageName): array
    {
        return $this->getComposerConfig()[self::EXTENSIONS][$packageName]['after'] ?? [];
    }

    /**
     * @return array{extensions: array<string, array{
     *   options: array<string, string|int|bool>|array{},
     *   before: array<int, string>,
     *   after: array<int, string>
     * }>}
     */
    private function getComposerConfig(): array
    {
        if ($this->cachedComposerConfig !== []) {
            return $this->cachedComposerConfig;
        }

        $rootConfig = [
            self::EXTENSIONS => [],
        ];

        foreach (array_keys($rootConfig) as $name) {
            if (
                is_array($extensionConfig = $this->extra[self::EXTRA_KEY] ?? null) &&
                !is_null($extensionConfig[$name] ?? null)
            ) {
                $rootConfig[$name] = $extensionConfig[$name];
            }
        }

        return $this->cachedComposerConfig = $rootConfig;
    }
}
