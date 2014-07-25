<?php

/**
 * Abstract database handler class
 *
 * @author Fabrizio Branca
 * @since 2012-09-20
 */
abstract class Est_Handler_AbstractDatabase extends Est_Handler_Abstract {

    /**
     * @var PDO
     */
    protected $dbConnection;

    /**
     * Get database connection parameter
     *
     * Expected keys:
     * - host
     * - database
     * - username
     * - password
     *
     * @return array
     */
    abstract protected function _getDatabaseConnectionParameters();

    /**
     * Get database connection
     *
     * @return PDO
     * @throws Exception
     */
    protected function getDbConnection() {
        if (is_null($this->dbConnection)) {
            $dbParameters = $this->_getDatabaseConnectionParameters();
            if (!is_array($dbParameters)) {
                throw new Exception('No valid database connection parameters found');
            }
            foreach (array('host', 'database', 'username', 'password') as $key) {
                if (!isset($dbParameters[$key]) || empty($dbParameters[$key])) {
                    throw new Exception(sprintf('No "%s" found in database connection parameters', $key));
                }
            }
            $this->dbConnection = new PDO(
                "mysql:host={$dbParameters['host']};dbname={$dbParameters['database']}",
                $dbParameters['username'],
                $dbParameters['password']
            );
        }
        return $this->dbConnection;
    }

}
