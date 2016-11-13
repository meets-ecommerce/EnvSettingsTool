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
 * Class Est_Handler_Magento_ApiRule
 */
class Est_Handler_Magento_ApiRule extends Est_Handler_Magento_AbstractApi
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
                 * param1 == role_id or role_name
                 * param2 == resource_id // many: can be comma separated
                 * param3 == allow|deny
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
                 * param2 == resource_id // many: can be comma separated OR * for all
                 */
                return $this->delete();
                break;
        }
        return true;
    }

    /**
     * Inserts one or many rule entries
     *
     * @return bool
     */
    private function insert()
    {
        $this->_checkIfTableExists('api_user');
        $this->setValue("INSERT");

        if (!in_array($this->getParam3(), array('deny', 'allow'))) {
            $this->log(sprintf("Could not add permissions with type %s. Error.",
                $this->getParam3()),
                Est_Message::ERROR);
            return false;
        }

        $role = $this->getApiRole($this->getParam1());
        if ($role === false) {
            $this->log(sprintf("Could not load API role %s. Error.",
                $this->getParam1()),
                Est_Message::ERROR);
            return false;
        }

        $resourceIds = explode(",", $this->getParam2());
        if (count($resourceIds) > 0) {
            $queryParams = array(
                ':role_id'        => $role['role_id'],
                ':api_permission' => $this->getParam3()
            );
            $query
                = "INSERT INTO api_rule (role_id, resource_id, api_privileges, assert_id, role_type, api_permission) VALUES (:role_id, :resource_id, NULL, 0, 'G', :api_permission);";
            foreach ($resourceIds as $resourceId) {
                $resourceId = trim($resourceId);
                if ($this->isRule($role['role_id'], $resourceId) == false) {
                    $queryParams[':resource_id'] = $resourceId;
                    var_dump($queryParams);
                    $result = $this->getDbConnection()->prepare($query)
                        ->execute($queryParams);

                    if ($result === false) {
                        $this->log(sprintf("Could not add resource %s to API role %s. Error.",
                            $resourceId, $role['role_name']),
                            Est_Message::ERROR);
                        return false;
                    }
                }
            }
            $this->log(sprintf("Added following resource(s) %s to API role %s.",
                implode(",", $resourceIds), $role['role_name']),
                Est_Message::OK);
        } else {
            $this->log(sprintf("No resources to add to API role %s.",
                $role['role_name']), Est_Message::OK);
        }
        return true;
    }

    /**
     * Updates one or many rule entries
     *
     * @return bool
     */
    private function update()
    {
        $this->_checkIfTableExists('api_rule');
        $this->setValue("UPDATE");
        $this->log("Update feature not implemented yet. Skipping.",
            Est_Message::SKIPPED);
        return true;
    }

    /**
     * Deletes one or many rule entries
     *
     * @return bool
     */
    private function delete()
    {
        $this->_checkIfTableExists('api_rule');
        $this->setValue("DELETE");

        $role = $this->getApiRole($this->getParam1());
        if ($role === false) {
            $this->log(sprintf("Could not load API role %s. Error.",
                $this->getParam1()),
                Est_Message::ERROR);
            return false;
        }

        $resourceIds = explode(",", $this->getParam2());
        if (count($resourceIds) > 0) {
            $queryParams = array(
                ':role_id' => $role['role_id'],
            );
            $query
                = "DELETE FROM api_rule WHERE role_id = :role_id";

            foreach ($resourceIds as $resourceId) {
                if ($resourceId == '*') {
                    $this->getDbConnection()->prepare($query)
                        ->execute($queryParams);
                    $this->log(sprintf("Removed all resources from API role %s.",
                        $role['role_name']),
                        Est_Message::OK);
                    return true;
                }

                $queryParams[':resource_id'] = trim($resourceId);
                $query .= ' AND resource_id = :resource_id';
                $this->getDbConnection()->prepare($query)
                    ->execute($queryParams);

            }
            $this->log(sprintf("Removed following resource(s) %s from API role %s.",
                implode(",", $resourceIds), $role['role_name']),
                Est_Message::OK);
        } else {
            $this->log(sprintf("No resources to add to API role %s.",
                $role['role_name']), Est_Message::OK);
        }
        return true;
    }


    /**
     * Returns if rule already there
     *
     * @param int    $roleId
     * @param string $resourceId
     *
     * @return bool
     */
    private function isRule($roleId, $resourceId)
    {
        return $this->_getFirstRow("SELECT * FROM api_rule WHERE role_id = :role_id AND resource_id = :resource_id;",
            array(
                ":role_id"     => $roleId,
                ":resource_id" => $resourceId
            )) == false ? false : true;
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
