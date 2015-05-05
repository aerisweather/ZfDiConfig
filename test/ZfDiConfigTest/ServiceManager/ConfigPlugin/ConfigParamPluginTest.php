<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigParamPlugin;
use Zend\ServiceManager\ServiceManager;

class ConfigParamPluginTest extends \PHPUnit_Framework_TestCase {

	/** @var ServiceManager */
	protected $serviceManager;

	/** @var ConfigParamPlugin */
	protected $plugin;

	protected function setUp() {
		parent::setUp();

		$this->serviceManager = new ServiceManager();
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

	/** @test */
	public function resolve_shouldComplainIfTheConfigParamIsNotSet() {
		$this->serviceManager->setService('config', [
			'foo' => [
				'bar' => null
			]
		]);

		$this->assertExceptionThrown('\Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException', function() {
			$this->plugin->resolve([
				'path' => 'foo.bar.faz'
			]);
		});
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

	protected function assertExceptionThrown($exception, callable $cb) {
		$caughtException = null;
		try {
			$cb();
		}
		catch (\Exception $ex) {
			$caughtException = $ex;
		}

		$this->assertNotNull($caughtException,
			"Expected exception $exception to be thrown, but no exception was thrown.");

		$this->assertInstanceOf($exception, $caughtException,
			"Expected exception $exception to be thrown, but actual exception was $caughtException");
	}


}