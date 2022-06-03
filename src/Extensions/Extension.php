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

namespace Gilbertsoft\TYPO3\ConfigHandling\Extensions;

final class Extension implements ExtensionInterface
{
    private string $name;

    private string $installPath;

    private string $class;

    private bool $forceConfig;

    /**
     * @var array<string, string|int|bool>|array{}
     */
    private array $options = [];

    /**
     * @param array{
     *   install_path: string,
     *   class: string,
     *   force_config_dir: bool,
     *   options: array<string, string|int|bool>|array{}
     * } $properties
     */
    public function __construct(
        string $name,
        array $properties
    ) {
        $this->name = $name;
        $this->installPath = $properties['install_path'];
        $this->class = $properties['class'];
        $this->forceConfig = $properties['force_config_dir'];
        $this->options = $properties['options'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInstallPath(): string
    {
        return $this->installPath;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getForceConfigDir(): bool
    {
        return $this->forceConfig;
    }

    /**
     * @return array<string, string|int|bool>|array{}
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
