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

use namespace HH\Lib\{C, Str, Vec};
// @oss-disable: use function \expect;
use function Facebook\FBExpect\expect; // @oss-enable

// @oss-disable: use type \DataProvider;
use type Facebook\HackTest\DataProvider; // @oss-enable

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

  public function provideExamples(): vec<varray<string>> {
    return Vec\map(
      /* HH_IGNORE_ERROR[4107] using directly because this is open source */
      /* HH_IGNORE_ERROR[2049] using directly because this is open source */
      \glob(__DIR__.'/examples/*.a'),
      /* HH_IGNORE_ERROR[4107] using directly because this is open source */
      /* HH_IGNORE_ERROR[2049] using directly because this is open source */
      $ex ==> varray[\basename($ex, '.a')],
    );
  }

  <<DataProvider('provideExamples')>>
  public function testUnifiedDiff(string $name): void {
    $base = __DIR__.'/examples/'.$name;
    $a = \file_get_contents($base.'.a');
    $b = \file_get_contents($base.'.b');
    $diff = StringDiff::lines($a, $b)->getUnifiedDiff();

    expect($diff)->toBeSame(
      \file_get_contents($base.'.udiff.expect'),
      'Did not match expected contents '.
      '(from diff -u %s.a %s.b | tail -n +3 > %s.udiff.expect)',
      $name,
      $name,
      $name,
    );
  }

  <<DataProvider('provideExamples')>>
  public function testCLIColoredDiff(string $name): void {
    $base = __DIR__.'/examples/'.$name;
    $a = \file_get_contents($base.'.a');
    $b = \file_get_contents($base.'.b');
    $diff = CLIColoredUnifiedDiff::create($a, $b);

    /* HH_IGNORE_ERROR[4107] using directly because this is open source */
    /* HH_IGNORE_ERROR[2049] using directly because this is open source */
    \file_put_contents($base.'.clidiff.out', $diff);

    /* HH_IGNORE_ERROR[4107] using directly because this is open source */
    /* HH_IGNORE_ERROR[2049] using directly because this is open source */
    if (!\file_exists($base.'.clidiff.expect')) {
      self::markTestIncomplete(Str\format(
        "No expect file present; maybe:\n  cp %s.clidiff.out %s.clidiff.expect",
        $base,
        $base,
      ));
    }

    expect($diff)->toBeSame(
      \file_get_contents($base.'.clidiff.expect'),
      'Did not match expected contents (- %s.clidiff.expect, + %s.clidiff.out)',
      $base,
      $base,
    );
  }
}
