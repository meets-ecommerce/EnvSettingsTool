<?php

class Est_HandlerCollectionTestcase extends PHPUnit_Framework_TestCase {

	/**
	 * @var Est_HandlerCollection
	 */
	private $handlerCollection;

	public function setUp() {
		$this->handlerCollection = new Est_HandlerCollection();
	}

	/**
	 * @test
	 */
	public function canBuildFromCSVAndReplaceByEnvironmentVariable() {
		$path = dirname(__FILE__).'/../fixtures/Settings.csv';
		putenv('DEBUG=TESTCONTENT');

		$this->handlerCollection->buildFromSettingsCSVFile($path,'latest');

		$handlers=array();
		foreach ($this->handlerCollection as $handler) {
			$handlers[]=$handler;
		}
		$this->assertEquals(2,count($handlers));
		$handler1 = $handlers[0];
		$this->assertTrue($handler1 instanceof Est_Handler_XmlFile);
		$this->assertEquals($handler1->getValue(),'latestdb');

		$handler2 = $handlers[1];
		$this->assertEquals($handler2->getValue(),'TESTCONTENT','either did not use fallback content or replacement with ENVVariable did not work');
	}

}