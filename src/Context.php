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
use DecodeLabs\Overpass;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus\Session;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin;

class Context
{
    #[Plugin]
    public Dir $runDir;

    #[Plugin]
    public Dir $rootDir;

    #[Plugin]
    public File $packageFile;

    protected File $nodeBin;
    protected File $npmBin;
    protected File $npxBin;

    protected ?Session $io = null;

    public function __construct(
        ?Dir $runDir = null
    ) {
        if (!$runDir) {
            if (false === ($dir = getcwd())) {
                throw Exceptional::Runtime('Unable to get current working directory');
            }

            $runDir = Atlas::dir($dir);
            $this->packageFile = $runDir->getFile('package.json');
        }

        $this->runDir = $runDir;

        if (!isset($this->packageFile)) {
            $this->packageFile = $this->findPackageJson();
        }

        $this->rootDir = $this->packageFile->getParent() ?? clone $runDir;
    }

    /**
     * Find package json
     */
    protected function findPackageJson(): File
    {
        $dir = $this->runDir;
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

        return $this->runDir->getFile('package.json');
    }


    /**
     * Is in package
     */
    public function isInPackage(): bool
    {
        return $this->packageFile->exists();
    }


    /**
     * Set session
     *
     * @return $this
     */
    public function setSesson(
        ?Session $session
    ): static {
        $this->io = $session;
        return $this;
    }

    /**
     * Get session
     */
    public function getSession(): ?Session
    {
        return $this->io;
    }


    /**
     * Set node path
     *
     * @return $this
     */
    public function setNodePath(
        string|File $path
    ): static {
        if (!$path instanceof File) {
            $path = Atlas::file($path);
        }

        if ($path->getPath() === 'node') {
            $path = Atlas::file((string)Systemic::$os->which('node'));
        } elseif (!$path->exists()) {
            throw Exceptional::Setup('Node binary could not be found');
        }

        $this->nodeBin = $path;
        return $this;
    }


    /**
     * Get node path
     */
    public function getNodePath(): File
    {
        if (!isset($this->nodeBin)) {
            $this->setNodePath('node');
        }

        return $this->nodeBin;
    }


    /**
     * Set npm path
     *
     * @return $this
     */
    public function setNpmPath(
        string|File $path
    ): static {
        if (!$path instanceof File) {
            $path = Atlas::file($path);
        }

        if ($path->getPath() === 'npm') {
            $path = Atlas::file((string)Systemic::$os->which('npm'));
        } elseif (!$path->exists()) {
            throw Exceptional::Setup('NPM binary could not be found');
        }

        $this->npmBin = $path;
        return $this;
    }

    /**
     * Get npm path
     */
    public function getNpmPath(): File
    {
        if (!isset($this->npmBin)) {
            $this->setNpmPath('npm');
        }

        return $this->npmBin;
    }



    /**
     * Set npx path
     *
     * @return $this
     */
    public function setNpxPath(
        string|File $path
    ): static {
        if (!$path instanceof File) {
            $path = Atlas::file($path);
        }

        if ($path->getPath() === 'npx') {
            $path = Atlas::file((string)Systemic::$os->which('npx'));
        } elseif (!$path->exists()) {
            throw Exceptional::Setup('NPX binary could not be found');
        }

        $this->npxBin = $path;
        return $this;
    }

    /**
     * Get npx path
     */
    public function getNpxPath(): File
    {
        if (!isset($this->npxBin)) {
            $this->setNpxPath('npx');
        }

        return $this->npxBin;
    }


    /**
     * Run node command
     */
    public function run(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            [(string)$this->getNodePath(), $name, ...$args],
            $this->rootDir
        );
    }


    /**
     * Run npm script
     */
    public function runScript(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            [(string)$this->getNpmPath(), 'run', $name, ...$args],
            $this->rootDir
        );
    }


    /**
     * Run npm script
     */
    public function runServerScript(
        string $name,
        string ...$args
    ): bool {
        return Systemic::command([(string)$this->getNpmPath(), 'run', $name, ...$args])
            ->setWorkingDirectory($this->rootDir)
            ->addSignal('SIGINT', 'SIGTERM', 'SIGQUIT')
            ->run();
    }


    /**
     * Run js file over bridge
     *
     * @param string|array<mixed>|int|float|null ...$args
     */
    public function bridge(
        string|File $script,
        string|array|int|float|null ...$args
    ): mixed {
        return (new Bridge($this))->run($script, ...$args);
    }


    /**
     * Install package
     */
    public function install(
        string ...$packages
    ): bool {
        return $this->runNpm('install', ...$packages);
    }

    /**
     * Install dev package
     */
    public function installDev(
        string ...$packages
    ): bool {
        return $this->runNpm(...['install', ...$packages, '--save-dev']);
    }

    /**
     * Remove package
     */
    public function uninstall(
        string ...$packages
    ): bool {
        return $this->runNpm('remove', ...$packages);
    }

    /**
     * Remove package
     */
    public function uninstallDev(
        string ...$packages
    ): bool {
        return $this->runNpm(...['remove', ...$packages, '--save-dev']);
    }


    /**
     * Prepare package install name
     */
    public function preparePackageInstallName(
        string $name,
        ?string $version = null
    ): string {
        $pkg = $name;

        if ($version !== null) {
            $pkg .= '@' . $version;
        }

        return $pkg;
    }


    /**
     * Run npm
     */
    public function runNpm(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            [(string)$this->getNpmPath(), '--loglevel=error', $name, ...$args],
            $this->rootDir
        );
    }


    public function runNpx(
        string $name,
        string ...$args
    ): bool {
        return Systemic::run(
            [(string)$this->getNpxPath(), $name, ...$args],
            $this->rootDir
        );
    }
}


// Register the Veneer facade
Veneer::register(Context::class, Overpass::class);
