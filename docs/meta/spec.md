# Overpass — Package Specification

> **Cluster:** `integration`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/overpass`
> **Role:** Node bridge

## Overview

### Purpose

Overpass provides a simple interface for installing dependencies and interacting with native Node.js scripts from PHP. It enables:

- Node.js project discovery (finding `package.json`)
- Running Node.js scripts directly
- Executing npm scripts from `package.json`
- Running executables from `node_modules/.bin`
- Running npx packages
- Installing and uninstalling npm packages
- Bridging between PHP and JavaScript (passing data to Node.js, receiving results)
- Automatic package manager detection (npm, pnpm, yarn)
- Binary path management and resolution

Overpass simplifies Node.js integration by providing a unified PHP interface for common Node.js operations.

### Non-Goals

- Overpass does not provide a Node.js runtime implementation
- It does not handle JavaScript parsing or execution in PHP
- It does not provide a full npm/pnpm/yarn API wrapper
- It does not handle WebSocket or real-time communication
- It does not provide browser automation or testing
- It does not handle transpilation or bundling

## Role in the Ecosystem

### Cluster & Positioning

Overpass belongs to the **integration** cluster, providing a bridge between PHP and Node.js ecosystems. It enables PHP applications to leverage Node.js tools and scripts.

### Usage Contexts

Overpass is used for:

- Frontend build tool integration (Vite, Webpack, etc.)
- Running JavaScript-based transformations from PHP
- Managing Node.js dependencies from PHP
- Executing npm scripts during deployment
- Passing data between PHP and JavaScript
- Integrating with Node.js-based services

## Public Surface

### Key Types

- **`Project`** — Main class for interacting with a Node.js project. Manages package.json discovery, binary paths, and delegates to Runtime and PackageManager.

- **`Runtime`** — Interface for Node.js runtime implementations. Defines methods for loading package managers, running scripts, and executing bridge operations.

- **`RuntimeTrait`** — Trait providing common implementation for Runtime interface.

- **`Runtime\Node`** — Concrete implementation of Runtime for Node.js. Detects package manager from lock files.

- **`PackageManager`** — Interface for package manager implementations (npm, pnpm, yarn). Defines methods for running scripts, executables, and managing packages.

- **`PackageManager\Npm`** — Concrete implementation for npm.

- **`PackageManager\Pnpm`** — Concrete implementation for pnpm.

- **`PackageManager\Yarn`** — Concrete implementation for Yarn.

### Main Entry Points

- **`Project::__construct(?Dir $dir, Systemic $systemic)`** — Creates a project instance. Finds package.json by searching up the directory tree. Uses working directory if no path provided.

- **`Project::isInitialised(): bool`** — Checks if package.json exists.

- **`Project::setBinaryPath(string $binary, string|File $path): void`** — Sets custom path for a binary (node, npm, etc.).

- **`Project::getBinaryPath(string $binary): string`** — Gets path for a binary. Returns custom path if set, otherwise resolves via Monarch.

- **`Project::run(string $name, string ...$args): bool`** — Runs a Node.js script. Executes `node <name> <args>`.

- **`Project::bridge(string|File $script, string|array|int|float|null ...$args): mixed`** — Executes a JavaScript module with PHP arguments and returns the result. Uses JSON for data exchange.

- **`Project::runScript(string $name, string ...$args): bool`** — Runs an npm script from package.json. Executes `npm run <name> <args>`.

- **`Project::runExecutable(string $name, string ...$args): bool`** — Runs an executable from node_modules/.bin. Executes `npx <name> <args>`.

- **`Project::runPackage(string $name, string ...$args): bool`** — Runs a package via npx. Alias for `runExecutable`.

- **`Project::install(string ...$packages): bool`** — Installs npm packages. Executes `npm install <packages>`.

- **`Project::installDev(string ...$packages): bool`** — Installs npm dev dependencies. Executes `npm install --save-dev <packages>`.

- **`Project::uninstall(string ...$packages): bool`** — Uninstalls npm packages. Executes `npm remove <packages>`.

- **`Project::uninstallDev(string ...$packages): bool`** — Uninstalls npm dev dependencies. Executes `npm remove --save-dev <packages>`.

- **`Project::ensureFetched(): bool`** — Ensures all dependencies are installed. Executes `npm install`.

- **`Project::update(): bool`** — Updates all dependencies. Executes `npm update`.

- **`Runtime::loadPackageManager(Project $project): PackageManager`** — Loads appropriate PackageManager based on lock file detection.

- **`Runtime::run(Project $project, string $name, string ...$args): bool`** — Runs a script with the runtime binary.

- **`Runtime::executeBridge(Project $project, string $payload): Result`** — Executes the bridge evaluator with JSON payload via stdin.

## Dependencies

### Decode Labs

- **`atlas`** — Used for file and directory operations.

- **`exceptional`** — Used for exception handling.

- **`monarch`** — Used for path resolution and working directory detection.

- **`systemic`** — Used for process execution and command management.

### External

- None (pure PHP implementation).

## Behaviour & Contracts

### Invariants

- Project searches up to 3 directories for package.json
- Package manager is auto-detected from lock files (pnpm-lock.yaml, package-lock.json, yarn.lock)
- Default package manager is npm if no lock file found
- Binary paths are resolved via Monarch if not explicitly set
- Bridge communication uses JSON for data exchange
- Bridge uses a unique delineator to separate output from script output
- All package manager commands run from rootDir
- Runtime property is lazily initialized
- PackageManager property is lazily initialized

### Input & Output Contracts

- **`Project::__construct(?Dir $dir, Systemic $systemic)`** — Creates project instance. Searches for package.json up to 3 directories. Uses working directory if dir is null. Stores packageFile and rootDir.

- **`Project::isInitialised(): bool`** — Returns true if packageFile exists, false otherwise.

- **`Project::setBinaryPath(string $binary, string|File $path): void`** — Stores binary path in paths array. Converts File to string path.

- **`Project::getBinaryPath(string $binary): string`** — Returns custom path if set, otherwise resolves via Monarch::getPaths()->resolve().

- **`Project::run(string $name, string ...$args): bool`** — Executes runtime binary with script name and args. Returns true if successful, false otherwise.

- **`Project::bridge(string|File $script, string|array|int|float|null ...$args): mixed`** — Throws Runtime exception if not initialized or script doesn't exist. Encodes script path and args as JSON payload. Executes evaluate.cjs with payload via stdin. Parses JSON output after delineator. Returns result from JSON. Throws Runtime exception on failure or invalid output.

- **`Project::runScript(string $name, string ...$args): bool`** — Delegates to packageManager. Returns true if successful, false otherwise.

- **`Project::install(string ...$packages): bool`** — Delegates to packageManager. Installs packages as regular dependencies. Returns true if successful, false otherwise.

- **`Runtime\Node::loadPackageManager(Project $project): PackageManager`** — Returns Pnpm if pnpm-lock.yaml exists, Npm if package-lock.json exists, Yarn if yarn.lock exists, otherwise Npm.

- **`Runtime::executeBridge(Project $project, string $payload): Result`** — Executes node with evaluate.cjs, writes payload to stdin, closes input, captures output. Returns Systemic Result.

- **`PackageManager\Npm::runScript(Project $project, string $name, string ...$args): bool`** — Executes `npm run <name> <args>` with signal handling (SIGINT, SIGTERM, SIGQUIT). Returns true if successful.

- **`PackageManager\Pnpm::runExecutable(Project $project, string $name, string ...$args): bool`** — Executes `pnpm exec <name> <args>`. Returns true if successful.

- **`PackageManager\Yarn::runPackage(Project $project, string $name, string ...$args): bool`** — Executes `yarn dlx <name> <args>`. Returns true if successful.

## Error Handling

Overpass uses the Exceptional pattern for error handling. Key exception types:

- **`Runtime`** — Thrown when project is not inside a JavaScript package (no package.json found), when a bridge script doesn't exist, when bridge execution fails, or when bridge evaluator doesn't return valid JSON.

Exceptions preserve context and include detailed error messages. Process execution failures are reported via boolean return values, while bridge failures throw exceptions.

## Configuration & Extensibility

### Extension Points

- **Custom Runtimes** — Implement `Runtime` interface to support alternative JavaScript runtimes (Bun, Deno).

- **Custom Package Managers** — Implement `PackageManager` interface to support alternative package managers.

- **Binary Path Customization** — Use `setBinaryPath()` to override default binary locations.

- **Bridge Scripts** — Create custom JavaScript modules that export functions accepting PHP-serializable arguments.

### Configuration

- **Project Discovery** — Project automatically searches up the directory tree for package.json.

- **Package Manager Detection** — Package manager is auto-detected from lock files or defaults to npm.

- **Binary Paths** — Binaries are resolved via Monarch paths unless explicitly set.

- **Runtime Selection** — Runtime is lazily loaded (currently only Node.js is supported).

- **Signal Handling** — Package manager commands automatically handle SIGINT, SIGTERM, and SIGQUIT signals.

## Interactions with Other Packages

- **Systemic** — Uses Systemic for process execution and command management.

- **Atlas** — Uses Atlas for file and directory operations.

- **Monarch** — Uses Monarch for path resolution and working directory detection.

- **Zest** — Uses Overpass for Vite integration and frontend build tool management.

## Usage Examples

### Basic Project Setup

```php
use DecodeLabs\Monarch;
use DecodeLabs\Overpass\Project;
use DecodeLabs\Systemic;

