<?php
class NestedPDOStatement extends \PDOStatement {
    public $dbh;

    private function __construct($dbh) {
        $this->dbh = $dbh;
    }
    /**
     * @param array $input_parameters
     */
    public function execute($input_parameters = null): bool {
        try {
            return parent::execute($input_parameters);
        } catch (Exception $e) {
            $this->error_info = [$e->getMessage()];
            return false;
        }
    }

    private $error_info;

    public function errorInfo(): array {
        if (!isset($this->error_info)) {
            return parent::errorInfo();
        }

        return $this->error_info;
    }
}
