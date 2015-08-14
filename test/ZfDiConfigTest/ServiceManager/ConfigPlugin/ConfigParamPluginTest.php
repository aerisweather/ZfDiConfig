<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigParamPlugin;
use Zend\ServiceManager\ServiceManager;

class ConfigParamPluginTest extends ConfigPluginTestCase {

	/** @var ServiceManager */
	protected $serviceManager;

	/** @var ConfigParamPlugin */
	protected $plugin;

	protected function setUp() {
		$x = [null => 'bar'];
		parent::setUp();

		$this->plugin = new ConfigParamPlugin();
		$this->plugin->setServiceLocator($this->serviceManager);
	}

	/** @test */
	public function resolve_shouldReturnConfigValues() {
		$this->serviceManager->setService('config', [
			'foo' => 'bar'
		]);

		$this->assertEquals('bar', $this->plugin->resolve([
			'path' => 'foo'
		]));
	}

	/** @test */
	public function resolve_shouldReturnNestedConfigValues() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => [
					'faz' => 'baz'
				]
			]
		]);

		$this->assertEquals('baz', $this->plugin->resolve([
			'path' => 'foo.bar.faz'
		]));
	}

	/** @test */
	public function resolve_shouldUseADefaultValue() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => [
					'faz' => 'baz'
				]
			]
		]);

		$this->assertEquals('qaz', $this->plugin->resolve([
			'path' => 'foo.bar.faz.qux',
			'default' => 'qaz',
		]));
	}

	/** @test */
	public function resolve_shouldReturnNullValues() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => [
					'faz' => null
				]
			]
		]);

		$this->assertNull($this->plugin->resolve([
			'path' => 'foo.bar.faz'
		]));
	}

	/**
	 * @test
	 * @expectedException \Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException
	 */
	public function resolve_shouldComplainIfTheConfigParamIsNotSet() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => null
			]
		]);

		$this->plugin->resolve([
			'path' => 'foo.bar.faz'
		]);
	}

	/** @test */
	public function configFromString_shouldConvertStringConfig() {
		$this->assertEquals([
			'path' => 'foo.bar.faz'
		], $this->plugin->configFromString('foo.bar.faz'));
	}

	/** @test */
	public function configFromString_shouldIntegrateWithResolve() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => [
					'faz' => 'baz'
				]
			]
		]);

		$longConfig = $this->plugin->configFromString('foo.bar.faz');
		$this->assertEquals('baz', $this->plugin->resolve($longConfig));
	}


}