// Load project from current directory
$project = new Project(null, Monarch::getService(Systemic::class));

// Load project from specific directory
$project = new Project(
    Atlas::getDir('/path/to/project'),
    Monarch::getService(Systemic::class)
);

// Access project info
echo $project->rootDir; // Parent or current dir containing package.json
echo $project->packageFile; // Location of package.json
```

### Running Node.js Scripts

```php
// Run a script directly
$project->run('myfile.js');
// Executes: node myfile.js

$project->run('build.js', '--production');
// Executes: node build.js --production
```

### Running npm Scripts

```php
// Run npm script from package.json
$project->runScript('build');
// Executes: npm run build

$project->runScript('test', '--coverage');
// Executes: npm run test --coverage
```

### Running Executables

```php
// Run executable from node_modules/.bin
$project->runExecutable('vite', 'build');
// Executes: npx vite build

$project->runExecutable('prettier', '--write', 'src/');
// Executes: npx prettier --write src/
```

### Running npx Packages

```php
// Run package via npx
$project->runPackage('create-react-app', 'my-app');
// Executes: npx create-react-app my-app
```

### Installing Packages

```php
// Install regular dependencies
$project->install('react', 'react-dom');
// Executes: npm install react react-dom

// Install dev dependencies
$project->installDev('vite', '@types/node');
// Executes: npm install --save-dev vite @types/node
```

### Uninstalling Packages

```php
// Uninstall regular dependencies
$project->uninstall('lodash');
// Executes: npm remove lodash

