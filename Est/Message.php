<?php

class Est_Message {

	const OK = 0;
	const WARNING = 1;
	const ERROR = 2;
	const SKIPPED = 3;

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
	public function __construct($text, $level=Est_Message::OK) {
		$this->text = $text;
		$this->level = $level;
	}

	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Get level
	 * see class constants
	 *
	 * @return int
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * Get colored text message
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getColoredText() {

		switch ($this->getLevel()) {
			case Est_Message::OK: $color = 'green'; break;
			case Est_Message::WARNING: $color = 'orange'; break;
			case Est_Message::SKIPPED: $color = 'blue'; break;
			case Est_Message::ERROR: $color = 'red'; break;
			default: throw new Exception('Invalid level');
		}

		return Est_CliOutput::getColoredString($this->getText(), $color);
	}

}