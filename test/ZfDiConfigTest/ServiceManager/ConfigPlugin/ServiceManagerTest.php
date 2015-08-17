<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigParamPlugin;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceManagerPlugin;
use Aeris\ZfDiConfigTest\Fixture\ConfigPlugin\StringPlugin;
use Zend\ServiceManager\AbstractPluginManager;

class ServiceManagerTest extends ConfigPluginTestCase {

	/** @var ServiceManagerPlugin */
	protected $serviceManagerPlugin;

	protected function setUp() {
		parent::setUp();

		$this->serviceManagerPlugin = new ServiceManagerPlugin();
		$this->pluginManager->registerPlugin($this->serviceManagerPlugin, '$serviceManager');

		$this->pluginManager->registerPlugin(new FactoryPlugin(), '$factory', '$factory');
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

		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([
			'config' => [
				'di' => [
					'foo' => '$=bar',
					'now' => '\DateTime'
				]
			]
		]);

		$this->assertEquals('bar', $serviceManager->get('foo'));
		$this->assertInstanceOf('\DateTime', $serviceManager->get('now'), 'should use default $factory plugin');
	}

	/** @test */
	public function shouldShareTheTopLevelServiceManager() {
		/** @var AbstractPluginManager $serviceManager */
		$serviceManager = $this->serviceManagerPlugin->resolve([]);

		$this->assertSame($serviceManager->getServiceLocator(), $this->serviceManager);
	}

	/** @test */
	public function shouldAcceptConfigReferences() {
		$this->pluginManager->registerPlugin(new ConfigParamPlugin(), '%', '%');

		$this->serviceManager->setService('config', [
			'my_conf' => [
				'invokables' => [
					'now' => '\DateTime'
				]
			]
		]);

		$serviceManager = $this->serviceManagerPlugin->resolve([
			'config' => '%my_conf'
		]);

		$this->assertInstanceOf('\DateTime', $serviceManager->get('now'));
	}

}