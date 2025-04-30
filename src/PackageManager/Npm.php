<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass\PackageManager;

use DecodeLabs\Overpass\PackageManager;
use DecodeLabs\Overpass\Project;
use DecodeLabs\Systemic;

class Npm implements PackageManager
{
    public string $name = 'npm';
    public string $binary = 'npm';

    protected function run(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            [$project->getBinaryPath($this->binary), '--loglevel=error', $name, ...$args],
            $project->rootDir
        );
    }

    public function runScript(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return Systemic::command([$project->getBinaryPath($this->binary), 'run', $name, ...$args])
            ->setWorkingDirectory($project->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
    }

    public function runExecutable(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return Systemic::command([$project->getBinaryPath('npx'), $name, ...$args])
            ->setWorkingDirectory($project->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
    }

    public function runPackage(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->runExecutable($project, $name, ...$args);
    }

    public function install(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'install', ...$packages);
    }

    public function installDev(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'install', '--save-dev', ...$packages);
    }

    public function uninstall(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'remove', ...$packages);
    }

    public function uninstallDev(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'remove', '--save-dev', ...$packages);
    }

    public function ensureFetched(
        Project $project,
    ): bool {
        return $this->run($project, 'install');
    }

    public function update(
        Project $project,
    ): bool {
        return $this->run($project, 'update');
    }
}
