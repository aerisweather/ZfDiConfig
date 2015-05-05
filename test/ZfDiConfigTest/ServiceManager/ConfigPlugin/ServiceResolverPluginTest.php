<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin;
use Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService;

class ServiceResolverPluginTest extends ConfigPluginTestCase {

	/** @var ServiceResolverPlugin */
	protected $plugin;

	protected function setUp(){
		parent::setUp();

		$this->plugin = new ServiceResolverPlugin();
		$this->plugin->setServiceLocator($this->serviceManager);
	}

	/** @test */
	public function resolve_shouldReturnAServiceByName() {
		$this->serviceManager->setService('FooService', $fooService = new FooService());

		$this->assertSame($fooService, $this->plugin->resolve([
			'name' => 'FooService',
		]));
	}

	/** @test */
	public function resolve_shouldUseAGetterOnAService() {
		$fooService = new FooService();
		$fooService->setBar('baz');

		$this->serviceManager->setService('FooService', $fooService);

		$this->assertEquals('baz', $this->plugin->resolve([
			'name' => 'FooService',
			'getter' => 'bar'
		]));
	}

	/**
	 * @expectedException \Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException
	 * @test
	 */
	public function resolve_shouldComplainIfTheGetterDoesNotExist() {
		$fooService = new FooService();

		$this->serviceManager->setService('FooService', $fooService);

		$this->assertEquals('baz', $this->plugin->resolve([
			'name' => 'FooService',
			'getter' => 'qux'
		]));
	}

	/** @test */
	public function configFromString_shouldIntegrateWithResolve() {
		$fooService = new FooService();
		$fooService->setBar('baz');

		$this->serviceManager->setService('FooService', $fooService);

		$pluginConfig = $this->plugin->configFromString('FooService::bar');
		$this->assertEquals('baz', $this->plugin->resolve($pluginConfig));
	}

}