<?php

namespace Cian;

interface EventInterface {

  public const ON_SAVE_COMPETITORS = 'OnCompetitors';

  public const ON_SAVE_PRICE = 'OnSavePrice';

  public function listen(string $event, callable $callback) : void;
  public function notify(string $event, ...$argc) : void;


}