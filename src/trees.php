<?php

namespace PhpTrees\Trees;

/**
 * Make directory node
 */
function mkdir(string $name, array $children = [], array $meta = [])
{
    return [
        "name" => $name,
        "children" => $children,
        "meta" => $meta,
        "type" => "directory",
    ];
}

/**
 * Make file node
 */
function mkfile(string $name, array $meta = [])
{
    return [
        "name" => $name,
        "meta" => $meta,
        "type" => "file",
    ];
}


/**
 * Return children
 * @example
 * getChildren(mkdir('etc')); // []
 * getChildren(mkdir('etc', [mkfile('name')])); // [<file>]
 */
function getChildren($node)
{
    return $node['children'];
}

/**
 * Return meta
 * @example
 * getMeta(mkfile('etc')); // []
 * getMeta(mkfile('etc', ['owner' => 'root'])); // ['owner' => 'root']
 */
function getMeta($node)
{
    return $node['meta'];
}

/**
 * Return name
 * @example
 * getName(mkfile('etc')); // etc
 * getName(mkdir('/')); // /
 */
function getName($node)
{
    return $node['name'];
}

/**
 * Test directory
 */
function isFile($node)
{
    return $node['type'] == 'file';
}

/**
 * Test file
 */
function isDirectory($node)
{
    return $node['type'] == 'directory';
}

/**
 * Recursively flatten `tree` up to `depth` times.
 * @example
 * flatten_depth([1]); // [1];
 * flatten_depth([1, 2, [3, 4]]); // [1, 2, 3, 4];
 * flatten_depth([1, [2, [3, 4]]], 1); // [1, 2, [3, 4]];
 */
function flatten_depth($tree, $depth = 0)
{
    $result = [];
    foreach ($tree as $key => $value) {
        if ($depth >= 0 && is_array($value)) {
            $value = flatten_depth($value, $depth > 1 ? $depth - 1 : 0 - $depth);
            $result = array_merge($result, $value);
        } else {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * Map tree
 */
function map($func, $tree)
{
    $map = function ($f, $node) use (&$map) {
        $updatedNode = $f($node);

        $children = $node['children'] ?? [];

        if (isDirectory($node)) {
            $updatedChildren = array_map(function ($n) use (&$f, &$map) {
                return $map($f, $n);
            }, $children);
            return array_merge($updatedNode, ['children' => $updatedChildren]);
        }

        return $updatedNode;
    };

    return $map($func, $tree);
}

/**
 * Reduce tree
 */
function reduce($func, $tree, $accumulator)
{
    $reduce = function ($f, $node, $acc) use (&$reduce) {
        $children = $node['children'] ?? [];
        $newAcc = $f($acc, $node);

        if (isFile($node)) {
            return $newAcc;
        }

        return array_reduce(
            $children,
            function ($iAcc, $n) use (&$reduce, &$f) {
                return $reduce($f, $n, $iAcc);
            },
            $newAcc
        );
    };

    return $reduce($func, $tree, $accumulator);
}

/**
 * Filter tree
 */
function filter($func, $tree)
{
    $filter = function ($f, $node) use (&$filter) {
        if (!$f($node)) {
            return null;
        }

        $children = $node['children'] ?? null;

        if (isDirectory($node)) {
            $updatedChildren = array_map(function ($n) use (&$f, &$filter) {
                return $filter($f, $n);
            }, $children);

            $filteredChildren = array_filter($updatedChildren, function ($n) {
                if ($n != null) {
                    return $n;
                }
            });
            return array_merge($node, ['children' => array_values($filteredChildren)]);
        }

        return $node;
    };

    return $filter($func, $tree);
}
