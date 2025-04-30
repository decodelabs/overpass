<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass;

use DecodeLabs\Systemic\Result;

interface Runtime
{
    public string $name { get; }
    public string $binary { get; }

    public function loadPackageManager(
        Project $project
    ): PackageManager;

    public function run(
        Project $project,
        string $name,
        string ...$args
    ): bool;

    public function executeBridge(
        Project $project,
        string $payload
    ): Result;
}
