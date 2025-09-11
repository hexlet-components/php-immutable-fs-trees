<?php

namespace Php\Immutable\Fs\Trees\tests;

use PHPUnit\Framework\TestCase;

use function Php\Immutable\Fs\Trees\trees\mkdir;
use function Php\Immutable\Fs\Trees\trees\mkfile;
use function Php\Immutable\Fs\Trees\trees\getName;
use function Php\Immutable\Fs\Trees\trees\getMeta;
use function Php\Immutable\Fs\Trees\trees\getChildren;
use function Php\Immutable\Fs\Trees\trees\isDirectory;
use function Php\Immutable\Fs\Trees\trees\isFile;
use function Php\Immutable\Fs\Trees\trees\array_flatten;
use function Php\Immutable\Fs\Trees\trees\map;
use function Php\Immutable\Fs\Trees\trees\filter;
use function Php\Immutable\Fs\Trees\trees\reduce;

class TreesTest extends TestCase
{
    public function testMake(): void
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

    public function testGetMeta(): void
    {
        $file = mkfile('etc', ['owner' => 'root']);
        $this->assertEquals(['owner' => 'root'], getMeta($file));
    }

    public function testGetName(): void
    {
        $file = mkfile('etc');
        $this->assertEquals('etc', getName($file));
    }

    public function testGetChildren(): void
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

    public function testFile(): void
    {
        $file = mkfile('robots.txt');
        $this->assertTrue(isFile($file));
    }

    public function testDirectory(): void
    {
        $directory = mkdir('/');
        $this->assertTrue(isDirectory($directory));
    }

    public function testFlattenDepth(): void
    {
        $tree = [1, 2, [3, [4, 5], [6, 7], 8]];
        $this->assertEquals([], array_flatten([]));
        $this->assertEquals([1], array_flatten([1]));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], array_flatten($tree));
        $this->assertEquals([1, 2, 3, [4, 5], [6, 7], 8], array_flatten($tree, 1));
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], array_flatten($tree, 3));
    }

    public function testMap(): void
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

    public function testReduce(): void
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

        $actual = reduce(fn($acc) => $acc + 1, $tree, 0);
        $this->assertEquals(6, $actual);

        $actual2 = reduce(function ($acc, $n) {
            return $n['type'] == 'file' ? $acc + 1 : $acc;
        }, $tree, 0);
        $this->assertEquals(2, $actual2);

        $actual3 = reduce(fn($acc, $n) => $n['type'] == 'directory' ? $acc + 1 : $acc, $tree, 0);
        $this->assertEquals(4, $actual3);
    }

    public function testFilter(): void
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

        $actual = filter(fn($n) => $n['type'] == 'directory', $tree);

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

    public function testFilter2(): void
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

        $actual = filter(fn($n) => in_array($n['name'], $names), $tree);
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
