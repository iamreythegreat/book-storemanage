<?php

namespace DBQuery;

require_once __DIR__ . "/Filter.class.php";

use \Exception;

class GroupFilter extends Filter {
    public function __construct(
        array $filters = [],
        string $join = "AND",
    ) {
        $this
            ->setFilters($filters)
            ->setJoin($join);
    }

    /** @var Filter[] */
    protected array $filters = [];
    public function getFilters() {
        return $this->filters;
    }

    public function setFilters(array $filters) {
        $this->filters = [];
        foreach ($filters as $data) {
            if ($data instanceof Filter) {
                $this->filters[] = $data;
            }

            if (gettype($data) !== "array") {
                continue;
            }

            if (strtolower($data["type"] ?? "") === "alias" || isset($data["alias"])) {
                $filter = FieldFilter::FromArray($data);
                if ($filter !== null) {
                    $this->filters[] = $filter;
                }
                continue;
            }

            if (strtolower($data["type"] ?? "") !== "group") {
                continue;
            }

            $filter = GroupFilter::FromArray($data);
            if ($filter !== null) {
                $this->filters[] = $filter;
            }
        }

        return $this;
    }

    protected static $allowed_joins = [
        "AND" => 1,
        "OR" => 1,
    ];

    protected string $join = "AND";
    public function setJoin(string $join) {
        $join = strtoupper(trim($join));
        if (isset(static::$allowed_joins[$join])) {
            $this->join = $join;
        }

        return $this;
    }

    public function getJoin() {
        return $this->join;
    }

    public static function FromArray(array $array) {
        $filters = $array["filters"] ?? [];
        $join    = $array["join"] ?? "AND";

        return new GroupFilter($filters, $join);
    }
}
