# php-immutable-fs-trees

[![github action status](https://github.com/hexlet-components/js-immutable-fs-trees/workflows/Node%20CI/badge.svg)](https://github.com/hexlet-components/js-immutable-fs-trees/actions)

## Install

```sh
$ composer require hexlet/pairs
```

## Usage example

```php

use function PhpTrees\Trees\mkdir;
use function PhpTrees\Trees\mkfile;
use function PhpTrees\Trees\getName;
use function PhpTrees\Trees\isDirectory;
use function PhpTrees\Trees\isFile;
use function PhpTrees\Trees\map;

isFile(mkfile('config')); // true
isDirectory(mkdir('etc')); // true

$tree = mkdir('etc', 'children' => [mkfile('config'), mkfile('hosts')]);

$callbackFn = function($node) {
  $name = getName($node);
  const $newName = strtoupper($name);
  return { ...node, name: newName };
};

map(fn($node) => strtoupper(getName($node)), $tree);
// {
//   name: 'ETC',
//   children: [
//     { name: 'CONFIG', meta: {}, type: 'file' },
//     { name: 'HOSTS', meta: {}, type: 'file' }
//   ],
//   meta: {},
//   type: 'directory',
// }
```

For more information, see the [Full Documentation](https://github.com/hexlet-components/js-immutable-fs-trees/tree/master/docs)
