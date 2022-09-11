<?php

namespace DBQuery;

use \Exception;

abstract class Filter {
    public function __construct() {
        //
    }

    abstract static function FromArray(array $array);
}
