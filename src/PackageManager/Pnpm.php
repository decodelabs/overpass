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

class Pnpm implements PackageManager
{
    public string $name = 'pnpm';
    public string $binary = 'pnpm';

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

    protected function runCommand(
        Project $project,
        string $command,
        string $name,
        string ...$args
    ): bool {
        return Systemic::command([$project->getBinaryPath($this->binary), $command, $name, ...$args])
            ->setWorkingDirectory($project->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
    }

    public function runScript(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->runCommand($project, 'run', $name, ...$args);
    }

    public function runExecutable(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->runCommand($project, 'exec', $name, ...$args);
    }

    public function runPackage(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->runCommand($project, 'dlx', $name, ...$args);
    }

    public function install(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'add', ...$packages);
    }

    public function installDev(
        Project $project,
        string ...$packages
    ): bool {
        return $this->run($project, 'add', '-D', ...$packages);
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
        return $this->run($project, 'remove', '-D', ...$packages);
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
