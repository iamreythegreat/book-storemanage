<?php
class NestedPDO extends PDO
{
    /**
    * @param string $dsn
    * @param string $username
    * @param string $passwd
    * @param array $options
    */
    public function __construct($dsn, $username, $passwd, $options){
        parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ["NestedPDOStatement", [$this]]);
    }

    /**
    * @var array Database drivers that support SAVEPOINT * statements.
    */
    protected static $_supportedDrivers = array("pgsql", "mysql");

    /**
    * @var int the current transaction depth
    */
    protected $_transactionDepth = 0;


    /**
    * Test if database driver support savepoints
    *
    * @return bool
    */
    protected function hasSavepoint()
    {
        return in_array($this->getAttribute(PDO::ATTR_DRIVER_NAME),
            self::$_supportedDrivers);
    }


    /**
    * Start transaction
    *
    * @return bool|void
    */
    public function beginTransaction()
    {
        if($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            parent::beginTransaction();
        } else {
            $this->exec("SAVEPOINT LEVEL{$this->_transactionDepth}");
        }

        $this->_transactionDepth++;

        return $this->_transactionDepth;
    }

    /**
    * Commit current transaction
    *
    * @return bool|void
    */
    public function commit()
    {
        $this->_transactionDepth--;

        if($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            parent::commit();
        } else {
            $this->exec("RELEASE SAVEPOINT LEVEL{$this->_transactionDepth}");
        }
    }

    /**
    * Rollback current transaction,
    *
    * @throws PDOException if there is no transaction started
    * @return bool|void
    */
    public function rollBack()
    {

        if ($this->_transactionDepth == 0) {
            throw new PDOException('Rollback error : There is no transaction started');
        }

        $this->_transactionDepth--;

        if($this->_transactionDepth == 0 || !$this->hasSavepoint()) {
            parent::rollBack();
        } else {
            $this->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->_transactionDepth}");
        }
    }

    public function inTransaction(?int $depth = null)
    {
        if($this->_transactionDepth === 0){
            return false;
        }

        if(!is_null($depth) && $this->_transactionDepth !== $depth){
            return false;
        }

        return true;
    }
}