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

class Yarn implements PackageManager
{
    public string $name = 'Yarn';
    public string $binary = 'yarn';

    public function __construct(
        protected Systemic $systemic
    ) {
    }

    protected function run(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->systemic->run(
            [$project->getBinaryPath($this->binary), '--loglevel=error', $name, ...$args],
            $project->rootDir
        );
    }

    public function runScript(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->runExecutable($project, $name, ...$args);
    }

    public function runExecutable(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->systemic->command([$project->getBinaryPath($this->binary), 'run', $name, ...$args])
            ->setWorkingDirectory($project->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
    }

    public function runPackage(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->systemic->command([$project->getBinaryPath($this->binary), 'dlx', $name, ...$args])
            ->setWorkingDirectory($project->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
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
        return $this->run($project, 'add', '--dev', ...$packages);
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
        return $this->run($project, 'remove', '--dev', ...$packages);
    }

    public function ensureFetched(
        Project $project,
    ): bool {
        return $this->run($project, 'install');
    }

    public function update(
        Project $project,
    ): bool {
        return $this->run($project, 'upgrade');
    }
}
