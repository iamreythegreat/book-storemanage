<?php

namespace DBQuery;

require_once __DIR__ . "/Filter.class.php";

use \Exception;

class FieldFilter extends Filter {
    protected ?string $operator = null;
    public mixed $value = null;
    public int $value_type = \PDO::PARAM_STR;

    /**
     * @param string $alias This should match a `FilterColumn::$alias`
     */
    public function __construct(
        public string $alias,
        ?string $operator = null,
        mixed $value = null,
        int|string $value_type = \PDO::PARAM_STR,
    ) {

        $this
            ->setOperator($operator)
            ->setValueType($value_type)
            ->setValue($value);
    }

    protected static $allowed_operators = [
        "="        => 1,
        "<=>"      => 1,
        ">"        => 1,
        ">="       => 1,
        "<"        => 1,
        "<="       => 1,
        "<>"       => 1,
        "!="       => 1,
        "LIKE"     => 1,
        "NOT LIKE" => 1,
        "IS"       => 1,
        "IS NOT"   => 1,
        "NOT"      => 1,
        "IN"       => 1,
        "NOT IN"   => 1,
    ];

    public function setOperator(?string $input) {
        if ($input === null) {
            return $this;
        }

        $input = strtoupper(trim($input));
        if (isset(static::$allowed_operators[$input])) {
            $this->operator = $input;
        }

        return $this;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function setValue(mixed $value) {
        if ($this->value_type === \PDO::PARAM_NULL || ($this->value_type === \PDO::PARAM_INT && $value === \APP::NULL_SEQUENCE)) {
            $value = null;
        }

        $this->value = $value;

        return $this;
    }

    public function setValueType(int|string $value_type) {
        if (gettype($value_type) === "string") {
            $value_type = match ($value_type) {
                "int", "integer" => \PDO::PARAM_INT,
                "null" => \PDO::PARAM_NULL,
                default => \PDO::PARAM_STR,
            };
        }

        $this->value_type = $value_type;

        return $this;
    }

    /** @return FieldFilter|null */
    public static function FromArray(array $array) {
        $alias       = $array["alias"] ?? null;
        $operator    = $array["operator"] ?? null;
        $value       = $array["value"] ?? null;
        $value_type  = $array["value_type"] ?? \PDO::PARAM_STR;

        if (
            $alias === null ||
            $operator === null
        ) {
            return null;
        }

        $value_type_type = gettype($value_type);
        if ($value_type_type !== "integer" && $value_type_type !== "string") {
            return null;
        }

        return new FieldFilter($alias, $operator, $value, $value_type);
    }
}
