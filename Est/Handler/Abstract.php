<?php

/**
 * Abstract handler class
 *
 * @author Fabrizio Branca
 * @since 2012-09-20
 */
abstract class Est_Handler_Abstract {

	/**
	 * @var array
	 */
	protected $messages = array();

	protected $param1;
	protected $param2;
	protected $param3;
	protected $value;

	protected $environment;


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
			return $this->_apply();
		} catch (Exception $e) {
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
		$label .= '(';
		$label .= is_null($this->param1) ? 'null' : $this->param1;
		$label .= ', ';
		$label .= is_null($this->param2) ? 'null' : $this->param2;
		$label .= ', ';
		$label .= is_null($this->param3) ? 'null' : $this->param3;
		$label .= ')';
		$label .= ' = ';
		$label .= $this->value;
		return $label;
	}



}