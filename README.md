# Overpass

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/overpass?style=flat)](https://packagist.org/packages/decodelabs/overpass)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/overpass.svg?style=flat)](https://packagist.org/packages/decodelabs/overpass)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/overpass.svg?style=flat)](https://packagist.org/packages/decodelabs/overpass)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/overpass/integrate.yml?branch=develop)](https://github.com/decodelabs/overpass/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/overpass?style=flat)](https://packagist.org/packages/decodelabs/overpass)

### Simple node.js bridge for PHP

Overpass provides a simple interface for installing dependencies and interacting with native node.js scripts.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---


## Installation

```bash
composer require decodelabs/overpass
```

## Usage

Load a context to work from:

```php
use DecodeLabs\Overpass\Context;

$context = new Context('path/to/project/');
```

Or use the `Overpass` Veneer frontage to work from `cwd()`.
Overpass will search back up the file tree for the nearest package.json.


```php
use DecodeLabs\Overpass;

echo Overpass::$runDir; // Working directory
echo Overpass::$rootDir; // Parent or current dir containing package.json
echo Overpass::$packageFile; // Location  of package.json

Overpass::run('myfile.js'); // node myfile.js
Overpass::runScript('my-script'); // npm run my-script

Overpass::install('package1', 'package2'); // npm install package1 package2
Overpass::installDev('package1', 'package2'); // npm install package1 package2 --save-dev
```

### Bridging

Overpass offers a simple Bridge system to allow you to define custom javascript, pass arguments to it, and consume the result via node.

```javascript
// myfile.js
module.exports = function(input) {
    return 'hello ' + input;
}
```

```php
use DecodeLabs\Overpass;

$result = Overpass::bridge('myfile.js', 'world'); // 'hello world'
```

## Licensing
Overpass is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
