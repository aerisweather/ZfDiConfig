<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin;
use Aeris\ZfDiConfigTest\Fixture\ConfigPlugin\PassThroughStringPlugin;
use Zend\ServiceManager\ServiceManager;

class FactoryPluginTest extends ConfigPluginTestCase {

	/** @var FactoryPlugin */
	protected $factoryPlugin;

	/** @var ConfigPluginManager */
	protected $pluginManager;

	protected function setUp() {
		parent::setUp();

		$this->serviceManager = new ServiceManager();

		$this->pluginManager = new ConfigPluginManager();
		$this->pluginManager->setServiceLocator($this->serviceManager);

		$this->factoryPlugin = new FactoryPlugin();

		$this->pluginManager->registerPlugin($this->factoryPlugin, '$factory', '$factory::');
		$this->pluginManager->registerPlugin(new PassThroughStringPlugin(), '$=', '$=');
	}

	/** @test */
	public function shouldCreateAServiceAsAnInstanceOfAClass() {
		$obj = $this->factoryPlugin->resolve([
			'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
		]);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService', $obj);
	}

	/** @test */
	public function shouldCreateAServiceAsAnInstanceOfAClass_shortHand() {
		$fooService = $this->factoryPlugin->resolve('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService');

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$fooService);
	}

	/** @test */
	public function shouldAllowNestedPlugins() {
		$obj = $this->factoryPlugin->resolve([
			'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			'args' => [
				[
					'$factory' => [
						'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
					]
				]
			]
		]);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService', $obj);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$obj->constructorArgs[0]);
	}

	/** @test */
	public function shouldInjectCtorArgs() {
		$obj = $this->factoryPlugin->resolve([
			'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			'args' => ['$=foo']
		]);

		$this->assertEquals('foo', $obj->constructorArgs[0]);
	}

	/** @test */
	public function shouldInjectServicesUsingSetters() {
		$obj = $this->factoryPlugin->resolve([
			'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			'setters' => [
				'bar' => '$=baz'
			]
		]);


		$this->assertEquals('baz', $obj->bar);
	}

	/**
	 * @test
	 * @expectedException \Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException
	 */
	public function shouldComplainReferencesWithNoRegisteredPlugin() {
		$this->factoryPlugin->resolve([
			'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			'setters' => [
				'bar' => '%$^NotARegisteredPlugin'
			]
		]);
	}
}