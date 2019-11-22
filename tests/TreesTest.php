<?php

namespace PhpPoints\tests;

use PHPUnit\Framework\TestCase;

use function PhpTrees\Trees\mkdir;
use function PhpTrees\Trees\mkfile;
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
}
