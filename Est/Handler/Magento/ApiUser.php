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
 * Class Est_Handler_Magento_ApiUser
 */
class Est_Handler_Magento_ApiUser extends Est_Handler_Magento_AbstractApi
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
                 * param1 == username
                 * param2 == email
                 * param3 == api_key
                 */
                return $this->createUser($this->param1, $this->param2,
                    $this->param3);
                break;
            case Est_Handler_Magento_AbstractDatabase::ACTION_UPDATE:
                /**
                 * param1 == user_id or username
                 * param2 == field
                 * param3 == value
                 */
                return $this->updateUser($this->param1, $this->param2,
                    $this->param3);
                break;
            case Est_Handler_Magento_AbstractDatabase::ACTION_DELETE:
                /**
                 * param1 == user_id or username
                 */
                return $this->deleteUser($this->param1);
                break;
        }
        return true;
    }

    /**
     * Creates an API user
     *
     * @param string $userName
     * @param string $email
     * @param string $apiKey
     *
     * @return bool
     */
    private function createUser($userName, $email, $apiKey)
    {

        $this->_checkIfTableExists('api_user');
        $this->setValue("INSERT");
        $apiUser = $this->getApiUser($userName);

        if ($apiUser !== false) {
            $this->log(sprintf("API User %s exists already.", $userName),
                Est_Message::SKIPPED);
            return true;
        }

        $queryParams = array(
            ':username'  => $userName,
            ':firstname' => $userName,
            ':lastname'  => $userName,
            ':email'     => $email,
            ':api_key'   => md5($apiKey),
        );
        $query
            = 'INSERT INTO api_user (username, firstname, lastname, email, api_key) VALUES(:username, :firstname, :lastname, :email, :api_key);';

        $result = $this->getDbConnection()->prepare($query)
            ->execute($queryParams);

        if ($result === false) {
            $this->log(sprintf("Could not create user %s", $userName),
                Est_Message::ERROR);
            return false;
        }

        $this->log(sprintf("Created API User %s.", $userName), Est_Message::OK);
        return true;
    }

    /**
     * Updates an user field
     *
     * @param string $userNameOrId
     * @param string $field
     * @param string $value
     *
     * @return bool
     */
    private function updateUser($userNameOrId, $field, $value)
    {
        $this->_checkIfTableExists('api_user');
        $this->setValue("UPDATE");
        $apiUser = $this->getApiUser($userNameOrId);
        if ($apiUser === false) {
            $this->log(sprintf("Could not find API user with name or id %s.",
                Est_Message::ERROR));
            return false;
        }
        if ($field === "api_key") {
            $value = md5($value);
        }
        $queryParams = array(
            ':value'   => $value,
            ':user_id' => $apiUser['user_id']
        );
        $query = "UPDATE api_user SET `" . $field
            . "` = :value WHERE user_id = :user_id";
        $result = $this->getDbConnection()->prepare($query)
            ->execute($queryParams);
        if ($result === false) {
            $this->log("Could not updated user.", Est_Message::ERROR);
            return false;
        }

        $this->log(sprintf("Updated API User %s.", $userNameOrId), Est_Message::OK);
        return true;
    }

    /**
     * Deletes an API user
     *
     * @param $userId can be user_id or username
     *
     * @return bool
     */
    private function deleteUser($userId)
    {
        $this->_checkIfTableExists('api_user');
        $this->setValue("DELETE");
        $apiUser = $this->getApiUser($userId);
        if ($apiUser === false) {
            $this->log(sprintf("API User already deleted.",
                Est_Message::SKIPPED));
            return true;
        }

        $queryParams = array(':user_id' => $apiUser['user_id']);
        $query = "DELETE FROM api_user WHERE user_id = :user_id LIMIT 1;";

        $result = $this->getDbConnection()->prepare($query)
            ->execute($queryParams);
        if ($result === false) {
            $this->log(sprintf("Could not delete API User with id %s for unknown reason.",
                $userId));
            return false;
        }

        // delete also connections to roles
        $isConnected
            = $this->_getFirstRow("SELECT * FROM api_role WHERE user_id = :user_id",
            array(":user_id" => $apiUser['user_id']));
        if ($isConnected !== false) {
            $this->getDbConnection()->prepare("DELETE FROM api_role WHERE user_id = :user_id")
                ->execute(array(":user_id" => $apiUser['user_id']));
        }

        $this->log(sprintf("Deleted API User with id %s.", $userId));
        return true;
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
