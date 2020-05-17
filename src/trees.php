<?php

namespace PhpTrees\Trees;

/**
 * Make directory node
 * @param string $name
 * @param array $children
 * @param array $meta
 * @return array
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
 * @param string $name
 * @param array $meta
 * @return array
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
 * @param array $node
 * @return array
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
 * @param array $node
 * @return array
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
 * @param array $node
 * @return string
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
 * @param array $node
 * @return boolean
 */
function isFile($node)
{
    return $node['type'] == 'file';
}

/**
 * Test file
 * @param array $node
 * @return boolean
 */
function isDirectory($node)
{
    return $node['type'] == 'directory';
}

/**
 * Recursively flatten `tree` up to `depth` times.
 * @param array $tree
 * @param int $depth
 * @return array
 * @example
 * flatten_depth([1]); // [1];
 * flatten_depth([1, 2, [3, 4]]); // [1, 2, 3, 4];
 * flatten_depth([1, [2, [3, 4]]], 1); // [1, 2, [3, 4]];
 */
function array_flatten($tree, $depth = 0)
{
    $result = [];
    foreach ($tree as $key => $value) {
        if ($depth >= 0 && is_array($value)) {
            $value = array_flatten($value, $depth > 1 ? $depth - 1 : 0 - $depth);
            $result = array_merge($result, $value);
        } else {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * Map tree
 * @param callable $func
 * @param array $tree
 * @return array
 */
function map($func, $tree)
{
    $updatedNode = $func($tree);
    $children = $tree['children'] ?? [];

    if (isDirectory($tree)) {
        $updatedChildren = array_map(function ($node) use ($func) {
            return map($func, $node);
        }, $children);
        return array_merge($updatedNode, ['children' => $updatedChildren]);
    }

    return $updatedNode;
}

/**
 * Reduce tree
 * @param callable $func
 * @param array $tree
 * @param mixed $accumulator
 * @return mixed
 */
function reduce($func, $tree, $accumulator)
{
    $children = $tree['children'] ?? [];
    $newAcc = $func($accumulator, $tree);

    if (isFile($tree)) {
        return $newAcc;
    }

    return array_reduce(
        $children,
        function ($acc, $node) use ($func) {
            return reduce($func, $node, $acc);
        },
        $newAcc
    );
}

/**
 * Filter tree
 * @param callable $func
 * @param array $tree
 * @return array
 */
function filter($func, $tree)
{
    if (!$func($tree)) {
        return null;
    }

    $children = $tree['children'] ?? null;

    if (isDirectory($tree)) {
        $updatedChildren = array_map(function ($node) use ($func) {
            return filter($func, $node);
        }, $children);

        $filteredChildren = array_filter($updatedChildren, function ($node) {
            if ($node != null) {
                return $node;
            }
        });
        return array_merge($tree, ['children' => array_values($filteredChildren)]);
    }

    return $tree;
}
