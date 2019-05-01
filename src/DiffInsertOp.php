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

/** An operation representing an insertion into the sequence */
final class DiffInsertOp<TContent> extends DiffOp<TContent> {
  public function __construct(
    private int $newPos,
    private TContent $content,
  ) {
  }

  public function getNewPos(): int {
    return $this->newPos;
  }

  <<__Override>>
  public function getContent(): TContent {
    return $this->content;
  }

  <<__Override>>
  public function isInsertOp(): bool {
      return true;
  }

  <<__Override>>
  public function asInsertOp(): DiffInsertOp<TContent> {
    return $this;
  }
}
