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
use function Facebook\FBExpect\expect;

/** Test raw diff ops - not unified diffs etc.
 *
 * Ordering is still important: -a +b is generally considered more readable than
 * +b -a
 */
final class RawDiffTest extends \Facebook\HackTest\HackTest {
  public function testReplaceLastItem(): void {
    $diff = (new StringDiff(vec['a', 'b', 'b'], vec['a', 'b', 'c']))->getDiff();

    expect(C\count($diff))->toBeSame(4);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'b', 'c'],
    );
  }

  public function testTotalReplacement(): void {
    $diff = (new StringDiff(vec['a', 'b', 'c'], vec['d', 'e', 'f']))->getDiff();

    expect(C\count($diff))->toBeSame(6);

    expect($diff[0])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[1])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[4])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[5])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c', 'd', 'e', 'f'],
    );
  }

  public function testAppend(): void {
    $diff = (
      new StringDiff(vec['a', 'b', 'c'], vec['a', 'b', 'c', 'd', 'e', 'f'])
    )->getDiff();

    expect(C\count($diff))->toBeSame(6);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[4])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[5])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c', 'd', 'e', 'f'],
    );
  }

  public function testTruncate(): void {
    $diff = (
      new StringDiff(vec['a', 'b', 'c'], vec['a', 'b'])
    )->getDiff();

    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testPrepend(): void {
    $diff = (new StringDiff(vec['b', 'c'], vec['a', 'b', 'c']))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testChomp(): void {
    $diff = (new StringDiff(vec['a', 'b', 'c'], vec['b', 'c']))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testInsertMid(): void {
    $diff = (new StringDiff(vec['a', 'c'], vec['a', 'b', 'c']))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[2])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testDeleteMid(): void {
    $diff = (new StringDiff(vec['a', 'b', 'c'], vec['a', 'c']))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[2])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testInsertFromEmpty(): void {
    $diff = (new StringDiff(vec[], vec['a', 'b', 'c']))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[1])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[2])->toBeInstanceOf(DiffInsertOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }

  public function testDeleteAll(): void {
    $diff = (new StringDiff(vec['a', 'b', 'c'], vec[]))->getDiff();
    expect(C\count($diff))->toBeSame(3);

    expect($diff[0])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[1])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'c'],
    );
  }
}
