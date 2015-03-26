<?php

/**
 * Add stuff to a file
 *
 * Parameters
 * - param1: targetFile
 * - param2: not used
 * - param3: not used
 *
 * @author Fabrizio Branca
 * @since  2012-10-01
 */
class Est_Handler_PrependFileContent extends Est_Handler_AddFileContent
{
    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply()
    {
        $this->setParam2('prepend');

        return parent::_apply();
    }
}
