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
 * Class AbstractApi
 */
class Est_Handler_Magento_AbstractApi
    extends Est_Handler_Magento_AbstractDatabase
{


    /**
     * @inheritdoc
     */
    protected function _apply()
    {
        parent::_apply();
    }

    /**
     * Returns an API user by Id or Name
     *
     * @param $userId
     *
     * @return bool|array
     */
    public function getApiUser($userId)
    {
        $basicQuery = 'SELECT * FROM api_user WHERE ';
        $queryParams = array(':value' => $userId);
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
     * Returns an API role by ID or Name
     *
     * @param $roleId
     *
     * @return bool|array
     */
    public function getApiRole($roleId)
    {
        $basicQuery = 'SELECT * FROM api_role WHERE ';
        $queryParams = array(':value' => $roleId);
        $apiUser = $this->_getFirstRow($basicQuery
            . '`role_id` = :value',
            $queryParams
        );
        if ($apiUser === false) {
            $apiUser = $this->_getFirstRow($basicQuery
                . '`role_name` = :value',
                $queryParams);
        }
        return $apiUser;
    }

}