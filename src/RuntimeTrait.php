<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Overpass;

use DecodeLabs\Systemic;
use DecodeLabs\Systemic\Controller\Custom as CustomController;
use DecodeLabs\Systemic\Result;

trait RuntimeTrait
{
    public function __construct(
        protected Systemic $systemic
    ) {
    }

    public function run(
        Project $project,
        string $name,
        string ...$args
    ): bool {
        return $this->systemic->run(
            [
                $project->getBinaryPath($this->binary),
                $name,
                ...$args
            ],
            $project->rootDir
        );
    }

    public function executeBridge(
        Project $project,
        string $payload
    ): Result {
        return $this->systemic->start(
            [$project->getBinaryPath($this->binary), __DIR__ . '/evaluate.cjs'],
            $project->rootDir,
            function (CustomController $controller) use ($payload) {
                yield $payload;
                $controller->closeInput();
                yield from $controller->capture();
            }
        );
    }
}
