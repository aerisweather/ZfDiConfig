<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceManagerPlugin;
use Aeris\ZfDiConfigTest\Fixture\ConfigPlugin\StringPlugin;
use Zend\ServiceManager\AbstractPluginManager;

class ServiceManagerTest extends ConfigPluginTestCase {

	/** @var ServiceManagerPlugin */
	protected $serviceManagerPlugin;

	protected function setUp() {
		parent::setUp();

		$this->serviceManagerPlugin = new ServiceManagerPlugin();
		$this->serviceManagerPlugin->setServiceLocator($this->serviceManager);
		$this->serviceManagerPlugin->setPluginManager($this->pluginManager);

		$this->serviceManager->setService('Aeris\ZfDiConfig\Options\ZfDiConfigOptions', new ZfDiConfigOptions([
			'default_plugin' => '$factory'
		]));

		$this->serviceManager->setService('Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager', $this->pluginManager);
	}

	/** @test */
	public function shouldCreateAPluginManager() {
		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([
			'config' => [
				'factories' => [
					'foo' => function () {
						return 'bar';
					}
				]
			]
		]);

		$this->assertInstanceOf('\Zend\ServiceManager\AbstractPluginManager', $serviceManager);
		$this->assertEquals('bar', $serviceManager->get('foo'));
	}

	/** @test */
	public function shouldValidatePluginsByServiceType_passing() {
		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([
			'service_type' => '\DateTime',
			'config' => [
				'factories' => [
					'now' => function () {
						return new \DateTime();
					}
				]
			]
		]);

		$serviceManager->get('now');
	}

	/**
	 * @test
	 * @expectedException \Zend\ServiceManager\Exception\RuntimeException
	 */
	public function shouldValidatePluginsByServiceType_failing() {
		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([
			'service_type' => '\DateTime',
			'config' => [
				'factories' => [
					'foo' => function () {
						return 'bar';
					}
				]
			]
		]);

		$serviceManager->get('foo');
	}

	/** @test */
	public function shouldAcceptDiConfigWithRegisteredPlugins() {
		$this->pluginManager->registerPlugin(new StringPlugin(), '$=', '$=');

		/** @var ZfDiConfigOptions $options */
		$options = $this->serviceManager->get('Aeris\ZfDiConfig\Options\ZfDiConfigOptions');
		$options->setDefaultPlugin('$=');

		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([
			'config' => [
				'di' => [
					'foo' => '$=bar',
					'faz' => [
						'val' => 'baz'
					]
				]
			]
		]);

		$this->assertEquals('bar', $serviceManager->get('foo'));
		$this->assertEquals('baz', $serviceManager->get('faz'), 'should use default plugin');
	}

	/** @test */
	public function shouldShareTheTopLevelServiceManager() {
		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([]);

		$this->assertSame($serviceManager->getServiceLocator(), $this->serviceManager);
	}

}