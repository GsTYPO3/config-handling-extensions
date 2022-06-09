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

namespace Gilbertsoft\TYPO3\ConfigHandling;

use Gilbertsoft\TYPO3\ConfigHandling\Extensions\ConfigProviderInterface;
use Gilbertsoft\TYPO3\ConfigHandling\Extensions\ExtensionInterface;
use Gilbertsoft\TYPO3\ConfigHandling\Extensions\ExtensionManager;
use Helhum\ConfigLoader\ConfigurationReaderFactory;
use Helhum\ConfigLoader\InvalidConfigurationFileException;
use Helhum\ConfigLoader\Reader\ConfigReaderInterface;
use Helhum\ConfigLoader\Reader\GlobFileReader;
use LogicException;

final class Extensions implements ConfigReaderInterface
{
    private ConfigurationReaderFactory $factory;

    private ExtensionManager $extensionManager;

    /**
     * @param array<string, mixed> $options
     * @phpstan-ignore-next-line to avoid unused parameter warnings
     */
    public function __construct(
        ConfigurationReaderFactory $configurationReaderFactory,
        array $options = [],
        ExtensionManager $extensionManager = null
    ) {
        $this->factory = $configurationReaderFactory;
        $this->extensionManager = $extensionManager ?? new ExtensionManager();
    }

    public function hasConfig(): bool
    {
        return $this->extensionManager->getExtensions() !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function readConfig(): array
    {
        $finalConfig = [];

        foreach (array_reverse($this->extensionManager->getExtensions(), true) as $extension) {
            $finalConfig = array_replace_recursive(
                $finalConfig,
                $this->processConfigFolder($extension),
                $this->processConfigProvider($extension)
            );
        }

        return $finalConfig;
    }

    /**
     * @return array<string, mixed>
     */
    private function processConfigProvider(ExtensionInterface $extension): array
    {
        if (($class = $extension->getClass()) === '') {
            return [];
        }

        if (!class_exists($class)) {
            throw new LogicException(sprintf(
                'Class "%s" not found in extension "%s".',
                $class,
                $extension->getName()
            ), 1_654_350_374);
        }

        $instance = new $class();
        if (!$instance instanceof ConfigProviderInterface) {
            throw new LogicException(sprintf(
                'Class "%s" does not implement ConfigProviderInterface in extension "%s".',
                $class,
                $extension->getName()
            ), 1_654_350_374);
        }

        if (!$instance->hasConfig($extension->getOptions())) {
            return [];
        }

        return $instance->getConfig($extension->getOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function processConfigFolder(ExtensionInterface $extension): array
    {
        if ($extension->getClass() !== '' && !$extension->getForceConfigDir()) {
            return [];
        }

        $globFileReader = new GlobFileReader($extension->getInstallPath() . '/config/*', $this->factory);

        if (!$globFileReader->hasConfig()) {
            return [];
        }

        try {
            return $globFileReader->readConfig();
        } catch (InvalidConfigurationFileException $invalidConfigurationFileException) {
            throw new InvalidConfigurationFileException(sprintf(
                'Configuration reader did not return an array for extension "%s".',
                $extension->getName()
            ), 1_654_212_558, $invalidConfigurationFileException);
        }
    }
}
