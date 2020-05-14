# php-immutable-fs-trees

[![github action status](https://github.com/hexlet-components/php-immutable-fs-trees/workflows/master/badge.svg)]((https://github.com/hexlet-components/php-immutable-fs-trees/workflows/master/badge.svg))

## Functions for working with Trees

```php
<?php

use function PhpTrees\Trees\mkdir;
use function PhpTrees\Trees\mkfile;
use function PhpTrees\Trees\getName;
use function PhpTrees\Trees\isDirectory;
use function PhpTrees\Trees\isFile;
use function PhpTrees\Trees\map;
```

## Examples

```php
<?php

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

[![Hexlet Ltd. logo](https://raw.githubusercontent.com/Hexlet/hexletguides.github.io/master/images/hexlet_logo128.png)](https://ru.hexlet.io/pages/about?utm_source=github&utm_medium=link&utm_campaign=php-eloquent-blog)

This repository is created and maintained by the team and the community of Hexlet, an educational project. [Read more about Hexlet (in Russian)](https://ru.hexlet.io/pages/about?utm_source=github&utm_medium=link&utm_campaign=php-eloquent-blog).
