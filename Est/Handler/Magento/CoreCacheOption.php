<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoe.com>
 */

/**
 * Parameters
 *
 * - code
 */
class Est_Handler_Magento_CoreCacheOption extends Est_Handler_Magento_AbstractDatabase
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
        $this->_checkIfTableExists('core_cache_option');

        $code          = trim($this->param1);
        $sqlParameters = $this->_getSqlParameters($code);

        $query                   = 'SELECT `value` FROM `' . $this->_tablePrefix . 'core_cache_option` WHERE `code` = :code';
        $firstRow                = $this->_getFirstRow($query, $sqlParameters);
        $sqlParameters[':value'] = $this->value;

        if ($firstRow === false) {
            $this->addMessage(
                new Est_Message(sprintf('No rows with code = "%s" found', $code), Est_Message::SKIPPED)
            );
        } elseif ($firstRow['value'] == $this->value) {
            $this->addMessage(
                new Est_Message(sprintf('Value "%s" is already in place. Skipping.', $firstRow['value']), Est_Message::SKIPPED)
            );
        } else {
            $sqlParameters[':value'] = $this->value;
            $query = 'UPDATE `' . $this->_tablePrefix . 'core_cache_option` SET `value` = :value WHERE `code` = :code';
            $this->_processUpdate($query, $sqlParameters, $firstRow['value']);
        }

        return true;
    }

    /**
     * Protected method that actually extracts the settings. This method is implemented in the inheriting classes and
     * called from ->extract and only echos constructed csv
     */
    protected function _extract()
    {
        $this->_checkIfTableExists('core_cache_option');

        $code = $this->param1;
        $sqlParameters = $this->_getSqlParameters($code);

        $query = 'SELECT code, value FROM `' . $this->_tablePrefix . 'core_cache_option` WHERE `code` = :code';

        return $this->_outputQuery($query, $sqlParameters);
    }

    /**
     * Constructs the sql parameters
     *
     * @param string $code
     * @return array
     * @throws Exception
     */
    protected function _getSqlParameters($code)
    {
        if (empty($code)) {
            throw new Exception("No code found");
        }

        return array(':code' => $code);
    }
}
