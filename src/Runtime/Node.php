<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass\Runtime;

use DecodeLabs\Overpass\PackageManager;
use DecodeLabs\Overpass\PackageManager\Npm;
use DecodeLabs\Overpass\PackageManager\Pnpm;
use DecodeLabs\Overpass\PackageManager\Yarn;
use DecodeLabs\Overpass\Project;
use DecodeLabs\Overpass\Runtime;
use DecodeLabs\Overpass\RuntimeTrait;

class Node implements Runtime
{
    use RuntimeTrait;

    public string $name { get => 'Node.js'; }
    public string $binary { get => 'node'; }

    public function loadPackageManager(
        Project $project
    ): PackageManager {
        if($project->rootDir->getFile('pnpm-lock.yaml')->exists()) {
            return new Pnpm();
        }

        if($project->rootDir->getFile('package-lock.json')->exists()) {
            return new Npm();
        }

        if($project->rootDir->getFile('yarn.lock')->exists()) {
            return new Yarn();
        }

        return new Npm();
    }
}
