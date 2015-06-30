<?php
/**
 * @author Fabrizio Branca
 */

/**
 * Parameters:
 * - store (Id or Code)
 * - not used
 * - not used
 */
class Est_Handler_Magento_StoreActivate extends Est_Handler_Magento_AbstractDatabase
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
        $this->_checkIfTableExists('core_store');
        $v = $this->_tablePrefix . 'core_store';

        $store = $this->param1;
        if (!is_numeric($store)) {
            $code = $store;
            $store = $this->_getStoreIdFromCode($code);
            $this->addMessage(new Est_Message("Found store id '$store' for code '$code'", Est_Message::INFO));
        }

        $value = filter_var($this->value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

        $this->_processUpdate(
            "UPDATE `{$v}` SET is_active = :value WHERE `store_id` = :store_id",
            array('store_id' => $store, 'value' => $this->value),
            $value['html_value']
        );
        return true;
    }
}
