<?php
/**
 * aoepeople/envsettingstool
 *
 * Extended by meets-ecommerce.de
 *
 * @copyright   Copyright (c) 2018 meets-ecommerce UG (haftungsbeschränkt) (http://meets-ecommerce.de)
 * @author      Daniel Matuschewsky <dm@meets-ecommerce.de>
 */

/**
 * Class Est_Handler_Magento_DesignChange
 */
class Est_Handler_Magento_DesignChange
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
         * - Param1 == store id        // to truncate
         * - Param2 == from-date       // 2018-12-01
         * - Param3 == to-date         // 2018-12-26
         *
         * - Value  == --skip--        // indicates to skip it
         *          == --delete--      // removes the entry
         *          == design/package
         */

        /*
            +------------------+----------+-------------------------+-----------+---------+
            | design_change_id | store_id | design                  | date_from | date_to |
            +------------------+----------+-------------------------+-----------+---------+
            |                1 |        1 | default/default         | NULL      | NULL    |
            +------------------+----------+-------------------------+-----------+---------+
         */
        $this->setParam1(trim($this->getParam1()));
        $this->setParam2(trim($this->getParam2()));
        $this->setParam3(trim($this->getParam3()));
        $this->setValue(trim($this->getValue()));
        switch ($this->getValue()) {
            case "--skip--":
                $this->setStatus(Est_Handler_Interface::STATUS_SKIPPED);
                $this->addMessage(new Est_Message(
                    sprintf('Design change skipped.'),
                    Est_Message::SKIPPED
                ));
                return true;
                break;
            case "--delete--":
                return $this->remove();
                break;
            default:
                return $this->add();
                break;
        }
    }

    /**
     * Removes a design change
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    protected function remove($storeId = null)
    {
        try {
            $storeId = $storeId !== null ? $storeId : $this->getParam1();
            if ($storeId === null) {
                throw new \Exception("Store Id cannot be null.");
            }
            $this->_processDelete("DELETE FROM design_change WHERE store_id = :store_id",
                ['store_id' => $storeId]);
        } catch (\Exception $e) {
            $this->setStatus(Est_Handler_Interface::STATUS_ERROR);
            $this->addMessage(new Est_Message(
                sprintf('Design %s could not set for store %s: %s.',
                    $this->getValue(), $this->getParam1(), $e->getMessage()),
                Est_Message::ERROR
            ));
            return false;
        }
        return true;
    }

    /**
     * Adds a design change
     *
     * @return bool
     */
    protected function add()
    {
        if (count($this->find($this->getParam1(), $this->getValue())) > 0) {
            $this->setStatus(Est_Handler_Interface::STATUS_SKIPPED);
            $this->addMessage(new Est_Message(
                sprintf('Design %s is already set for store id %s.',
                    $this->getValue(), $this->getParam1()),
                Est_Message::SKIPPED
            ));
            return true;
        }
        if (count($this->find($this->getParam1()) > 0)) {
            $this->remove($this->getParam1());
        }

        try {
            $this->_processInsert("INSERT INTO
                design_change (store_id, design)
                VALUES (:store_id, :design)", [
                'store_id' => $this->getParam1(),
                'design'   => $this->getValue()
            ]);
            $this->setStatus(Est_Handler_Interface::STATUS_DONE);
            $this->addMessage(new Est_Message(
                sprintf('Design %s added to store %s',
                    $this->getValue(), $this->getParam1()),
                Est_Message::OK
            ));
            return true;
        } catch (\Exception $e) {
            $this->setStatus(Est_Handler_Interface::STATUS_ERROR);
            $this->addMessage(new Est_Message(
                sprintf('Design %s could not set for store %s: %s.',
                    $this->getValue(), $this->getParam1(), $e->getMessage()),
                Est_Message::ERROR
            ));
            return false;
        }
    }

    /**
     * Returns a design change
     *
     * @param int|null $storeId
     *
     * @return mixed
     */
    protected function get($storeId = null)
    {
        return $this->_getAllRows("
            SELECT *
            FROM design_change
            WHERE store_id = :store_id
            LIMIT 1
        ", ['store_id' => $storeId]);
    }

    protected function find($storeId, $design = null)
    {
        $query
            = "
            SELECT *
            FROM design_change
            WHERE store_id = :store_id  
        ";
        $params = ['store_id' => $storeId];
        if ($design !== null) {
            $query .= "AND design = :design";
            $params['design'] = $design;
        }
        return $this->_getAllRows($query, $params);
    }


}