// Uninstall dev dependencies
$project->uninstallDev('webpack');
// Executes: npm remove --save-dev webpack
```

### Dependency Management

```php
// Ensure all dependencies are installed
$project->ensureFetched();
// Executes: npm install

// Update all dependencies
$project->update();
// Executes: npm update
```

### Bridging PHP and JavaScript

```php
// JavaScript module (myfile.js)
// module.exports = function(input) {
//     return 'hello ' + input;
// }

// PHP
$result = $project->bridge('myfile.js', 'world');
// Returns: 'hello world'

// Pass multiple arguments
$result = $project->bridge('calc.js', 10, 20);

// Pass arrays
$result = $project->bridge('process.js', ['key' => 'value']);
```

### Custom Binary Paths

```php
// Set custom binary paths
$project->setBinaryPath('node', '/usr/local/bin/node');
$project->setBinaryPath('npm', '/usr/local/bin/npm');

// Get binary path
$nodePath = $project->getBinaryPath('node');

// Check if custom path is set
if ($project->hasBinaryPath('node')) {
    // Custom path is set
}

// Remove custom path
$project->removeBinaryPath('node');
```

### Package Manager Detection

```php
// Package manager is auto-detected
$pm = $project->packageManager;

echo $pm->name; // 'npm', 'pnpm', or 'Yarn'
echo $pm->binary; // 'npm', 'pnpm', or 'yarn'
```

## Implementation Notes (for Contributors)

### Architecture

- **Project Discovery** — Project searches up the directory tree (max 3 levels) for package.json, providing flexible project root detection.

- **Lazy Initialization** — Runtime and PackageManager are lazily loaded on first access to avoid unnecessary work.

- **Package Manager Detection** — Lock files are checked in priority order (pnpm, npm, yarn) to determine the appropriate package manager.

- **Binary Path Management** — Custom binary paths can be set per project, otherwise resolved via Monarch paths.

- **Bridge Communication** — Uses JSON for bidirectional data exchange between PHP and JavaScript. A unique delineator separates script output from result JSON.

- **Signal Handling** — Long-running commands (scripts, executables) automatically handle termination signals.

- **Process Execution** — Uses Systemic for robust process execution with working directory management.

- **evaluate.cjs** — CommonJS module that loads user scripts, executes them with arguments, and returns results via JSON. Uses dynamic import for ESM support.

### Performance Considerations

- Lazy initialization defers runtime and package manager detection until needed
- Package.json search is limited to 3 directory levels
- Binary path resolution is cached per project
- Bridge communication uses stdin/stdout for efficient data transfer

### Design Decisions

- **Project-Based API** — Centering the API around a Project instance provides clear scope and context for operations.

- **Lazy Initialization** — Creating runtime and package manager on demand improves initialization speed.

- **Package Manager Auto-Detection** — Detecting package manager from lock files ensures correct command execution.

- **Binary Path Customization** — Allowing custom binary paths provides flexibility for non-standard installations.

- **Bridge Pattern** — Using a bridge evaluator script enables seamless PHP-to-JavaScript function calls.

- **JSON Communication** — Using JSON for data exchange provides a simple, robust serialization format.

- **Signal Handling** — Automatic signal handling ensures graceful termination of long-running processes.

- **Systemic Integration** — Using Systemic for process execution provides consistent error handling and output capture.

## Testing & Quality

**Code Quality:** 4.5/5 — Excellent, mature codebase with comprehensive functionality and solid architecture.

**README Quality:** 4/5 — Very good documentation with clear usage examples covering main use cases.

**Documentation:** 0/5 — No formal documentation beyond README.

**Tests:** 0/5 — No test suite currently.

See `composer.json` for supported PHP versions.

## Roadmap & Future Ideas

- Enhanced documentation and API reference
- Test suite implementation
- Support for additional runtimes (Bun, Deno)
- Package.json parsing and manipulation
- Lock file parsing and dependency resolution
- Enhanced bridge error handling and debugging
- Bidirectional streaming for long-running processes
- Type definitions for bridge arguments and results
- Caching for package manager detection
- Support for monorepo structures

## References

- [Decode Labs Chorus](https://github.com/decodelabs/chorus)
- [Overpass Repository](https://github.com/decodelabs/overpass)
- [Systemic Repository](https://github.com/decodelabs/systemic)
- [Zest Repository](https://github.com/decodelabs/zest)

