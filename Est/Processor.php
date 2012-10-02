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
	 * @return bool
	 */
	public function apply() {

		$this->parseCsv();

		foreach ($this->handlers as $handler) { /* @var $handler Est_Handler_Abstract */
			$res = $handler->apply();
			if (!$res) {
				// An error has occured in one of the handlers. Stop here.
				return false;
			}
		}

		return true;
	}

	/**
	 * Parse csv file
	 *
	 * @throws Exception
	 */
	protected function parseCsv() {
		$fh = fopen($this->settingsFilePath, 'r');

		// first line: labels
		$labels = fgetcsv($fh);
		if (!$labels) {
			throw new Exception('Error while reading labels from csv file');
		}

		$columnIndex = array_search($this->environment, $labels);

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

			// set value
			$handler->setValue($row[$columnIndex]);

			$this->handlers[] = $handler;
		}

	}

	/**
	 * Print result
	 */
	public function printResults() {
		foreach ($this->handlers as $handler) { /* @var $handler Est_Handler_Abstract */
			$this->output();
			$label = $handler->getLabel();
			$this->output($label);
			$this->output(str_repeat('-', strlen($label)));
			foreach ($handler->getMessages() as $message) { /* @var $message Est_Message */
				$this->output($message->getColoredText());
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