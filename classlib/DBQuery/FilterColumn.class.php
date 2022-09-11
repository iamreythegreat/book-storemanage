<?php

namespace DBQuery;

use \Exception;

class FilterColumn {
    public string $alias;

    public function __construct(
        string $alias,
        public string $sql,
    ) {
        $this->alias = static::AliasToParam($alias);
    }

    public static function AliasToParam(string $alias) {
        return strtolower(trim(preg_replace("/[\s\W]+/", "_", $alias), " \t\n\r\0\x0B_"));
    }

    /** @return FieldFilter|null */
    public static function FromArray(array $array) {
        $alias = $array["alias"] ?? null;
        $sql    = $array["sql"] ?? null;

        if (
            $alias === null ||
            $sql === null ||
            gettype($alias) !== "string" ||
            gettype($sql) !== "string"
        ) {
            return null;
        }

        return new FilterColumn($alias, $sql);
    }
}
