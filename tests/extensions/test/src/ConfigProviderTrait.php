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

namespace Gilbertsoft\TYPO3\ConfigHandling\Extension\Test;

trait ConfigProviderTrait
{
    /**
     * @return array{
     *   TEST: array{Key1: string, Key2: string, Key3: string, Key4: string},
     *   TEST_CLASS: array{Key1: string, Key2: string, Key3: string}
     * }
     */
    public function getConfig(array $options): array
    {
        return [
            'TEST' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key3' => 'ConfigProvider',
                'Key4' => 'ConfigProvider',
            ],
            'TEST_CLASS' => [
                'Key1' => 'ConfigProvider',
                'Key2' => 'ConfigProvider',
                'Key3' => 'ConfigProvider',
            ],
        ];
    }
}
