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
 * Map tree
 */
function map($func, $tree)
{
    $map = function ($f, $node) use (&$map) {
        $updatedNode = $f($node);

        $children = $node['children'] ?? [];

        if ($node['type'] == 'directory') {
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

        if ($node['type'] == 'file') {
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

        $children = $node['children'];

        if ($node['type'] == 'directory') {
            $updatedChildren = array_map(function ($n) use (&$f, &$filter) {
                 return $filter($f, $n);
            }, $children);

            $filteredChildren = array_filter($updatedChildren, function ($n) {
                if ($n != null) {
                    return $n;
                }
            });
            return array_merge($node, ['children' => $filteredChildren]);
        }

        return $node;
    };

    return $filter($func, $tree);
}
