<?php
/**
 * aoepeople/envsettingstool
 *
 * Extended by meets-ecommerce.de
 *
 * @copyright   Copyright (c) 2016 meets-ecommerce UG (haftungsbeschränkt) (http://meets-ecommerce.de)
 * @author      Daniel Matuschewsky <dm@meets-ecommerce.de>
 */

/**
 * Class Est_Handler_Magento_DatabaseTableDrop
 */
class Est_Handler_Magento_DatabaseTableDrop
    extends Est_Handler_Magento_AbstractDatabaseTable
{

    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply()
    {
        /**
         * - Param1 == table        // to truncate
         * - Param2 == no_fk_check  // no foreign key checks
         *
         * - Value  == --drop--     // indicates to drop it
         *             --skip--     // indicates to skip it
         */

        if (strtolower($this->getValue()) !== '--drop--') {
            $this->setStatus(Est_Handler_Interface::STATUS_SKIPPED);
            $this->addMessage(new Est_Message(
                sprintf('Dropping tables skipped. Not defined --drop-- in value.'),
                Est_Message::SKIPPED
            ));
            return true;
        }


        $tables = $this->getTables();
        if ($tables === false) {
            $this->addMessage(new Est_Message(
                sprintf('Cannot drop table %s. Whether table does exist or * is not allowed.',
                    $this->getParam1()),
                Est_Message::ERROR
            ));
            return false;
        }

        foreach ($tables as $key => $table) {
            try {
                $this->_checkIfTableExists($table);
            } catch (Exception $e) {
                unset($tables[$key]);
            }
        }

        if (trim($this->getParam2()) === 'no_fk_check') {
            $this->unsetForeignKeyCheck();
        }

        $errorMessages = array();

        foreach ($tables as $table) {
            $result = $this->getDbConnection()->prepare("DROP TABLE "
                . $table . ";")
                ->execute();

            if ($result === false) {
                $errorMessages[$table] = "Table " . $table . " > "
                    . var_export($this->getDbConnection()->errorInfo(),
                        true);
            }
        }

        if (trim($this->getParam2()) === 'no_fk_check') {
            $this->setForeignKeyCheck();
        }

        if (count($errorMessages)) {
            $this->addMessage(new Est_Message(
                sprintf("Cannot drop table(s): \n%s",
                    implode("\n", $errorMessages)
                ),
                Est_Message::ERROR
            ));

            return false;
        }

        $this->addMessage(new Est_Message(
            sprintf('Dropped table(s) %s.', implode(",", $tables)),
            Est_Message::OK
        ));

        return true;
    }

}