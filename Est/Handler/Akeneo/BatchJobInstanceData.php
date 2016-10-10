<?php

/**
 * Parameters
 *
 * - jobCode
 * - key
 */
class Est_Handler_Akeneo_BatchJobInstanceData extends Est_Handler_Akeneo_AbstractDatabase
{
    /**
     * Akeneo Export Job Config Table
     * @var string
     */
    private $_tableName = 'akeneo_batch_job_instance';
    /**
     * Field where serialized configuration is stored
     * @var string
     */
    private $_configField = 'rawConfiguration';

    protected function _apply()
    {
        $this->_checkIfTableExists($this->_tableName);

        $jobCode = $this->param1;
        $key     = $this->param2;

        $sqlParameters = array(
            ':code' => $jobCode
        );

        // Decide on Action, skipping Insert
        if (strtolower(trim($this->value)) == '--delete--') {
            $action = self::ACTION_DELETE;
        } else {
            $action = self::ACTION_UPDATE;
        }

        $query = 'SELECT `'.$this->_configField.'` FROM `' . $this->_tablePrefix . $this->_tableName . '` WHERE `code` LIKE :code';
        $firstRow = $this->_getFirstRow($query, $sqlParameters);

        if ($firstRow === false) {
            $this->addMessage(
                new Est_Message('No matching rows found in the db', Est_Message::SKIPPED)
            );
        } else {
            $configData = unserialize($firstRow[$this->_configField]);

            switch ($action) {
                case self::ACTION_DELETE:
                    // Deletes the export job -> possibly necessary in some environments
                    $configData[$key] = '';
                    $query = 'DELETE FROM `' . $this->_tablePrefix . $this->_tableName . '` WHERE `code` LIKE :code';
                    $this->_processDelete($query, $sqlParameters);
                    break;
                case self::ACTION_UPDATE:
                    // Replaces a value in the given export job configuration
                    $configData[$key] = $this->value;
                    $sqlParameters[':value'] = serialize($configData);
                    $query = 'UPDATE `' . $this->_tablePrefix . $this->_tableName . '` SET `' . $this->_configField . '` = :value WHERE `code` LIKE :code';
                    $this->_processUpdate($query, $sqlParameters, $configData[$key]);
                    break;
                case self::ACTION_NO_ACTION;
                default:
                    break;
            }
        }

        $this->destroyDb();

        return true;
    }
}
