<?php

namespace PhpTrees\tests;

use PHPUnit\Framework\TestCase;

use function PhpTrees\Trees\mkdir;
use function PhpTrees\Trees\mkfile;
use function PhpTrees\Trees\getName;
use function PhpTrees\Trees\getMeta;
use function PhpTrees\Trees\getChildren;
use function PhpTrees\Trees\isDirectory;
use function PhpTrees\Trees\isFile;
use function PhpTrees\Trees\array_flatten;
use function PhpTrees\Trees\map;
use function PhpTrees\Trees\filter;
use function PhpTrees\Trees\reduce;

class TreesTest extends TestCase
{

    public function testMake()
    {
        $tree = mkdir('/', [mkdir('etc'), mkdir('usr'), mkfile('robots.txt')]);
        $expected = [
        'children' => [
          [
            'children' => [],
            'meta' => [],
            'name' => 'etc',
            'type' => 'directory',
          ],
          [
            'children' => [],
            'meta' => [],
            'name' => 'usr',
            'type' => 'directory',
          ],
          [
            'meta' => [],
            'name' => 'robots.txt',
            'type' => 'file',
          ],
        ],
        'meta' => [],
        'name' => '/',
        'type' => 'directory',
        ];

        $this->assertEquals($expected, $tree);
    }

    public function testGetMeta()
    {
        $file = mkfile('etc', ['owner' => 'root']);
        $this->assertEquals(['owner' => 'root'], getMeta($file));
    }

    public function testGetName()
    {
        $file = mkfile('etc');
        $this->assertEquals('etc', getName($file));
    }

    public function testGetChildren()
    {
        $tree = mkdir('/', [mkdir('etc'), mkdir('usr'), mkfile('robots.txt')]);
        $expected = [
          [
            'children' => [],
            'meta' => [],
            'name' => 'etc',
            'type' => 'directory',
          ],
          [
            'children' => [],
            'meta' => [],
            'name' => 'usr',
            'type' => 'directory',
          ],
          [
            'meta' => [],
            'name' => 'robots.txt',
            'type' => 'file',
          ],
        ];
        $this->assertEquals($expected, getChildren($tree));
    }

    public function testFile()
    {
        $file = mkfile('robots.txt');
        $this->assertTrue(isFile($file));
    }

    public function testDirectory()
    {
        $directory = mkdir('/');
        $this->assertTrue(isDirectory($directory));
    }

    public function testFlattenDepth()
    {
        $tree = [1, 2, [3, [4, 5], [6, 7], 8]];
        $this->assertEquals([], array_flatten([]));
        $this->assertEquals([1], array_flatten([1]));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], array_flatten($tree));
        $this->assertEquals([1, 2, 3, [4, 5], [6, 7], 8], array_flatten($tree, 1));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], array_flatten($tree, 3));
    }

    public function testMap()
    {
        $tree = mkdir('/', [
            mkdir('eTc', [
                mkdir('NgiNx'),
                mkdir('CONSUL', [
                    mkfile('config.json'),
                ]),
            ]),
            mkfile('hOsts'),
        ]);

        $expected = [
          'children' => [
            [
              'children' => [
                [
                  'children' => [], 'meta' => [], 'name' => 'NGINX', 'type' => 'directory',
                ],
                [
                  'children' => [
                    ['meta' => [], 'name' => 'CONFIG.JSON', 'type' => 'file']
                  ],
                  'meta' => [],
                  'name' => 'CONSUL',
                  'type' => 'directory',
                ],
              ],
              'meta' => [],
              'name' => 'ETC',
              'type' => 'directory',
            ],
            ['meta' => [], 'name' => 'HOSTS', 'type' => 'file'],
          ],
          'meta' => [],
          'name' => '/',
          'type' => 'directory',
        ];

        $actual = map(function ($n) {
            return array_merge($n, ['name' => strtoupper($n['name'])]);
        }, $tree);

        $this->assertEquals($expected, $actual);
    }

    public function testReduce()
    {
        $tree = mkdir('/', [
            mkdir('eTc', [
                mkdir('NgiNx'),
                mkdir('CONSUL', [
                    mkfile('config.json'),
                ]),
            ]),
            mkfile('hOsts'),
        ]);

        $actual = reduce(function ($acc) {
            return $acc + 1;
        }, $tree, 0);
        $this->assertEquals(6, $actual);

        $actual2 = reduce(function ($acc, $n) {
            return $n['type'] == 'file' ? $acc + 1 : $acc;
        }, $tree, 0);
        $this->assertEquals(2, $actual2);

        $actual3 = reduce(function ($acc, $n) {
            return $n['type'] == 'directory' ? $acc + 1 : $acc;
        }, $tree, 0);
        $this->assertEquals(4, $actual3);
    }

    public function testFilter()
    {
        $tree = mkdir('/', [
            mkdir('etc', [
                mkdir('nginx', [
                    mkdir('conf.d'),
                ]),
                mkdir('consul', [
                    mkfile('config.json'),
                ]),
              ]),
              mkfile('hosts'),
        ]);

        $actual = filter(function ($n) {
            return $n['type'] == 'directory';
        }, $tree);

        $expected = [
            'children' => [
              [
                'children' => [
                  [
                    'children' => [[
                      'children' => [],
                      'meta' => [],
                      'name' => 'conf.d',
                      'type' => 'directory',
                    ]],
                    'meta' => [],
                    'name' => 'nginx',
                    'type' => 'directory',
                  ],
                  [
                    'children' => [],
                    'meta' => [],
                    'name' => 'consul',
                    'type' => 'directory',
                  ],
                ],
                'meta' => [],
                'name' => 'etc',
                'type' => 'directory',
              ],
            ],
            'meta' => [],
            'name' => '/',
            'type' => 'directory',
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testFilter2()
    {
        $tree = mkdir('/', [
            mkdir('etc', [
                mkdir('nginx', [
                    mkdir('conf.d'),
                ]),
                mkdir('consul', [
                    mkfile('config.json'),
                ]),
            ]),
            mkfile('hosts'),
        ]);

        $names = ['/', 'hosts'];

        $actual = filter(function ($n) use ($names) {
            return in_array($n['name'], $names);
        }, $tree);

        $expected = [
            'name' => '/',
            'children' => [[
                'name' => 'hosts',
                'meta' => [],
                'type' => 'file'
            ]],
            'meta' => [],
            'type' => 'directory'
        ];

        $this->assertEquals($expected, $actual);
    }
}
