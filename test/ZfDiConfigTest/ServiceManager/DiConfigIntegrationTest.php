<?php

namespace Aeris\ZfDiConfigTest\ServiceManager;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin;
use Aeris\ZfDiConfig\ServiceManager\DiConfig;
use Aeris\ZfDiConfigTest\Fixture\ConfigPlugin\StringPlugin;
use Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService;
use Zend\ServiceManager\ServiceManager;

class DiConfigIntegrationTest extends \PHPUnit_Framework_TestCase {

	/** @var ServiceManager */
	protected $serviceManager;

	/** @var ConfigPluginManager */
	protected $pluginManager;

	protected function setUp() {
		parent::setUp();

		$this->serviceManager = new ServiceManager();

		$this->pluginManager = new ConfigPluginManager();
		$this->pluginManager->setServiceLocator($this->serviceManager);
		$this->pluginManager->registerPlugin(new FactoryPlugin(), '$factory');
		$this->pluginManager->registerPlugin(new ServiceResolverPlugin(), '$service', '@');
		$this->pluginManager->registerPlugin(new StringPlugin(), '$=', '$=');
	}

	protected function createDiConfig(array $config = []) {
		$diConfig = new DiConfig($config);
		$diConfig->setPluginManager($this->pluginManager);
		$diConfig->setDefaultPlugin('$factory');
		
		return $diConfig;
	}

	/** @test */
	public function shouldCreateAServiceWithInjectedServices() {
		$diConfig = $this->createDiConfig([
			'BarService' => '\stdClass',
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'args' => [
					[
						'$factory' => [
							'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
						]
					]
				],
				'setters' => [
					'bar' => '@BarService',
				]
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		/** @var FooService $fooService */
		$fooService = $this->serviceManager->get('FooService');
		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService', $fooService);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$fooService->constructorArgs[0], 'Should allow nested factories');

		$barService = $this->serviceManager->get('BarService');
		$this->assertInstanceOf('\stdClass', $barService, 'Should create an "invokable" service');
		$this->assertSame($barService, $fooService->bar, 'Should inject services in other services');
	}

	/** @test */
	public function shouldAcceptAPluginAtTheTopLevel_longPluginName() {
		$diConfig = $this->createDiConfig([
			'FooString' => [
				'$=' => ['val' => 'foo']
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->assertEquals('foo', $this->serviceManager->get('FooString'));
	}

	/** @test */
	public function shouldAcceptAPluginAtTheTopLevel_shortPluginName() {
		$diConfig = $this->createDiConfig([
			'FooString' => '$=foo'
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->assertEquals('foo', $this->serviceManager->get('FooString'));
	}

	/** @test */
	public function shouldUseACustomDefaultPlugin() {
		$diConfig = $this->createDiConfig([
			'FooString' => [
				'val' => 'foo'
			]
		]);
		$diConfig->setDefaultPlugin('$=');
		$diConfig->configureServiceManager($this->serviceManager);

		$this->assertEquals('foo', $this->serviceManager->get('FooString'));
	}

}