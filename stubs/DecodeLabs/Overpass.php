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
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Terminus\Session as Ref0;

class Overpass implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Overpass';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;
    /** @var RunDirPlugin|PluginWrapper<RunDirPlugin> $runDir */
    public static RunDirPlugin|PluginWrapper $runDir;
    /** @var RootDirPlugin|PluginWrapper<RootDirPlugin> $rootDir */
    public static RootDirPlugin|PluginWrapper $rootDir;
    /** @var PackageFilePlugin|PluginWrapper<PackageFilePlugin> $packageFile */
    public static PackageFilePlugin|PluginWrapper $packageFile;

    public static function isInPackage(): bool {
        return static::$_veneerInstance->isInPackage();
    }
    public static function setSesson(?Ref0 $session): Inst {
        return static::$_veneerInstance->setSesson(...func_get_args());
    }
    public static function getSession(): ?Ref0 {
        return static::$_veneerInstance->getSession();
    }
    public static function setNodePath(PackageFilePlugin|string $path): Inst {
        return static::$_veneerInstance->setNodePath(...func_get_args());
    }
    public static function getNodePath(): PackageFilePlugin {
        return static::$_veneerInstance->getNodePath();
    }
    public static function setNpmPath(PackageFilePlugin|string $path): Inst {
        return static::$_veneerInstance->setNpmPath(...func_get_args());
    }
    public static function getNpmPath(): PackageFilePlugin {
        return static::$_veneerInstance->getNpmPath();
    }
    public static function setNpxPath(PackageFilePlugin|string $path): Inst {
        return static::$_veneerInstance->setNpxPath(...func_get_args());
    }
    public static function getNpxPath(): PackageFilePlugin {
        return static::$_veneerInstance->getNpxPath();
    }
    public static function run(string $name, string ...$args): bool {
        return static::$_veneerInstance->run(...func_get_args());
    }
    public static function runScript(string $name, string ...$args): bool {
        return static::$_veneerInstance->runScript(...func_get_args());
    }
    public static function runServerScript(string $name, string ...$args): bool {
        return static::$_veneerInstance->runServerScript(...func_get_args());
    }
    public static function bridge(PackageFilePlugin|string $script, array|string|int|float|null ...$args): mixed {
        return static::$_veneerInstance->bridge(...func_get_args());
    }
    public static function install(string ...$packages): bool {
        return static::$_veneerInstance->install(...func_get_args());
    }
    public static function installDev(string ...$packages): bool {
        return static::$_veneerInstance->installDev(...func_get_args());
    }
    public static function uninstall(string ...$packages): bool {
        return static::$_veneerInstance->uninstall(...func_get_args());
    }
    public static function uninstallDev(string ...$packages): bool {
        return static::$_veneerInstance->uninstallDev(...func_get_args());
    }
    public static function preparePackageInstallName(string $name, ?string $version = NULL): string {
        return static::$_veneerInstance->preparePackageInstallName(...func_get_args());
    }
    public static function runNpm(string $name, string ...$args): bool {
        return static::$_veneerInstance->runNpm(...func_get_args());
    }
    public static function runNpx(string $name, string ...$args): bool {
        return static::$_veneerInstance->runNpx(...func_get_args());
    }
};
