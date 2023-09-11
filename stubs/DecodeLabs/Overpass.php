<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Overpass\Context as Inst;
use DecodeLabs\Atlas\Dir as RunDirPlugin;
use DecodeLabs\Atlas\Dir as RootDirPlugin;
use DecodeLabs\Atlas\File as PackageFilePlugin;
use DecodeLabs\Terminus\Session as Ref0;

class Overpass implements Proxy
{
    use ProxyTrait;

    const VENEER = 'DecodeLabs\\Overpass';
    const VENEER_TARGET = Inst::class;

    public static Inst $instance;
    public static RunDirPlugin $runDir;
    public static RootDirPlugin $rootDir;
    public static PackageFilePlugin $packageFile;

    public static function isInPackage(): bool {
        return static::$instance->isInPackage();
    }
    public static function setSesson(?Ref0 $session): Inst {
        return static::$instance->setSesson(...func_get_args());
    }
    public static function getSession(): ?Ref0 {
        return static::$instance->getSession();
    }
    public static function setNodePath(PackageFilePlugin|string $path): Inst {
        return static::$instance->setNodePath(...func_get_args());
    }
    public static function getNodePath(): PackageFilePlugin {
        return static::$instance->getNodePath();
    }
    public static function setNpmPath(PackageFilePlugin|string $path): Inst {
        return static::$instance->setNpmPath(...func_get_args());
    }
    public static function getNpmPath(): PackageFilePlugin {
        return static::$instance->getNpmPath();
    }
    public static function setNpxPath(PackageFilePlugin|string $path): Inst {
        return static::$instance->setNpxPath(...func_get_args());
    }
    public static function getNpxPath(): PackageFilePlugin {
        return static::$instance->getNpxPath();
    }
    public static function run(string $name, string ...$args): bool {
        return static::$instance->run(...func_get_args());
    }
    public static function runScript(string $name, string ...$args): bool {
        return static::$instance->runScript(...func_get_args());
    }
    public static function runServerScript(string $name, string ...$args): bool {
        return static::$instance->runServerScript(...func_get_args());
    }
    public static function bridge(PackageFilePlugin|string $script, array|string|int|float|null ...$args): mixed {
        return static::$instance->bridge(...func_get_args());
    }
    public static function install(string ...$packages): bool {
        return static::$instance->install(...func_get_args());
    }
    public static function installDev(string ...$packages): bool {
        return static::$instance->installDev(...func_get_args());
    }
    public static function uninstall(string ...$packages): bool {
        return static::$instance->uninstall(...func_get_args());
    }
    public static function uninstallDev(string ...$packages): bool {
        return static::$instance->uninstallDev(...func_get_args());
    }
    public static function preparePackageInstallName(string $name, ?string $version = NULL): string {
        return static::$instance->preparePackageInstallName(...func_get_args());
    }
    public static function runNpm(string $name, string ...$args): bool {
        return static::$instance->runNpm(...func_get_args());
    }
    public static function runNpx(string $name, string ...$args): bool {
        return static::$instance->runNpx(...func_get_args());
    }
};
