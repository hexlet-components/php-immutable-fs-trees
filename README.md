# php-immutable-fs-trees

[![github action status](https://github.com/hexlet-components/php-immutable-fs-trees/workflows/PHP%20CI/badge.svg)](../../actions)

Functions for working with trees.

## Examples

```php
<?php

use function Php\Immutable\Fs\Trees\trees\mkdir;
use function Php\Immutable\Fs\Trees\trees\mkfile;
use function Php\Immutable\Fs\Trees\trees\getName;
use function Php\Immutable\Fs\Trees\trees\isDirectory;
use function Php\Immutable\Fs\Trees\trees\isFile;
use function Php\Immutable\Fs\Trees\trees\map;

isFile(mkfile('config')); // true
isDirectory(mkdir('etc')); // true

$tree = mkdir('etc', [mkfile('config'), mkfile('hosts')]);

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

[![Hexlet Ltd. logo](https://raw.githubusercontent.com/Hexlet/assets/master/images/hexlet_logo128.png)](https://ru.hexlet.io/pages/about?utm_source=github&utm_medium=link&utm_campaign=php-immutable-fs-trees)

This repository is created and maintained by the team and the community of Hexlet, an educational project. [Read more about Hexlet (in Russian)](https://ru.hexlet.io/pages/about?utm_source=github&utm_medium=link&utm_campaign=php-immutable-fs-trees).
