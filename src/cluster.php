<?hh // strict
/*
 *  Copyright (c) 2017-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\DiffLib;

use namespace HH\Lib\{C, Vec};

/** Group sequential operations of the same kind into one.
 *
 * For example, `[insert(a), insert(b), keep(c), insert(d), insert(e)]` will
 * be transformed to `[insert([a, b]), keep([c]), insert([d, e])`.
 */
function cluster<T>(
  vec<DiffOp<T>> $diff,
): vec<DiffOp<vec<T>>> {
  $clusters = vec[];
  $limit = C\count($diff);
  while (!C\is_empty($diff)) {
    $class = $diff[0]->getDiffOpClass();

    $next = C\find_key($diff, $op ==> $op->getDiffOpClass() !== $class);
    if ($next === null) {
      $clusters[] = $diff;
      break;
    }

    $clusters[] = Vec\take($diff, $next);
    $diff = Vec\drop($diff, $next);
  }

  return Vec\map(
    $clusters,
    $cluster ==> {
      $first = C\firstx($cluster);

      if ($first->isDeleteOp()) {
        return new DiffDeleteOp(
          $first->asDeleteOp()->getOldPos(),
          Vec\map($cluster, $op ==> $op->asDeleteOp()->getContent()),
        );
      }

      if ($first->isInsertOp()) {
        return new DiffInsertOp(
          $first->asInsertOp()->getNewPos(),
          Vec\map($cluster, $op ==> $op->asInsertOp()->getContent()),
        );
      }

      if ($first->isKeepOp()) {
        $first = $first->asKeepOp();
        return new DiffKeepOp(
          $first->getOldPos(),
          $first->getNewPos(),
          Vec\map($cluster, $op ==> $op->asKeepOp()->getContent()),
        );
      }

      invariant_violation('invalid op kind: %s', \get_class($first));
    }
  );
}
