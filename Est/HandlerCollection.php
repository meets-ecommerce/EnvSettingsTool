<?php

class Est_HandlerCollection implements Iterator {

	/**
	 * @var array
	 */
	protected $handlers = array();



	public function buildFromSettingsCSVFile($csvFile,$environment,$defaultEnvironment='DEFAULT') {
		$fh = fopen($csvFile, 'r');

		// first line: labels
		$labels = fgetcsv($fh);
		if (!$labels) {
			throw new Exception('Error while reading labels from csv file');
		}

		$columnIndex = array_search($environment, $labels);
		$columnIndexDefault = array_search($defaultEnvironment, $labels);

		if ($columnIndex === false) {
			throw new Exception('Could not find current environment in csv file');
		}
		if ($columnIndex <= 3) { // those are reserved for handler class, param1-3
			throw new Exception('Environment cannot be defined in one of the first four columns');
		}

		while ($row = fgetcsv($fh)) {
			$handlerClassname = trim($row[0]);

			if (empty($handlerClassname) || $handlerClassname[0] == '#' || $handlerClassname[0] == '/') {
				// This is a comment line. Skipping...
				continue;
			}

			if (!class_exists($handlerClassname)) {
				throw new Exception(sprintf('Could not find handler class "%s"', $handlerClassname));
			}
			$handler = new $handlerClassname(); /* @var $handler Est_Handler_Abstract */
			if (!$handler instanceof Est_Handler_Abstract) {
				throw new Exception(sprintf('Handler of class "%s" is not an instance of Est_Handler_Abstract', $handlerClassname));
			}

			// set parameters
			for ($i=1; $i<=3; $i++) {
				$setterMethod = 'setParam'.$i;
				$handler->$setterMethod($row[$i]);
			}

			$value = $this->getValue($row[$columnIndex],$row[$columnIndexDefault]);
			// set value
			$handler->setValue($value);
			$this->addHandler($handler);
		}
	}

	/**
	 * @param $value
	 * @param $defaultValue
	 * @return string
	 */
	protected function getValue($value,$defaultValue) {
		if (empty($value)) {
			$value = $defaultValue;
		}

		return $this->replaceWithEnvironmentVariables($value);
	}

	/**
	 * Replaces this pattern ###ENV:TEST### with the environment variable
	 * @param $string
	 * @return string
	 * @throws \Exception
	 */
	protected function replaceWithEnvironmentVariables($string) {
		$matches=array();
		preg_match_all('/###ENV:([^#]*)###/',$string,$matches,PREG_PATTERN_ORDER);
		if (!is_array($matches) || !is_array($matches[0])) {
			return $string;
		}
		foreach ($matches[0] as $index=>$completeMatch) {
			if (getenv($matches[1][$index]) == FALSE) {
				throw new \Exception('Expect an environmentvariable '.$matches[1][$index]);
			}
			$string = str_replace($completeMatch,getenv($matches[1][$index]),$string);
		}
		return $string;
	}

	/**
	 * @param Est_Handler_Interface $handler
	 * @throws Exception
	 */
	public function addHandler(Est_Handler_Interface $handler) {
		$hash = $this->getHandlerHash($handler);
		if (isset( $this->handlers[$hash] )) {
			throw new Exception('Handler with this specification already exist. Cannot add: '.$handler->getLabel());
		}
		$this->handlers[]=$handler;
	}

	/**
	 * @param $handlerClassname
	 * @param $p1
	 * @param $p2
	 * @param $p3
	 * @return Est_Handler_Interface || bool
	 */
	public function getHandler($handlerClassname,$p1,$p2,$p3) {
		if (isset($this->handlers[$this->getHandlerHashByValues($handlerClassname,$p1,$p2,$p3)])) {
			return $this->handlers[$this->getHandlerHashByValues($handlerClassname,$p1,$p2,$p3)];
		}
		else {
			return false;
		}
	}

	/**
	 * @param $handlerClassname
	 * @param $p1
	 * @param $p2
	 * @param $p3
	 * @return string
	 */
	protected function getHandlerHash(Est_Handler_Interface $handler) {
		return $this->getHandlerHashByValues(get_class($handler),$handler->getParam1(),$handler->getParam2(),$handler->getParam3());
	}

	/**
	 * @param $handlerClassname
	 * @param $p1
	 * @param $p2
	 * @param $p3
	 * @return string
	 */
	protected function getHandlerHashByValues($handlerClassname,$p1,$p2,$p3) {
		return md5($handlerClassname.$p1.$p2.$p3);
	}

	public function rewind()
	{
		reset($this->handlers);
	}

	public function current()
	{
		return current($this->handlers);
	}

	public function key()
	{
		return key($this->handlers);
	}

	public function next()
	{
		return next($this->handlers);
	}

	public function valid()
	{
		$key = key($this->handlers);
		$var = ($key !== NULL && $key !== FALSE);
		return $var;
	}


}