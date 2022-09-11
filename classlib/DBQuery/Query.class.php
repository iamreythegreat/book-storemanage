<?php

namespace DBQuery;

require_once __DIR__ . "/FieldFilter.class.php";
require_once __DIR__ . "/GroupFilter.class.php";
require_once __DIR__ . "/FilterColumn.class.php";

use \Exception;

class Query {
    public ?string $last_error = null;

    public function __construct(
        public ?string $sql = null,
        public ?string $group_by = null,
        array $filter_columns = [],
        array $having_columns = [],
        public string $filter_join = "AND",
        array $filters = [],
        array $ordering = [],
        public ?int $limit_start = null,
        public ?int $limit_length = null,
        public ?\PDO $db = null
    ) {
        /** @var Config $config */
        global $config;

        $this->db ??= $config->db;

        $this->setFilterColumns($filter_columns);
        $this->setHavingColumns($having_columns);
        $this->setFilters($filters);
        $this->setOrdering($ordering);
    }

    public function hasError() {
        return $this->last_error !== null;
    }

    /** @var FilterColumn[] */
    protected $filter_columns = [];
    public function setFilterColumns(array $columns) {
        $this->filter_columns = [];
        foreach ($columns as $data) {
            if ($data instanceof FilterColumn) {
                $this->filter_columns[] = $data;
            }

            if (gettype($data) !== "array") {
                continue;
            }

            $filter_column = FilterColumn::FromArray($data);
            if ($filter_column === null) {
                continue;
            }

            $this->filter_columns[] = $filter_column;
        }

        return $this;
    }

    /** @var FilterColumn[] */
    protected $having_columns = [];
    public function setHavingColumns(array $columns) {
        $this->having_columns = [];
        foreach ($columns as $data) {
            if ($data instanceof FilterColumn) {
                $this->having_columns[] = $data;
            }

            if (gettype($data) !== "array") {
                continue;
            }

            $having_column = FilterColumn::FromArray($data);
            if ($having_column === null) {
                continue;
            }

            $this->having_columns[] = $having_column;
        }

        return $this;
    }

