<?php

/**
 * Abstract magento database handler class
 *
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoe.com>
 */
abstract class Est_Handler_Akeneo_AbstractDatabase extends Est_Handler_AbstractDatabase
{
    /**@+
     * Actions to apply on row
     *
     * @var string
     */
    const ACTION_NO_ACTION = 0;
    const ACTION_INSERT = 1;
    const ACTION_UPDATE = 2;
    const ACTION_DELETE = 3;
    /**@-*/

    /**
     * Table prefix
     *
     * @var string
     */
    protected $_tablePrefix = '';

    /**
     * Read database connection parameters from local.xml file
     *
     * @return array
     * @throws Exception
     */
    protected function _getDatabaseConnectionParameters()
    {
        // Get Yml Reader if not already present
        if (!class_exists('Spyc')) {
            $currentPath = __DIR__;
            $vendorPos = strpos(strtolower($currentPath), 'vendor');
            if (!$vendorPos) {
                throw new Exception("Composer Vendor Directory not found in current Path, needed to require Spyc.");
            }
            $include = substr($currentPath, 0, $vendorPos) . 'vendor/mustangostang/spyc/Spyc.php';
            require_once $include;
        }
        $localYmlFile = 'app/config/parameters.yml';

        if (!is_file($localYmlFile)) {
            throw new Exception(sprintf('File "%s" not found', $localYmlFile));
        }

        $config = spyc_load_file($localYmlFile);

        if ($config === false) {
            throw new Exception(sprintf('Could not load yml file "%s"', $localYmlFile));
        }

        return array(
            'host'     => (string) $config['parameters']['database_host'],
            'database' => (string) $config['parameters']['database_name'],
            'username' => (string) $config['parameters']['database_user'],
            'password' => (string) $config['parameters']['database_password']
        );
    }

    /**
     * @param string $table
     * @throws Exception
     */
    protected function _checkIfTableExists($table)
    {
        $result = $this->getDbConnection()
                       ->query("SHOW TABLES LIKE \"{$this->_tablePrefix}{$table}\"");
        if ($result->rowCount() == 0) {
            throw new Exception("Table \"{$this->_tablePrefix}{$table}\" doesn't exist");
        }
    }

    /**
     * Get first row query
     *
     * @param string $query
     * @param array $sqlParameters
     * @return mixed
     */
    protected function _getFirstRow($query, array $sqlParameters)
    {
        $statement = $this->getDbConnection()->prepare($query);
        $statement->execute($sqlParameters);
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        return $statement->fetch();
    }

    /**
     * Process delete query
     *
     * @param string $query
     * @param array $sqlParameters
     * @throws Exception
     */
    protected function _processDelete($query, array $sqlParameters)
    {
        $pdoStatement = $this->getDbConnection()->prepare($query);
        $result       = $pdoStatement->execute($sqlParameters);

        if ($result === false) {
            throw new Exception('Error while deleting rows');
        }

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount > 0) {
            $this->addMessage(new Est_Message(sprintf('Deleted "%s" row(s)', $rowCount)));
        } else {
            $this->addMessage(new Est_Message('No rows deleted.', Est_Message::SKIPPED));
        }
    }

    /**
     * Process insert query
     *
     * @param string $query
     * @param array $sqlParameters
     * @throws Exception
     */
    protected function _processInsert($query, array $sqlParameters)
    {
        $result = $this->getDbConnection()
            ->prepare($query)
            ->execute($sqlParameters);

        if ($result === false) {
            // TODO: include speaking error message
            throw new Exception('Error while updating value');
        }

        $this->addMessage(new Est_Message(sprintf('Inserted new value "%s"', $this->value)));
    }

    /**
     * Process update query
     *
     * @param string $query
     * @param array $sqlParameters
     * @param string $oldValue
     * @throws Exception
     */
    protected function _processUpdate($query, array $sqlParameters, $oldValue)
    {
        $result = $this->getDbConnection()
            ->prepare($query)
            ->execute($sqlParameters);

        if ($result === false) {
            // TODO: include speaking error message
            throw new Exception('Error while updating value');
        }

        $this->addMessage(new Est_Message(sprintf('Updated value from "%s" to "%s"',
            $oldValue,
            $this->value))
        );
    }
}
