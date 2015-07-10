<?php

/**
 * Parameters
 *
 * - role_id
 * - resource_id
 * - privilege
 *
 * - value
 *
 * @author Fabrizio Branca
 */
class Est_Handler_Magento_Api2AclRule extends Est_Handler_Magento_AbstractDatabase
{


    /**
     * Protected method that actually applies the settings. This method is implemented in the inheriting classes and
     * called from ->apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply()
    {
        $this->_checkIfTableExists('api2_acl_rule');

        $roleId = $this->param1;
        if ($roleId != '%' && !is_numeric($roleId)) {
            $roleName = $roleId;
            $roleId = $this->_getRoleIdFromName($roleName);
            $this->addMessage(new Est_Message("Found role id '$roleId' for role '$roleName'", Est_Message::INFO));
        }
        $resourceId = $this->param2;
        $privilege = $this->param3;

        if (empty($roleId)) {
            throw new Exception("No role given");
        }
        if (empty($resourceId)) {
            throw new Exception("No resourceId given");
        }
        if ($resourceId === 'all' && !empty($privilege)) {
            throw new Exception("No privilege allowed if resourceId is 'all");
        }

        if (strtolower(trim($this->value)) == '--delete--') {
            $action = Est_Handler_Magento_AbstractDatabase::ACTION_DELETE;
        } elseif (strtolower(trim($this->value)) == '--insert--') {
            $action = Est_Handler_Magento_AbstractDatabase::ACTION_INSERT;
        } else {
            $action = filter_var($this->value, FILTER_VALIDATE_BOOLEAN) ? Est_Handler_Magento_AbstractDatabase::ACTION_INSERT : Est_Handler_Magento_AbstractDatabase::ACTION_DELETE;
        }

        $sqlParameters = array(
            ':roleId' => $roleId,
            ':resourceId' => $resourceId,
            ':privilege' => $privilege
        );

        if ($action == Est_Handler_Magento_AbstractDatabase::ACTION_DELETE) {
            $query = 'DELETE FROM `' . $this->_tablePrefix . 'api2_acl_rule` WHERE IFNULL(`role_id`, \'\') LIKE :roleId AND IFNULL(`resource_id`, \'\') LIKE :resourceId AND IFNULL(`privilege`, \'\') LIKE :privilege';
            $this->_processDelete($query, $sqlParameters);
        } else {
            // check if row exists
            $query = 'SELECT `entity_id` FROM `' . $this->_tablePrefix . 'api2_acl_rule` WHERE IFNULL(`role_id`, \'\') LIKE :roleId AND IFNULL(`resource_id`, \'\') LIKE :resourceId AND IFNULL(`privilege`, \'\') LIKE :privilege';
            $firstRow = $this->_getFirstRow($query, $sqlParameters);
            if ($firstRow) {
                $this->addMessage(new Est_Message('Rule is already in place. Skipping.'), Est_Message::SKIPPED);
            } else {
                $query = 'INSERT INTO `' . $this->_tablePrefix . 'api2_acl_rule` (`role_id`, `resource_id`, `privilege`) VALUES (:roleId, :resourceId, :privilege)';
                $this->_processInsert($query, $sqlParameters);
            }
        }

        $this->destroyDb();

        return true;
    }

    /**
     * Look up role id for a given role name
     *
     * @param $code
     * @return mixed
     * @throws Exception
     */
    protected function _getRoleIdFromName($roleName)
    {
        $results = $this->_getAllRows(
            "SELECT `entity_id` FROM `{$this->_tablePrefix}api2_acl_role` WHERE `role_name` LIKE :role_name",
            array(':role_name' => $roleName)
        );

        if (count($results) === 0) {
            throw new Exception("Could not find a role for name '$roleName'");
        } elseif (count($results) > 1) {
            throw new Exception("Found more than one role for name '$roleName'");
        }

        $result = end($results);

        return $result['entity_id'];
    }

}
