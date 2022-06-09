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

use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\ExtensionConfig;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\Config\RootConfig;
use LogicException;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;
use TYPO3\CMS\Core\Service\DependencyOrderingService;

final class ExtensionsTraitBuilder implements InstallerScript
{
    /**
     * @var string
     */
    private const PACKAGE_TYPE = 'typo3-config-handling-extension';

    private Filesystem $filesystem;

    private DependencyOrderingService $dependencyOrderingService;

    public function __construct(
        Filesystem $filesystem = null,
        DependencyOrderingService $dependencyOrderingService = null
    ) {
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->dependencyOrderingService = $dependencyOrderingService ?? new DependencyOrderingService();
    }

    /**
     * Entry method called in Composer post-dump-autoload hook
     */
    public function run(Event $event): bool
    {
        $composer = $event->getComposer();
        $basePath = dirname($composer->getConfig()->getConfigSource()->getName());

        $rootConfig = new RootConfig($composer->getPackage()->getExtra());

        $extensions = [];
        foreach ($this->extractPackageMapFromComposer($composer) as [$package, $installPath]) {
            if ($package->getType() !== self::PACKAGE_TYPE) {
                continue;
            }

            $name = $package->getName();
            $installPath = ($installPath !== '' ? $installPath : $basePath);

            $extensionConfig = new ExtensionConfig($package->getExtra());

            $before = array_merge_recursive(
                $extensionConfig->getBefore(),
                $rootConfig->getBefore($name),
            );
            $after = array_merge_recursive(
                $extensionConfig->getAfter(),
                $rootConfig->getAfter($name),
            );

            $extensions[$name] = [
                'install_path' => $installPath,
                'class' => $extensionConfig->getClass(),
                'force_config_dir' => $extensionConfig->getForceConfigDir(),
                'options' => $rootConfig->getOptions($name),
                'before' => $before,
                'after' => $after,
            ];
        }

        $extensions = $this->dependencyOrderingService->orderByDependencies($extensions);

        return $this->writeExtensionsTrait($extensions);
    }

    /**
     * @return array<int, array{0: PackageInterface, 1: string}>
     */
    private function extractPackageMapFromComposer(Composer $composer): array
    {
        return $composer->getAutoloadGenerator()->buildPackageMap(
            $composer->getInstallationManager(),
            $composer->getPackage(),
            $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages()
        );
    }

    /**
     * @param array<string, array{name: string, install_path: string}> $extensions
     */
    private function writeExtensionsTrait(array $extensions): bool
    {
        return $this->filesystem->filePutContentsIfModified(
            __DIR__ . '/../Extensions/ExtensionsTrait.php',
            $this->generateExtensionsTrait($extensions)
        ) !== false;
    }

    private function loadExtensionsTraitTemplate(): string
    {
        if (($template = file_get_contents(__DIR__ . '/../../res/php/ExtensionsTrait.php')) === false) {
            // @codeCoverageIgnoreStart
            throw new LogicException('ExtensionsTrait was not found!', 1_654_212_558);
            // @codeCoverageIgnoreEnd
        }

        return $template;
    }

    /**
     * @param array<string, array{name: string, install_path: string}> $extensions
     */
    private function generateExtensionsTrait(array $extensions): string
    {
        $template = $this->loadExtensionsTraitTemplate();

        $template = str_replace(
            '$installedExtensions = []',
            '$installedExtensions = ' . $this->dumpToPhpCode($extensions),
            $template
        );

        return str_replace(
            '@generated',
            sprintf('@generated by %s on %s', self::class, date('c')),
            $template
        );
    }

    /**
     * @param array<mixed> $array
     */
    private function dumpToPhpCode(array $array = [], int $level = 0): string
    {
        $lines = "[\n";
        ++$level;

        foreach ($array as $key => $value) {
            $lines .= str_repeat('    ', $level + ($level > 0 ? 1 : 0));
            $lines .= is_int($key) ? $key . ' => ' : "'" . $key . "' => ";

            if (is_array($value)) {
                if ($value !== []) {
                    $lines .= $this->dumpToPhpCode($value, $level);
                } else {
                    $lines .= "[],\n";
                }
            } elseif ($key === 'install_path' && is_string($value)) {
                $lines .= str_replace(
                    "'.'",
                    '',
                    $this->filesystem->findShortestPathCode(__DIR__ . '/../Extensions', $value, true, true)
                ) . ",\n";
            } else {
                $lines .= var_export($value, true) . ",\n";
            }
        }

        return $lines . (str_repeat('    ', $level - 1 + ($level > 0 ? 1 : 0)) . ']' . ($level - 1 == 0 ? '' : ",\n"));
    }
}
