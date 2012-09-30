<?php

class Est_Message {

	/**
	 * Levels are taken from Zend_Log
	 */
	const EMERG   = 0;  // Emergency: system is unusable
	const ALERT   = 1;  // Alert: action must be taken immediately
	const CRIT    = 2;  // Critical: critical conditions
	const ERR     = 3;  // Error: error conditions
	const WARN    = 4;  // Warning: warning conditions
	const NOTICE  = 5;  // Notice: normal but significant condition
	const INFO    = 6;  // Informational: informational messages
	const DEBUG   = 7;  // Debug: debug messages

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @var string
	 */
	protected $level;

	/**
	 * Constructor
	 *
	 * @param string $text
	 * @param int $level
	 */
	public function __construct($text, $level=Est_Message::INFO) {
		$this->text = $text;
		$this->level = $level;
	}

	public function getText() {
		return $this->text;
	}

}