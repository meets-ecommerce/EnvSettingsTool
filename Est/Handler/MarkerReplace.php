<?php

/**
 * Replace a marker in a file
 *
 * Parameters
 * - param1: file
 * - param2: marker
 * - param3: not used
 *
 * @author Fabrizio Branca
 * @since 2012-09-20
 */
class Est_Handler_MarkerReplace extends Est_Handler_Abstract {

	/**
	 * Apply
	 *
	 * @param $value
	 * @param null $param1
	 * @param null $param2
	 * @param null $param3
	 * @throws Exception
	 * @return bool
	 */
	protected function _apply() {

		// let's use some speaking variable names... :)
		$file = $this->param1;
		$marker = $this->param2;

		if (!is_file($file)) {
			throw new Exception(sprintf('File "%s" does not exist', $file));
		}
		if (!is_writable($file)) {
			throw new Exception(sprintf('File "%s" is not writeable', $file));
		}
		if (empty($marker)) {
			throw new Exception('No marker defined');
		}
		if (!empty($this->param3)) {
			throw new Exception('Param3 is not used in this handler and must be empty');
		}

		// read file
		$fileContent = file_get_contents($file);
		if ($fileContent === false) {
			throw new Exception(sprintf('Error while reading file "%s"', $file));
		}
		$count = 0;

		// do the replacement
		$fileContent = str_replace($marker, $this->value, $fileContent, $count);

		if ($count == 0) {
			throw new Exception(sprintf('Could not find marker "%s" in file "%s"', $marker, $file));
		}

		// write back to file
		$res = file_put_contents($file, $fileContent);
		if ($res === false) {
			throw new Exception(sprintf('Error while writing file "%s"', $file));
		}

		$this->addMessage(new Est_Message(
			sprintf('Replaced %s occurence(s) of marker "%s" in file "%s" with value "%s"', $count, $marker, $file, $this->value),
			Est_Message::OK
		));

		return true;
	}


}