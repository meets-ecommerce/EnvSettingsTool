<?php

class Est_HandlerCollectionTestcase extends PHPUnit_Framework_TestCase {


    /**
     * @test
     */
    public function canBuildFromCSVAndReplaceByEnvironmentVariable() {

        putenv('DEBUG=TESTCONTENT');
        $handlerCollection = $this->getHandlerCollectionFromFixture();

        $handlers=array();
        foreach ($handlerCollection as $handler) {
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
    public function canUseDefautlValues() {
        /*
            Est_Handler_Magento_CoreConfigData,1,foo,bar,defaultvalue,
            Est_Handler_Magento_CoreConfigData,2,foo,bar,defaultvalue,,
            Est_Handler_Magento_CoreConfigData,3,foo,bar,defaultvalue,0,
            Est_Handler_Magento_CoreConfigData,4,foo,bar,defaultvalue, ,
            Est_Handler_Magento_CoreConfigData,5,foo,bar,,
        */
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithDefaultValues.csv');

        $this->assertEquals('defaultvalue', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',1,'foo','bar')->getValue());
        $this->assertEquals('defaultvalue', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',2,'foo','bar')->getValue());
        $this->assertEquals(0, $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',3,'foo','bar')->getValue());
        $this->assertEquals(' ', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',4,'foo','bar')->getValue());
        $this->assertEquals('', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',5,'foo','bar')->getValue());
        $this->assertEquals('', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData',6,'foo','bar')->getValue());

    }


    /**
     * @test
     */
    public function canGetHandler() {
        $handlerCollection = $this->getHandlerCollectionFromFixture();
        $handler = $handlerCollection->getHandler('Est_Handler_XmlFile','app/etc/local.xml','/config/global/resources/default_setup/connection/host','');
        $this->assertTrue($handler instanceof Est_Handler_XmlFile);

    }

    /**
     * @test
     */
    public function canUseHandlersWithOneLoopParams() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithOneLoop.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
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
    public function canUseHandlersWithTwoLoopParams() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithTwoLoops.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
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

    /**
     * @test
     */
    public function canUseHandlersWithTwoLoopParamsInTheSameParameter() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithTwoLoopsInTheSameParameter.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(4, $handlers);

        $this->assertEquals('test_store_a', $handlers[0]->getParam1());
        $this->assertEquals('test_store_b', $handlers[1]->getParam1());
        $this->assertEquals('test_website_a', $handlers[2]->getParam1());
        $this->assertEquals('test_website_b', $handlers[3]->getParam1());

        $this->assertEquals('1', $handlers[0]->getParam2());
        $this->assertEquals('1', $handlers[1]->getParam2());
        $this->assertEquals('1', $handlers[2]->getParam2());
        $this->assertEquals('1', $handlers[3]->getParam2());

        $this->assertEquals('dev/debug/profiler', $handlers[0]->getParam3());
        $this->assertEquals('dev/debug/profiler', $handlers[1]->getParam3());
        $this->assertEquals('dev/debug/profiler', $handlers[2]->getParam3());
        $this->assertEquals('dev/debug/profiler', $handlers[3]->getParam3());

        $this->assertEquals('test2', $handlers[0]->getValue());
        $this->assertEquals('test2', $handlers[1]->getValue());
        $this->assertEquals('test2', $handlers[2]->getValue());
        $this->assertEquals('test2', $handlers[3]->getValue());
    }

    /**
     * @test
     */
    public function canUseReferencesToOtherColumns() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithReferences.csv', 'environment_b');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(1, $handlers);
        $this->assertEquals('foo', $handlerCollection->getHandler('Est_Handler_Magento_CoreConfigData','p1','p2','p3')->getValue());
    }

    /**
     * @test
     */
    public function canUseHandlersWithInlineLoop() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithInlineLoop.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(3, $handlers);

        $this->assertEquals('param1', $handlers[0]->getParam1());
        $this->assertEquals('param1', $handlers[1]->getParam1());
        $this->assertEquals('param1', $handlers[2]->getParam1());

        $this->assertEquals('param2', $handlers[0]->getParam2());
        $this->assertEquals('param2', $handlers[1]->getParam2());
        $this->assertEquals('param2', $handlers[2]->getParam2());

        $this->assertEquals('a/b/c', $handlers[0]->getParam3());
        $this->assertEquals('a/b/d', $handlers[1]->getParam3());
        $this->assertEquals('a/b/e', $handlers[2]->getParam3());

        $this->assertEquals('test2', $handlers[0]->getValue());
        $this->assertEquals('test2', $handlers[1]->getValue());
        $this->assertEquals('test2', $handlers[2]->getValue());
    }

    /**
     * @test
     */
    public function settingsWithVariable() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithVariables.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(6, $handlers);

        $this->assertEquals('value2', $handlers[2]->getValue());
        $this->assertEquals('value1', $handlers[3]->getValue());
        $this->assertEquals('inline-value1-usage', $handlers[4]->getValue());
        $this->assertEquals('value1', $handlers[5]->getValue());
    }

    /**
     * @test
     */
    public function settingsWithVariableNotSet() {
        $this->setExpectedException('Exception', 'Variable "notset" is not set');
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithVariablesNotSet.csv');
    }

    /**
     * @test
     */
    public function settingsWithCommentsAndEmptyLines() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithEmptyLinesAndComments.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(5, $handlers);
    }

    /**
     * @test
     */
    public function exceptionWhenSameSignature() {
        $this->setExpectedException('Exception');
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithSameParameters.csv');
    }

    /**
     * @test
     */
    public function settingsWithMarker() {
        $handlerCollection = $this->getHandlerCollectionFromFixture('SettingsWithMarkers.csv');

        $handlers = array();
        foreach ($handlerCollection as $handler) {
            $handlers[] = $handler;
        }

        $this->assertCount(6, $handlers);

        for ($i=0; $i<6; $i++) {
            $this->assertEquals($i+1, $handlers[$i]->getParam1());
            $this->assertEquals('foo', $handlers[$i]->getParam2());
            $this->assertEquals('bar', $handlers[$i]->getParam3());
        }

        $this->assertEquals('1', $handlers[0]->getValue());
        $this->assertEquals('foo', $handlers[1]->getValue());
        $this->assertEquals('bar', $handlers[2]->getValue());
        $this->assertEquals('latest', $handlers[3]->getValue());
        $this->assertEquals('latest', $handlers[4]->getValue());
        $this->assertEquals('inline-6-parameter', $handlers[5]->getValue());
    }


    /**
     * Get handler collection from fixture
     *
     * @param string $file
     * @param string $environment
     * @return Est_HandlerCollection
     */
    private function getHandlerCollectionFromFixture($file='Settings.csv', $environment='latest') {
        $path = dirname(__FILE__).'/../fixtures/'.$file;
        $handlerCollection = new Est_HandlerCollection();
        $handlerCollection->buildFromSettingsCSVFile($path, $environment);
        return $handlerCollection;
    }


}