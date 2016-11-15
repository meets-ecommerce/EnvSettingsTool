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
 * Class Est_Handler_Magento_AbstractDatabaseTable
 */
abstract class Est_Handler_Magento_AbstractDatabaseTable
    extends Est_Handler_Magento_AbstractDatabase
{

    /**
     * Returns all tables
     *
     * @return bool|array
     */
    protected function getTables()
    {
        $this->setParam1(trim($this->getParam1()));
        $param = $this->getParam1();
        if (strpos($param, '*') !== false && strpos($param, '*') !== 0) {
            $param = str_replace('*', '%', $param);
            $tableObjects = $this->getDbConnection()
                ->query("SHOW TABLES LIKE '" . $param . "';")->fetchAll();
            $tables = array();
            if (count($tableObjects) > 0) {
                foreach ($tableObjects as $tableObject) {
                    $tables[] = $tableObject[0];
                }
            }
            return $tables;
        } elseif (strpos($param, '*') === false) {
            return array($param);
        } else {
            return false;
        }
    }

    /**
     * Sets foreign key check to 1
     *
     * @throws
     */
    protected function setForeignKeyCheck()
    {
        $result = $this->getDbConnection()->prepare("SET FOREIGN_KEY_CHECKS=1;")
            ->execute();
        if($result === false){
            throw new Exception("Cannot set foreign key checks to 1;");
        }
    }

    /**
     * Sets foreign key check to 0
     *
     * @throws Exception
     */
    protected function unsetForeignKeyCheck()
    {
        $result = $this->getDbConnection()->prepare("SET FOREIGN_KEY_CHECKS=0;")
            ->execute();
        if($result === false){
            throw new Exception("Cannot set foreign key checks to ;");
        }
    }

}