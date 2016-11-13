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
class Est_Handler_Magento_ApiRole extends Est_Handler_Magento_AbstractApi
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
                 * param1 == role_id or role_name (role_id only if user to role connection)
                 * param2 == user_id or username  (usage to add a user to role)
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
                 * param1 == role_id or role_name
                 * param2 == user_id or username
                 */
                return $this->delete();
                break;
        }
        return true;
    }

    /**
     * Inserts an API role or
     * added an API user to API role
     *
     * @return bool
     */
    private function insert()
    {
        $this->_checkIfTableExists('api_role');
        $this->setValue("INSERT");

        // Add user to role
        if (strlen($this->getParam1()) > 0 && strlen($this->getParam2()) > 0) {
            $role = $this->getRole($this->getParam1());
            if ($role === false) {
                $this->log(sprintf("Could not load API role %s. Error",
                    $this->getParam1()),
                    Est_Message::ERROR);
                return false;
            }
            $user = $this->getApiUser($this->getParam2());
            if ($user === false) {
                $this->log(sprintf("Could not load API user %s. Error",
                    $this->getParam2()),
                    Est_Message::ERROR);
                return false;
            }
            $queryParams = array(
                ":parent_id" => (int)$role['role_id'],
                ':user_id'   => (int)$user['user_id']
            );
            $query
                = "SELECT * FROM api_role WHERE user_id = :user_id AND parent_id = :parent_id;";
            $result = $this->_getFirstRow($query, $queryParams);
            if ($result === false) {
                $queryParams[':role_type'] = 'U';
                $queryParams[':role_name'] = $user['username'];
                $query
                    = "INSERT INTO api_role (parent_id, tree_level, sort_order, role_type, user_id, role_name) VALUES (:parent_id, 0, 0, :role_type, :user_id, :role_name)";

                $result = $this->getDbConnection()->prepare($query)
                    ->execute($queryParams);
                if ($result === false) {
                    $this->log(sprintf("Could not add API user %s to API role %s. Error.",
                        $user['username'], $role['role_name']),
                        Est_Message::ERROR);
                    return false;
                }
                $this->log(sprintf("Added API user %s to API role %s",
                    $user['username'], $role['role_name']));
            } else {
                $this->log(sprintf("API user %s is already added to API role %s. Skipping",
                    $user['username'], $role['role_name']),
                    Est_Message::SKIPPED);
                return true;
            }

        } // Add new role
        elseif (strlen($this->getParam1()) > 0
            && strlen($this->getParam2()) == 0
        ) {
            $role = $this->getRole($this->getParam1());
            if ($role !== false) {
                $this->log($this->getParam1() . " is already there. Skipping.",
                    Est_Message::SKIPPED);
                return true;
            }
            $queryParams = array("role_name" => $this->getParam1());
            $query
                = "INSERT INTO api_role (parent_id, tree_level, sort_order, role_type, user_id, role_name) VALUES (0, 0, 0, 'G', 0, :role_name)";
            $result = $this->getDbConnection()->prepare($query)
                ->execute($queryParams);
            if ($result === false) {
                $this->log(sprintf("Could not add API role %s due unknown reason. Error.",
                    $this->getParam1()), Est_Message::ERROR);
                return false;
            }
            $this->log(sprintf("Added API role %s.", $this->getParam1()),
                Est_Message::OK);
        }
        return true;
    }

    /**
     * Updates an API role or an API user and role connection
     *
     * @return bool
     */
    private function update()
    {
        $this->_checkIfTableExists('api_role');
        $this->setValue("UPDATE");
        $this->log("Update feature not implemented yet. Skipping.",
            Est_Message::SKIPPED);
        return true;
    }

    /**
     * Deletes an API role, deletes an API role connection
     *
     * @return bool
     */
    private function delete()
    {
        $this->_checkIfTableExists('api_role');
        $this->setValue("DELETE");

        // Remove API user from API role
        if (strlen($this->getParam1()) > 0 && strlen($this->getParam2()) > 0) {
            $role = $this->getRole($this->getParam1());
            if ($role === false) {
                $this->log(sprintf("Could not load API role %s. Error.",
                    $this->getParam1()),
                    Est_Message::ERROR);
                return false;
            }

            $user = $this->getApiUser($this->getParam2());
            if ($user === false) {
                $this->log(sprintf("Could not load API user %s. Error.",
                    $this->getParam2()),
                    Est_Message::ERROR);
                return false;
            }

            // is connected already ?
            $queryParams = array(
                ":role_type" => 'U',
                ':user_id'   => $user['user_id'],
                ':parent_id' => $role['role_id']
            );

            $query
                = "DELETE FROM api_role WHERE parent_id = :parent_id AND user_id = :user_id AND role_type = :role_type;";

            $result = $this->getDbConnection()->prepare($query)
                ->execute($queryParams);
            if ($result === false) {
                $this->log(sprintf("Could not remove API user %s from API role %s. Error.",
                    $user['username'], $role['role_name']),
                    Est_Message::ERROR);
                return false;
            }
            $this->log(sprintf("Removed API user %s from API role %s.",
                $user['username'], $role['role_name']));

        } // Remove API role
        elseif
        (strlen($this->getParam1()) > 0
            && strlen($this->getParam2()) == 0
        ) {
            $role = $this->getRole($this->getParam1());
            if ($role === false) {
                $this->log(sprintf("API role %s is already deleted. Skipping.",
                    $this->getParam1()),
                    Est_Message::SKIPPED);
                return true;
            }

            $queryParams = array(
                ":role_type" => 'G',
                ':role_id'   => $role['role_id']
            );
            $query
                = "DELETE FROM api_role WHERE role_id = :role_id AND role_type = :role_type LIMIT 1;";

            $result = $this->getDbConnection()->prepare($query)
                ->execute($queryParams);
            if ($result === false) {
                $this->log(sprintf("Could not remove API role %s. Error.",
                    $role['role_name']),
                    Est_Message::ERROR);
                return false;
            }

            // delete also connections to users
            $isConnected
                = $this->_getFirstRow("SELECT * FROM api_role WHERE role_id = :role_id",
                array(":role_id" => $role['role_id']));
            if ($isConnected !== false) {
                $this->getDbConnection()->prepare("DELETE FROM api_role WHERE role_id = :role_id")
                    ->execute(array(":role_id" => $role['role_id']));
            }

            $this->log(sprintf("Removed API role %s.",
                $role['role_name']), Est_Message::OK);
        }

        return true;
    }


    /**
     * Returns an API role by given id or name
     *
     * @param string $roleName
     *
     * @return mixed
     */
    private function getRole($roleName)
    {
        return $this->getApiRole($roleName);
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
    protected function log(
        $message,
        $type = Est_Message::INFO
    ) {
        $this->addMessage(
            new Est_Message($message, $type)
        );
    }


}