    /** @var Filter[] */
    protected $filters = [];
    public function setFilters(array $filters) {
        $this->filters = [];
        foreach ($filters as $data) {
            if ($data instanceof Filter) {
                $this->filters[] = $data;
            }

            if (gettype($data) !== "array") {
                continue;
            }

            if (strtolower($data["type"] ?? "") === "field" || isset($data["alias"])) {
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

    protected array $ordering = [];
    public function setOrdering(array $ordering) {
        $this->ordering = [];
        foreach ($ordering as $order) {
            if (!isset($order["alias"])) {
                continue;
            }

            $direction = match (strtolower($order["direction"] ?? "")) {
                "desc"  => "DESC",
                default => "ASC",
            };

            $this->ordering[] = [
                "alias"     => strtolower($order["alias"]),
                "direction" => $direction,
            ];
        }

        return $this;
    }

    protected function query(?array $filters = null, ?int $limit_start = null, ?int $limit_length = null) {
        if ($filters !== null) {
            $this->filters = $filters;
        }

        if ($limit_start !== null) {
            $this->limit_start = $limit_start;
        }

        if ($limit_length !== null) {
            $this->limit_length = $limit_length;
        }

        $this->checkProperties();

        $sql = $this->getSql();

        $query = $this->db->prepare($sql);
        foreach ($this->bindings as $key => $data) {
            $query->bindValue(":{$key}", $data["value"], $data["type"]);
        }

        return $query;
    }

    public function fetch(?array $filters = null, ?int $limit_start = null, ?int $limit_length = null) {
        $this->last_error = null;
        $query = $this->query($filters, $limit_start, $limit_length);

        $success = $query->execute();
        if (!$success) {
            $this->last_error = join(" - ", $query->errorInfo());
        }

        if ($query->rowCount() === 0) {
            return [];
        }

        return $query->fetch();
    }

    public function fetchAll(?array $filters = null, ?int $limit_start = null, ?int $limit_length = null, ?int $mode = \PDO::FETCH_ASSOC) {
        $this->last_error = null;
        $query = $this->query($filters, $limit_start, $limit_length);

        $success = $query->execute();
        if (!$success) {
            $this->last_error = join(" - ", $query->errorInfo());
        }

        if ($query->rowCount() === 0) {
            return [];
        }

        return $query->fetchAll($mode);
    }

    public function count(?array $filters = null, ?int $limit_start = null, ?int $limit_length = null) {
        $this->last_error = null;
        $query = $this->query($filters, $limit_start, $limit_length);

        $success = $query->execute();
        if (!$success) {
            $this->last_error = join(" - ", $query->errorInfo());
        }

        return $query->rowCount();
    }

    public function checkProperties() {
        if ($this->sql === null) {
            throw new \Error("Main SQL not defined");
        }

        if ($this->group_by === null) {
            throw new \Error("\"GROUP BY\" clause not defined");
        }

        if ($this->limit_start !== null && $this->limit_length === null) {
            throw new \Error("\$limit_length must be set when using \$limit_start property");
        }
    }

    protected $bindings = [];
    public function getBindings(bool $refresh = true) {
        if ($refresh) {
            $this->getWhereSql();
        }

        return $this->bindings;
    }

    public function getGeneratedSql(bool $include_whitespace = false, bool $format_base_sql = false) {
        $sql = $this->getSql($include_whitespace, $format_base_sql);
        foreach ($this->bindings as $key => $binding) {
            if ($binding["type"] === \PDO::PARAM_INT) {
                $sql = str_replace(":{$key}", $binding["value"] ?? "NULL", $sql);
                continue;
            }

            $sql = str_replace(":{$key}", "\"{$binding["value"]}\"", $sql);
        }

        return $sql;
    }

    public function getSql(bool $include_whitespace = false, bool $format_base_sql = false) {
        $this->checkProperties();
        $where_sql = $this->getWhereSql($include_whitespace);
        $order_sql = $this->getOrderSql($include_whitespace);
        $limit_sql = $this->getLimitSql($include_whitespace);

        if ($format_base_sql) {
            // try to format a bit
            $sql = $this->sql;

            $spaces       = null;
            $part_matches = [];
            $raw_parts    = explode("\n", str_replace("\t", "    ", $sql));

            foreach ($raw_parts as $pi => $part) {
                preg_match_all("/^( +)/", $part, $matches);
                $part_matches[$pi] = $matches;
                if (count($matches[0]) === 0) {
                    continue;
                }

                $part_spaces = strlen($matches[0][0]);

                if ($spaces === null) {
                    $spaces = $part_spaces;
                    continue;
                }

                $spaces = min($spaces, $part_spaces);
            }

            if ($spaces !== null) {
                $parts = [];

                foreach ($raw_parts as $pi => $part) {
                    $matches = $part_matches[$pi];
                    if (count($matches[0]) === 0) {
                        $parts[] = $part;
                        continue;
                    }

                    $parts[] = str_repeat(" ", strlen($matches[0][0]) - $spaces) . trim($part);
                }

                $sql = join("\n", $parts);
            }

            return "{$sql} {$where_sql} {$this->group_by} {$order_sql} {$limit_sql}";
        }

        return "{$this->sql} {$where_sql} {$this->group_by} {$order_sql} {$limit_sql}";
    }

    public function getWhereSql(bool $include_whitespace = false) {
        $this->bindings = [];

        $binding_counter = 0;
        $where_parts = [];

        foreach ($this->filters as $filter) {
            $sql = $this->getFilterWhereSql($filter, $binding_counter, $include_whitespace);
            if ($sql === null) {
                continue;
            }

            $where_parts[] = $sql;
            $binding_counter++;
        }

        if (count($where_parts) === 0) {
            return "";
        }

        if ($include_whitespace) {
            return "\nWHERE\n\t" . join(" {$this->filter_join}\n\t", $where_parts);
        }

        return "WHERE " . join(" {$this->filter_join} ", $where_parts);
    }

    protected function getFilterWhereSql(Filter $filter, string $index, bool $include_whitespace = false) {
        if ($filter instanceof GroupFilter) {
            return $this->getGroupFilterWhereSql($filter, $index, $include_whitespace);
        }

        if ($filter instanceof FieldFilter) {
            return $this->getFieldFilterWhereSql($filter, $index, $include_whitespace);
        }

        return null;
    }

    protected function getGroupFilterWhereSql(GroupFilter $filter, string $index, bool $include_whitespace = false) {
        $group_index = 0;
        $filter_parts = [];
        foreach ($filter->getFilters() as $child_filter) {
            $sql = $this->getFilterWhereSql($child_filter, "{$index}_{$group_index}", $include_whitespace);
            if ($sql === null) {
                continue;
            }

            $filter_parts[] = $sql;
            $group_index++;
        }

        if (count($filter_parts) === 0) {
            return null;
        }

        if ($include_whitespace) {
            $depth = count(explode("_", strval($index)));
            $tabs  = str_repeat("\t", $depth);

            return "(\n{$tabs}\t" . join(" {$filter->getJoin()}\n{$tabs}\t", $filter_parts) . "\n{$tabs})";
        }

        return "(" . join(" {$filter->getJoin()} ", $filter_parts) . ")";
    }

    protected function getFieldFilterWhereSql(FieldFilter $filter, string $index, bool $include_whitespace = false) {
        $column = $this->getFilterColumnByAlias($filter->alias);
        if ($column === null) {
            return null;
        }

        if ($filter->getOperator() === null) {
            return null;
        }

        if (gettype($filter->value) === "array") {
            return $this->getArrayFilterWhereSql($column, $filter, $index);
        }

        $binding_key = "{$column->alias}_{$index}";

        $this->bindings[$binding_key] = ["value" => $filter->value, "type" => $filter->value_type];

        return "{$column->sql} {$filter->getOperator()} :{$binding_key}";
    }

    protected function getArrayFilterWhereSql(FilterColumn $column, FieldFilter $filter, string $index) {
        $value_index = 0;
        $value_parts = [];
        foreach ($filter->value as $value) {
            $binding_key = "{$column->alias}_{$index}_{$value_index}";

            $value_parts[] = ":{$binding_key}";
            $this->bindings[$binding_key] = ["value" => $value, "type" => $filter->value_type];

            $value_index++;
        }

        return "{$column->sql} {$filter->getOperator()} (" . join(", ", $value_parts) . ")";
    }

    public function getOrderSql(bool $include_whitespace = false) {
        $order_parts = [];

        foreach ($this->ordering as $order) {
            /** @var FilterColumn|null */
            $column = first($this->filter_columns, fn (FilterColumn $column) => $column->alias === $order["alias"]);
            if ($column === null) {
                continue;
            }

            $order_parts[] = "{$column->sql} {$order["direction"]}";
        }

        if (count($order_parts) === 0) {
            return "";
        }

        if ($include_whitespace) {
            return "\nORDER BY\n\t" . join(",\n\t", $order_parts);
        }

        return "ORDER BY " . join(", ", $order_parts);
    }

    public function getLimitSql(bool $include_whitespace = false) {
        if (
            $this->limit_start === null ||
            $this->limit_length === null
        ) {
            return "";
        }

        if ($include_whitespace) {
            return "\nLIMIT {$this->limit_start}, {$this->limit_length}";
        }

        return "LIMIT {$this->limit_start}, {$this->limit_length}";
    }

    /** @return FilterColumn|null */
    public function getFilterColumnByAlias(string $alias) {
        return first($this->filter_columns, fn (FilterColumn $column) => $column->alias === $alias);
    }
}
