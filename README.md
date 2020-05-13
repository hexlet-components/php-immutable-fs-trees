# php-immutable-fs-trees

[![github action status](https://github.com/hexlet-components/php-immutable-fs-trees/workflows/master/badge.svg)]((https://github.com/hexlet-components/php-immutable-fs-trees/workflows/master/badge.svg))

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

map(fn($node) => array_merge($node, ['name' => strtoupper(getName($node))]), $tree);
// [
//    name => 'ETC',
//    children => [
//        [ name => 'CONFIG', meta => [], type => 'file' ],
//        [ name => 'HOSTS', meta => [], type => 'file' ]
//    ],
//    meta => [],
//    type => 'directory'
// ]
```
