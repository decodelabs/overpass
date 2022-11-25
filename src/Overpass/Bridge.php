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
use DecodeLabs\Terminus\Session;

class Bridge
{
    protected ?Session $io = null;
    protected Context $context;

    /**
     * Init with path and session
     */
    public function __construct(
        string|Dir|Context $context,
        ?Session $session = null
    ) {
        // Install path
        if (!$context instanceof Context) {
            if (!$context instanceof Dir) {
                $context = Atlas::dir($context);
            }

            $context = new Context($context);
        }

        $this->context = $context;
        $this->context->rootDir->ensureExists();


        // Terminus
        $this->io = $session;
    }


    /**
     * Set node path
     *
     * @return $this
     */
    public function setNodePath(
        string|File $path
    ): static {
        $this->context->setNodePath($path);
        return $this;
    }


    /**
     * Get node path
     */
    public function getNodePath(): File
    {
        return $this->context->getNodePath();
    }


    /**
     * Set npm path
     *
     * @return $this
     */
    public function setNpmPath(
        string|File $path
    ): static {
        $this->context->setNpmPath($path);
        return $this;
    }


    /**
     * Get npm path
     */
    public function getNpmPath(): File
    {
        return $this->context->getNpmPath();
    }



    /**
     * Ensure a package is installed
     *
     * @return $this
     */
    public function ensurePackage(string $packageName): static
    {
        $packageFile = $this->context->rootDir->getFile('node_modules/' . $packageName . '/package.json');

        if ($packageFile->exists()) {
            return $this;
        }

        $this->context->install($packageName);

        /** @phpstan-ignore-next-line */
        if (!$packageFile->exists()) {
            throw Exceptional::Runtime('NPM install failed: ' . $packageName);
        }

        /** @phpstan-ignore-next-line */
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

        $result = Systemic::$process->newLauncher($this->getNodePath(), [__DIR__ . '/evaluate.js'])
            ->setWorkingDirectory($this->context->rootDir)
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
        $this->context->rootDir->getFile('package.json')->delete();
        $this->context->rootDir->getFile('package-lock.json')->delete();
        $this->context->rootDir->getDir('node_modules')->delete();

        return $this;
    }
}
