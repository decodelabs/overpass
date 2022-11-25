<?php

/**
 * @package Overpass
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */

namespace DecodeLabs\Overpass
{
    use DecodeLabs\Overpass;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Context::class, Overpass::class);
}
