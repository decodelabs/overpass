<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass;

interface PackageManager
{
    public string $name { get; }
    public string $binary { get; }

    public function runScript(
        Project $project,
        string $name,
        string ...$args
    ): bool;

    public function runExecutable(
        Project $project,
        string $name,
        string ...$args
    ): bool;

    public function runPackage(
        Project $project,
        string $name,
        string ...$args
    ): bool;

    public function install(
        Project $project,
        string ...$packages
    ): bool;

    public function installDev(
        Project $project,
        string ...$packages
    ): bool;

    public function uninstall(
        Project $project,
        string ...$packages
    ): bool;

    public function uninstallDev(
        Project $project,
        string ...$packages
    ): bool;

    public function ensureFetched(
        Project $project
    ): bool;

    public function update(
        Project $project
    ): bool;
}
