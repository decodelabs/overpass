<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\Dir;
use DecodeLabs\Atlas\File;
use DecodeLabs\Exceptional;
use DecodeLabs\Monarch;
use DecodeLabs\Overpass\Runtime\Node as NodeRuntime;
use DecodeLabs\Systemic;

class Project
{
    public protected(set) Dir $rootDir;
    public protected(set) File $packageFile;

    public Runtime $runtime {
        get {
            if (!isset($this->runtime)) {
                $this->runtime = new NodeRuntime($this->systemic);
            }

            return $this->runtime;
        }
    }

    public PackageManager $packageManager {
        get {
            if (isset($this->packageManager)) {
                return $this->packageManager;
            }

            return $this->packageManager = $this->runtime->loadPackageManager($this);
        }
    }

    /**
     * @var array<string,string>
     */
    protected array $paths = [];

    public function __construct(
        ?Dir $dir,
        protected Systemic $systemic
    ) {
        $dir ??= Atlas::getDir(Monarch::getPaths()->working);
        $this->packageFile = $this->findPackageJson($dir);
        $this->rootDir = $this->packageFile->getParent() ?? $dir;
    }

    /**
     * Find package json
     */
    private function findPackageJson(
        Dir $dir
    ): File {
        $fallback = $dir->getFile('package.json');
        $count = 0;

        do {
            $file = $dir->getFile('package.json');

            if ($file->exists()) {
                return $file;
            }

            if (++$count >= 3) {
                break;
            }

            $dir = $dir->getParent();
        } while ($dir !== null);

        return $fallback;
    }


    public function isInitialised(): bool
    {
        return $this->packageFile->exists();
    }




    public function setBinaryPath(
        string $binary,
        string|File $path
    ): void {
        if ($path instanceof File) {
            $path = $path->path;
        }

        $this->paths[$binary] = $path;
    }

    public function getBinaryPath(
        string $binary
    ): string {
        if (isset($this->paths[$binary])) {
            return $this->paths[$binary];
        }

        return Monarch::getPaths()->resolve($binary);
    }

    public function removeBinaryPath(
        string $binary
    ): void {
        unset($this->paths[$binary]);
    }

    public function hasBinaryPath(
        string $binary
    ): bool {
        return isset($this->paths[$binary]);
    }


    public function run(
        string $name,
        string ...$args
    ): bool {
        return $this->runtime->run($this, $name, ...$args);
    }

    /**
     * @param string|array<mixed>|int|float|null ...$args
     */
    public function bridge(
        string|File $script,
        string|array|int|float|null ...$args
    ): mixed {
        if (!$this->isInitialised()) {
            throw Exceptional::Runtime(
                message: 'Project not inside js package'
            );
        }

        if (!$script instanceof File) {
            $script = Atlas::getFile($script);
        }

        if (!$script->exists()) {
            throw Exceptional::Runtime(
                message: 'Script ' . $script . ' does not exist'
            );
        }

        $delineator = '---overpass-' . uniqid('x-', true) . '---';

        $payload = (string)json_encode([
            'path' => (string)$script,
            'args' => $args,
            'delineator' => $delineator
        ]);

        $result = $this->runtime->executeBridge($this, $payload);

        if (!$result->wasSuccessful()) {
            $error = (string)($result->getError() ?? 'Unknown error');
            throw Exceptional::Runtime($error);
        }

        $output = $result->getOutput();
        $parts = explode($delineator, (string)$output);

        if (empty($output = array_pop($parts))) {
            throw Exceptional::Runtime(
                message: 'Bridge evaluator did not return valid JSON'
            );
        }

        /** @var array<string, mixed> */
        $output = json_decode($output, true, 512, \JSON_THROW_ON_ERROR);
        return $output['result'] ?? null;
    }


    public function runScript(
        string $name,
        string ...$args
    ): bool {
        return $this->packageManager->runScript($this, $name, ...$args);
    }

    public function runExecutable(
        string $name,
        string ...$args
    ): bool {
        return $this->packageManager->runExecutable($this, $name, ...$args);
    }

    public function runPackage(
        string $name,
        string ...$args
    ): bool {
        return $this->packageManager->runPackage($this, $name, ...$args);
    }

    public function install(
        string ...$packages
    ): bool {
        return $this->packageManager->install($this, ...$packages);
    }

    public function installDev(
        string ...$packages
    ): bool {
        return $this->packageManager->installDev($this, ...$packages);
    }

    public function uninstall(
        string ...$packages
    ): bool {
        return $this->packageManager->uninstall($this, ...$packages);
    }

    public function uninstallDev(
        string ...$packages
    ): bool {
        return $this->packageManager->uninstallDev($this, ...$packages);
    }

    public function ensureFetched(): bool
    {
        return $this->packageManager->ensureFetched($this);
    }

    public function update(): bool
    {
        return $this->packageManager->update($this);
    }
}
