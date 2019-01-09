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
// @oss-disable: use function \expect;
use function Facebook\FBExpect\expect; // @oss-enable

final class ClusterTest extends \Facebook\HackTest\HackTest {
  public function testReplaceTrailing(): void {
    $diff = StringDiff::characters('abc', 'ade')->getDiff()
      |> cluster($$);
    expect(C\count($diff))->toBeSame(3);
    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[2])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec[vec['a'], vec['b', 'c'], vec['d', 'e']],
    );
  }
}
