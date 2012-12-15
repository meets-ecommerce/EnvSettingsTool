<?php

/**
 * Abstract handler class
 *
 * @author Fabrizio Branca
 * @since 2012-09-20
 */
abstract class Est_Handler_Abstract {

	CONST STATUS_NOTEXECUTED = 'NOT_EXECUTED';
	CONST STATUS_RUNNING = 'RUNNING';
	CONST STATUS_FINISHED = 'FINISHED'; // we don't have any detailed information besides that it finished without exeptions here
	CONST STATUS_SKIPPED = 'SKIPPED';
	CONST STATUS_ALREADYINPLACE = 'ALREADY IN PLACE';
	CONST STATUS_SUBJECTNOTFOUND = 'SUBJECT_NOT_FOUND';
	CONST STATUS_DONE = 'DONE';
	CONST STATUS_ERROR = 'ERROR';

	/**
	 * @var array
	 */
	protected $messages = array();

	protected $param1;
	protected $param2;
	protected $param3;
	protected $value;

	protected $environment;

	protected $status = Est_Handler_Abstract::STATUS_NOTEXECUTED;


	/**
	 * Protected method that actually applies the settings. This method is implemented in the inheriting classes and
	 * called from ->apply
	 *
	 * @return bool
	 */
	abstract protected function _apply();

	/**
	 * Apply setting
	 *
	 * @return bool
	 */
	public function apply() {
		try {
			$this->setStatus(Est_Handler_Abstract::STATUS_RUNNING);
			$result = $this->_apply();
			if ($this->getStatus() == Est_Handler_Abstract::STATUS_RUNNING) {
				$this->setStatus(Est_Handler_Abstract::STATUS_FINISHED);
			}
			return $result;
		} catch (Exception $e) {
			$this->setStatus(Est_Handler_Abstract::STATUS_ERROR);
			$this->addMessage(new Est_Message(
				$e->getMessage(),
				Est_Message::ERROR
			));
			return false;
		}
	}

	public function setEnvironment($environment) {
		$this->environment = $environment;
	}

	public function setParam1($param1) {
		$this->param1 = $param1;
	}

	public function setParam2($param2) {
		$this->param2 = $param2;
	}

	public function setParam3($param3) {
		$this->param3 = $param3;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * Add message
	 *
	 * @param $message
	 */
	protected function addMessage($message) {
		$this->messages[] = $message;
	}

	/**
	 * Get messages
	 *
	 * @return array
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * Get a speaking label
	 *
	 * @return string
	 */
	public function getLabel() {
		$label = get_class($this);
		$label .= sprintf("('%s', '%s', '%s')",
			$this->formatParam($this->param1),
			$this->formatParam($this->param2),
			$this->formatParam($this->param3)
		);
		$label .= ' = ';
		$label .= $this->value;
		return $label;
	}

	public function formatParam($param) {
		if (is_null($param)) {
			$param = 'null';
		}
		return $param;
	}

	protected function setStatus($status) {
		$this->status = $status;
	}

	public function getStatus() {
		return $this->status;
	}



}