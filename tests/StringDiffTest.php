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

/** Test string-specific functionality */
final class StringDiffTest extends \Facebook\HackTest\HackTest {
  public function testDiffLines(): void {
    $diff = StringDiff::lines("a\nb\nb\n", "a\nb\nc\n")->getDiff();
    expect(C\count($diff))->toBeSame(5);

    expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
    expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
    expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);
    expect($diff[4])->toBeInstanceOf(DiffKeepOp::class);

    expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
      vec['a', 'b', 'b', 'c', ''],
    );
  }

	public function testDiffCharacters(): void {
    $diff = StringDiff::characters('abb', 'abc')->getDiff();
		expect(C\count($diff))->toBeSame(4);

		expect($diff[0])->toBeInstanceOf(DiffKeepOp::class);
		expect($diff[1])->toBeInstanceOf(DiffKeepOp::class);
		expect($diff[2])->toBeInstanceOf(DiffDeleteOp::class);
		expect($diff[3])->toBeInstanceOf(DiffInsertOp::class);

		expect(Vec\map($diff, $op ==> $op->getContent()))->toBeSame(
			vec['a', 'b', 'b', 'c'],
		);
	}
}
