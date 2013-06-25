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

	/**
	 * @test
	 */
	public function paramsWithOneLoop() {
		$path = dirname(__FILE__).'/../fixtures/SettingsWithOneLoop.csv';
		$this->handlerCollection->buildFromSettingsCSVFile($path, 'latest');

		$handlers = array();
		foreach ($this->handlerCollection as $handler) {
			$handlers[] = $handler;
		}

		$this->assertCount(2, $handlers);

		$this->assertEquals('default', $handlers[0]->getParam1());
		$this->assertEquals('default', $handlers[1]->getParam1());

		$this->assertEquals('1', $handlers[0]->getParam2());
		$this->assertEquals('2', $handlers[1]->getParam2());

		$this->assertEquals('dev/debug/profiler', $handlers[0]->getParam3());
		$this->assertEquals('dev/debug/profiler', $handlers[1]->getParam3());

		$this->assertEquals('test2', $handlers[0]->getValue());
		$this->assertEquals('test2', $handlers[1]->getValue());
	}

	/**
	 * @test
	 */
	public function paramsWithTwoLoops() {
		$path = dirname(__FILE__).'/../fixtures/SettingsWithTwoLoops.csv';
		$this->handlerCollection->buildFromSettingsCSVFile($path, 'latest');

		$handlers = array();
		foreach ($this->handlerCollection as $handler) {
			$handlers[] = $handler;
		}

		$this->assertCount(4, $handlers);

		$this->assertEquals('store', $handlers[0]->getParam1());
		$this->assertEquals('store', $handlers[1]->getParam1());
		$this->assertEquals('website', $handlers[2]->getParam1());
		$this->assertEquals('website', $handlers[3]->getParam1());

		$this->assertEquals('1', $handlers[0]->getParam2());
		$this->assertEquals('2', $handlers[1]->getParam2());
		$this->assertEquals('1', $handlers[2]->getParam2());
		$this->assertEquals('2', $handlers[3]->getParam2());

		$this->assertEquals('dev/debug/profiler', $handlers[0]->getParam3());
		$this->assertEquals('dev/debug/profiler', $handlers[1]->getParam3());
		$this->assertEquals('dev/debug/profiler', $handlers[2]->getParam3());
		$this->assertEquals('dev/debug/profiler', $handlers[3]->getParam3());

		$this->assertEquals('test2', $handlers[0]->getValue());
		$this->assertEquals('test2', $handlers[1]->getValue());
		$this->assertEquals('test2', $handlers[2]->getValue());
		$this->assertEquals('test2', $handlers[3]->getValue());
	}


}