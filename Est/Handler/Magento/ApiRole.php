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
 * Class Est_Handler_Magento_ApiRole
 */
class Est_Handler_Magento_ApiRole extends Est_Handler_Magento_AbstractDatabase
{

    /**
     * Protected method that actually applies the settings.
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply()
    {
        $action = $this->detectDatabaseAction($this->value);
        switch ($action) {
            case Est_Handler_Magento_AbstractDatabase::ACTION_NO_ACTION:
                return true;
                break;
            case Est_Handler_Magento_AbstractDatabase::ACTION_INSERT:
                /**
                 * param1 ==
                 * param2 ==
                 * param3 ==
                 */
                return $this->insert();
                break;
            case Est_Handler_Magento_AbstractDatabase::ACTION_UPDATE:
                /**
                 * param1 ==
                 * param2 ==
                 * param3 ==
                 */
                return $this->update();
                break;
            case Est_Handler_Magento_AbstractDatabase::ACTION_DELETE:
                /**
                 * param1 ==
                 * param2 ==
                 * param3 ==
                 */
                return $this->delete();
                break;
        }
        return true;
    }

    private function insert()
    {

        $this->_checkIfTableExists('api_user');
        $this->setValue("INSERT");
        return true;
    }

    private function update()
    {
        $this->_checkIfTableExists('api_user');
        $this->setValue("UPDATE");

        return true;
    }

    private function deleteUser($userId)
    {
        $this->_checkIfTableExists('api_user');
        $this->setValue("DELETE");
        return true;
    }


    /**
     * Returns an API user by given id oder username
     *
     * @param string $value
     *
     * @return bool|array
     */
    private function getApiUser($value)
    {
        $basicQuery = 'SELECT * FROM api_user WHERE ';
        $queryParams = array(':value' => $value);
        $apiUser = $this->_getFirstRow($basicQuery . '`user_id` = :value',
            $queryParams
        );
        if ($apiUser === false) {
            $apiUser = $this->_getFirstRow($basicQuery . '`username` = :value',
                $queryParams);
        }
        return $apiUser;
    }

    /**
     * Returns the database action
     *
     * @param string $value
     *
     * @return int
     */
    private function detectDatabaseAction($value)
    {
        switch ($value) {
            case '--delete--':
                return Est_Handler_Magento_AbstractDatabase::ACTION_DELETE;
                break;
            case '--insert--':
                return Est_Handler_Magento_AbstractDatabase::ACTION_INSERT;
                break;
            case '--update--':
                return Est_Handler_Magento_AbstractDatabase::ACTION_UPDATE;
                break;
            case '--skip--':
                return Est_Handler_Magento_AbstractDatabase::ACTION_NO_ACTION;
                break;
            default:
                return $value;

        }
    }

    /**
     * Logs a message
     *
     * @param string $message
     * @param int    $type
     */
    protected function log($message, $type = Est_Message::INFO)
    {
        $this->addMessage(
            new Est_Message($message, $type)
        );
    }


}
