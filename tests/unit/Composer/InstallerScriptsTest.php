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
use Gilbertsoft\TYPO3\ConfigHandling\Composer\ExtensionsTraitBuilder;
use Gilbertsoft\TYPO3\ConfigHandling\Composer\InstallerScripts;
use Gilbertsoft\TYPO3\ConfigHandling\Tests\Unit\TestCase;
use Prophecy\Argument;
use TYPO3\CMS\Composer\Plugin\Core\ScriptDispatcher;

/**
 * @covers \Gilbertsoft\TYPO3\ConfigHandling\Composer\InstallerScripts
 */
final class InstallerScriptsTest extends TestCase
{
    public function testRegister(): void
    {
        $eventProphecy = $this->prophesize(Event::class);
        $scriptDispatcherProphecy = $this->prophesize(ScriptDispatcher::class);
        $scriptDispatcherProphecy
            ->addInstallerScript(Argument::type(ExtensionsTraitBuilder::class))
            ->shouldBeCalledOnce()
        ;

        InstallerScripts::register($eventProphecy->reveal(), $scriptDispatcherProphecy->reveal());
    }
}
