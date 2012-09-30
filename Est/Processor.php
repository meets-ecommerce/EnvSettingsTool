<?php

class Est_Processor {

	/**
	 * @var string
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected $settingsFilePath;

	/**
	 * @var array
	 */
	protected $handlers = array();

	/**
	 * Constructor
	 *
	 * @param $environment
	 * @param $settingsFilePath
	 * @throws InvalidArgumentException
	 */
	public function __construct($environment, $settingsFilePath) {
		if (empty($environment)) {
			throw new InvalidArgumentException('No environment parameter set.');
		}
		if (empty($settingsFilePath)) {
			throw new InvalidArgumentException('No settings file set.');
		}
		if (!file_exists($settingsFilePath)) {
			throw new InvalidArgumentException('Could not read settings file.');
		}

		$this->environment = $environment;
		$this->settingsFilePath = $settingsFilePath;
	}

	/**
	 * Apply settings to current environment
	 *
	 * @return void
	 */
	public function apply() {

		$this->parseCsv();

		foreach ($this->handlers as $handler) { /* @var $handler Est_Handler_Abstract */
			$handler->apply();
		}
	}

	/**
	 * Parse csv file
	 *
	 * @throws Exception
	 */
	protected function parseCsv() {
		$fh = fopen($this->settingsFilePath, 'r');

		// first line: handler classnames
		$handlerClassnames = fgetcsv($fh);
		if (!$handlerClassnames) {
			throw new Exception('Error while reading handler classnames from csv file');
		}

		// skip first cell
		array_shift($handlerClassnames);

		// instanciate handlers
		$i = 0;
		foreach ($handlerClassnames as $handlerClassname) {
			if (!class_exists($handlerClassname)) {
				throw new Exception(sprintf('Could not find handler class "%s"', $handlerClassname));
			}
			$handler = new $handlerClassname(); /* @var $handler Est_Handler_Abstract */
			if (!$handler instanceof Est_Handler_Abstract) {
				throw new Exception(sprintf('Handler of class "%s" is not an instance of Est_Handler_Abstract', $handlerClassname));
			}
			$this->handlers[$i++] = $handler;
		}

		// set first parameter
		for ($k=1; $k<=3; $k++) {
			$params = fgetcsv($fh);
			if (!$params) {
				throw new Exception(sprintf('Error while reading param%s from csv file', $k));
			}

			// skip first cell
			array_shift($params);

			$i = 0;
			foreach ($params as $param) {
				$setterMethod = 'setParam'.$k;
				$this->handlers[$i++]->$setterMethod($param);
			}
		}

		// find current environment in following rows
		$foundCurrentEnvironment = false;
		while (!$foundCurrentEnvironment && $row = fgetcsv($fh)) {
			$environment = array_shift($row);
			if ($environment == $this->environment) {
				$foundCurrentEnvironment = true;
			}
		}

		if (!$foundCurrentEnvironment) {
			throw new Exception('Could not find current environment in csv file');
		}

		// set values
		$i = 0;
		foreach ($row as $value) {
			$this->handlers[$i++]->setValue($value);
		}

		foreach ($this->handlers as $handler) {
			$this->output($handler->getLabel());
		}
	}

	/**
	 * Print result
	 */
	public function printResults() {
		foreach ($this->handlers as $handler) { /* @var $handler Est_Handler_Abstract */
			$this->output();
			$this->output($handler->getLabel());
			$this->output(str_repeat('-', 80));
			foreach ($handler->getMessages() as $message) { /* @var $message Est_Message */
				$this->output($message->getText());
			}
		}
	}

	protected function output($message='', $newLine=true) {
		echo $message;
		if ($newLine) {
			echo "\n";
		}
	}

}