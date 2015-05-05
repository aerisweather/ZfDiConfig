<?php

namespace Aeris\ZfDiConfigTest\ServiceManager;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin;
use Aeris\ZfDiConfig\ServiceManager\DiConfig;
use Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService;
use Zend\ServiceManager\ServiceManager;

class DiConfigTest extends \PHPUnit_Framework_TestCase {

	/** @var ServiceManager */
	protected $serviceManager;

	/** @var ConfigPluginManager */
	protected $pluginManager;

	/** @var DiConfig */
	protected $diConfig;

	protected function setUp() {
		parent::setUp();

		$this->serviceManager = new ServiceManager();

		$this->pluginManager = new ConfigPluginManager();
		$this->pluginManager->setServiceLocator($this->serviceManager);
		$this->pluginManager->registerPlugin(new FactoryPlugin(), '$factory');
		$this->pluginManager->registerPlugin(new ServiceResolverPlugin(), '$service', '@');
	}

	protected function setUpDiConfig(array $config = []) {
		$this->diConfig = new DiConfig($config);
		$this->diConfig->setPluginManager($this->pluginManager);
	}


	/** @test */
	public function shouldCreateAServiceAsAnInstanceOfAClass() {
		$this->setUpDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService'
			]
		]);
		$this->diConfig->configureServiceManager($this->serviceManager);

		$this->assertInstanceOf('\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
			$this->serviceManager->get('FooService'));
	}

	/** @test */
	public function shouldInjectServicesAsCtorArgs() {
		$this->serviceManager
			->setService('ServiceToInject', $serviceToInject = new \stdClass());

		$this->setUpDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'args' => ['@ServiceToInject']
			]
		]);
		$this->diConfig->configureServiceManager($this->serviceManager);

		/** @var FooService $fooService */
		$fooService = $this->serviceManager->get('FooService');
		$this->assertSame($serviceToInject, $fooService->constructorArgs[0]);
	}

	/** @test */
	public function shouldInjectServicesUsingSetters() {
		$this->serviceManager
			->setService('BarService', $barService = new \stdClass());

		$this->setUpDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => '@BarService'
				]
			]
		]);
		$this->diConfig->configureServiceManager($this->serviceManager);

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

		$this->setUpDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => 'BarService'  // missing @ to denote a service reference
				]
			]
		]);
		$this->diConfig->configureServiceManager($this->serviceManager);

		$this->serviceManager->get('FooService');
	}

	/**
	 * @test
	 * @expectedException \Zend\ServiceManager\Exception\ServiceNotFoundException
	 */
	public function shouldComplainAboutReferencesToUndefinedServices() {
		$this->setUpDiConfig([
			'FooService' => [
				'class' => '\Aeris\ZfDiConfigTest\ServiceManager\Mock\FooService',
				'setters' => [
					'bar' => '@NotAService'  // missing @ to denote a service reference
				]
			]
		]);
		$this->diConfig->configureServiceManager($this->serviceManager);

		$this->serviceManager->get('FooService');
	}

}