<?php

/**
 * Store a variable in memory for later use in values
 *
 * Parameters
 * - param1: variable name
 * - param2: not used
 * - param3: not used
 *
 * @author Fabrizio Branca
 * @since 2013-11-08
 */
class Est_Handler_SetVar extends Est_Handler_Abstract {

    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _register() {

        // let's use some speaking variable names... :)
        $variableName = $this->param1;
        $value = $this->value;

        Est_VariableStorage::add($variableName, $value);

        $this->addMessage(new Est_Message(sprintf('Storing value "%s" for variable "%s".', $value, $variableName), Est_Message::OK));
    }

    protected function _apply() {
        return true;
    }



}