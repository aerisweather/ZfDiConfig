<?php

namespace Aeris\ZfDiConfigTest\ServiceManager;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin;
use Aeris\ZfDiConfig\ServiceManager\DiConfig;
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
	}

	protected function createDiConfig(array $config = []) {
		$diConfig = new DiConfig($config);
		$diConfig->setPluginManager($this->pluginManager);
		
		return $diConfig;
	}


	/** @test */
	public function shouldCreateAServiceAsAnInstanceOfAClass() {
		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$this->serviceManager->get('FooService'));
	}
	/** @test */
	public function shouldCreateAServiceAsAnInstanceOfAClass_shortHand() {
		$diConfig = $this->createDiConfig([
			'FooService' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$this->serviceManager->get('FooService'));
	}

	/** @test */
	public function shouldAllowNestedPlugins() {
		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'args' => [
					[
						'$factory' => [
							'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
						]
					]
				]
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		/** @var FooService $fooService */
		$fooService = $this->serviceManager->get('FooService');
		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService', $fooService);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$fooService->constructorArgs[0]);
	}

	/** @test */
	public function shouldInjectServicesAsCtorArgs() {
		$this->serviceManager
			->setService('ServiceToInject', $serviceToInject = new \stdClass());

		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'args' => ['@ServiceToInject']
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		/** @var FooService $fooService */
		$fooService = $this->serviceManager->get('FooService');
		$this->assertSame($serviceToInject, $fooService->constructorArgs[0]);
	}

	/** @test */
	public function shouldInjectServicesUsingSetters() {
		$this->serviceManager
			->setService('BarService', $barService = new \stdClass());

		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => '@BarService'
				]
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		/** @var FooService $fooService */
		$fooService = $this->serviceManager->get('FooService');
		$this->assertSame($barService, $fooService->bar);
	}

	/**
	 * @test
	 * @expectedException \Zend\ServiceManager\Exception\ServiceNotCreatedException
	 */
	public function shouldComplainAboutPoorlyFormattedReferences() {
		$this->serviceManager
			->setService('BarService', $barService = new \stdClass());

		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => 'BarService'  // missing @ to denote a service reference
				]
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->serviceManager->get('FooService');
	}

	/**
	 * @test
	 * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
	 */
	public function shouldComplainAboutReferencesToUndefinedServices() {
		$diConfig = $this->createDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => '@NotAService'  // missing @ to denote a service reference
				]
			]
		]);
		$diConfig->configureServiceManager($this->serviceManager);

		$this->serviceManager->get('FooService');
	}

}