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
use DecodeLabs\Glitch\Proxy as Glitch;
use DecodeLabs\Systemic;
use DecodeLabs\Terminus;
use DecodeLabs\Terminus\Session;

class Bridge
{
    protected Dir $installDir;
    protected File $nodeBin;
    protected File $npmBin;

    protected Session $io;

    /**
     * Init with path and session
     */
    public function __construct(
        string|Dir $installPath,
        ?Session $session = null
    ) {
        // Install path
        if (!$installPath instanceof Dir) {
            $installPath = Atlas::dir($installPath);
        }

        $this->installDir = $installPath;
        $this->installDir->ensureExists();


        // Terminus
        $this->io = $session ?? Terminus::getSession();
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
     * Ensure a package is installed
     *
     * @return $this
     */
    public function ensurePackage(string $packageName): static
    {
        if ($this->installDir->getFile('node_modules/' . $packageName . '/package.json')->exists()) {
            return $this;
        }

        $result = Systemic::$process->newLauncher($this->getNpmPath(), [
                '--loglevel=error',
                'install',
                $packageName
            ])
            ->setWorkingDirectory($this->installDir)
            ->setSession($this->io)
            ->launch();

        if ($result->hasError()) {
            throw Exceptional::Runtime(
                $result->getError()
            );
        }

        return $this;
    }



    /**
     * Run a script
     *
     * @param string|array<mixed>|int|float|null ...$args
     */
    public function run(
        string|File $script,
        string|array|int|float|null ...$args
    ): mixed {
        if (!$script instanceof File) {
            $script = Atlas::file($script);
        }

        if (!$script->exists()) {
            throw Exceptional::Runtime('Script ' . $script . ' does not exist');
        }

        $delineator = '---overpass-' . uniqid('x-', true) . '---';

        $payload = json_encode([
            'path' => (string)$script,
            'args' => $args,
            'delineator' => $delineator
        ]);

        $result = Systemic::$process->newLauncher($this->getNodePath(), [
                __DIR__ . '/evaluate.js'
            ])
            ->setWorkingDirectory($this->installDir)
            ->setDecoratable(false)
            ->setInputGenerator(function () use ($payload) {
                return $payload;
            })
            ->launch();

        $output = $result->getOutput();

        if (
            $result->hasError() &&
            empty($output)
        ) {
            $error = (string)$result->getError();
            $e = Exceptional::Runtime($error);

            if (!preg_match('/deprecat/i', $error)) {
                throw $e;
            } else {
                Glitch::logException($e);
            }
        }

        $parts = explode($delineator, (string)$output);

        if (empty($output = array_pop($parts))) {
            throw Exceptional::Runtime('Bridge evaluator did not return valid JSON');
        }

        /** @var array<string, mixed> */
        $output = json_decode($output, true);
        return $output['result'] ?? null;
    }


    /**
     * Purge
     *
     * @return $this
     */
    public function purge(): static
    {
        $this->installDir->getFile('package.json')->delete();
        $this->installDir->getFile('package-lock.json')->delete();
        $this->installDir->getDir('node_modules')->delete();

        return $this;
    }
}
