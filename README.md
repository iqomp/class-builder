# iqomp/class-builder

Create PHP class/interface content based on structured array

## Installation

```bash
composer require iqomp/class-builder
```

## Usage

This module create new library named `Iqomp\ClassBuilder\Builder` that can be
used to generate php class file content based on structured array:

```php
<?php

use Iqomp\ClassBuilder\Builder;

$structure = [ /* ... */ ];
$result = Builder::build($structure);
```

## Data Structure

Below is a complete list of array data structure known so far:

```php
$structure = [
    // the file comments
    'comments' => [
        'My first library',
        '@package vendor/module',
        '@version 0.0.1'
    ],

    'namespace' => 'Vendor\\Module',
    'type' => 'class', // interface
    'name' => 'ClassName',
    'extends' => [
        '\\Other\\Module\\Class',
        // may add more for interface
    ],
    'implements' => [
        '\\Other\\Module\\Iface',
        // may add more
    ],
    'properties' => [
        'first' => [
            'static' => false,
            'visibility' => 'public',
            'type' => 'string',
            'default' => null // remove to not set the default
        ],
        'second' => [
            'visibility' => 'protected',
            'type' => '\\Other\\Module\\Class'
        ]
    ],
    'methods' => [
        'getOne' => [
            'static' => false,
            'visibility' => 'public',
            'return' => '?object',
            'arguments' => [
                'first' => [
                    'type' => 'int'
                ],
                'second' => [
                    'type' => 'bool',
                    'default' => 0
                ]
            ],
            'content' => 'return false;'
        ]
    ]
];
```

Based on above data structure, calling `Builder::build` will result as below include
the `<?php` line:

```php
<?php

/**
 * My first library
 * @package vendor/module
 * @version 0.0.1
 */

namespace Vendor\Module;

class ClassName extends \Other\Module\Class implements \Other\Module\Iface
{
    public string $first = null;

    protected \Other\Module\Class $second;

    public function getOne (int $first, bool $second = 0): ?object
    {
        return false;
    }
}
```

## TODO

1. Use `use` class to short used classes.
