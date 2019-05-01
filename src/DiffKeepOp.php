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

/** An operation indicating that this element in the sequence is unchanged */
final class DiffKeepOp<TContent> extends DiffOp<TContent> {
  public function __construct(
    private int $oldPos,
    private int $newPos,
    private TContent $content,
  ) {
  }

  public function getOldPos(): int {
    return $this->oldPos;
  }

  public function getNewPos(): int {
    return $this->newPos;
  }

  <<__Override>>
  public function getContent(): TContent {
    return $this->content;
  }

  <<__Override>>
  public function isKeepOp(): bool {
    return true;
  }

  <<__Override>>
  public function asKeepOp(): DiffKeepOp<TContent> {
    return $this;
  }
}
