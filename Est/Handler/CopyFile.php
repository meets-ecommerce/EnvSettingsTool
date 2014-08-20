<?php

/**
 * Copy file
 *
 * Parameters
 * - param1: targetFile
 * - param2: not used
 * - param3: not used
 *
 * @author Fabrizio Branca
 * @since 2014-08-19
 */
class Est_Handler_CopyFile extends Est_Handler_Abstract {

    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply() {

        // let's use some speaking variable names... :)
        $sourceFile = $this->value;
        $targetFile = $this->param1;

        if (empty($sourceFile)) {
            $this->setStatus(Est_Handler_Interface::STATUS_SKIPPED);
            return true;
        }

        if (!is_file($sourceFile)) {
            throw new Exception(sprintf('Source file "%s" does not exist', $targetFile));
        }

        if (is_file($targetFile) && (md5_file($targetFile) == md5_file($sourceFile))){
            $this->setStatus(Est_Handler_Interface::STATUS_ALREADYINPLACE);
            $this->addMessage(new Est_Message(
                sprintf('Files "%s" and "%s" are identical', $sourceFile, $targetFile),
                Est_Message::SKIPPED
            ));
        } else {
            $res = copy($sourceFile, $targetFile);
            if ($res) {
                $this->setStatus(Est_Handler_Interface::STATUS_DONE);
                $this->addMessage(new Est_Message(
                    sprintf('Successfully copied file "%s" to "%s"', $sourceFile, $targetFile),
                    Est_Message::OK
                ));
            } else {
                $this->setStatus(Est_Handler_Interface::STATUS_ERROR);
                $this->addMessage(new Est_Message(
                    sprintf('Error while copying file "%s" to "%s"', $sourceFile, $targetFile),
                    Est_Message::ERROR
                ));
            }
        }

        return true;
    }


}