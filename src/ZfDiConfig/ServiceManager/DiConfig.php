<?php

namespace Aeris\ZfDiConfig\ServiceManager;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class DiConfig implements  ConfigInterface {

	/** @var ConfigPluginManager */
	protected $pluginManager;

	/** @var array */
	protected $config;

	public function __construct(array $config = []) {
		$this->config = $config;
	}


	public function configureServiceManager(ServiceManager $serviceManager) {
		foreach ($this->config as $serviceName => $serviceConfig) {
			// Create all services using the $factory plugin
			$factory = function() use ($serviceConfig) {
				return $this->pluginManager->resolve([
					'$factory' => $serviceConfig,
				]);
			};
			$serviceManager->setFactory($serviceName, $factory);
		}
	}

	protected function createServiceFactory($serviceConfig, ServiceLocatorInterface $serviceManager) {
		return function() use ($serviceConfig, $serviceManager) {
			if (isset($serviceConfig['args'])) {
				$rClass = new \ReflectionClass($serviceConfig['class']);

				$args = array_map(function($arg) use ($serviceManager) {
					return $this->pluginManager->resolve($arg);
				}, $serviceConfig['args']);

				$service = $rClass->newInstanceArgs($args);
			}
			else {
				$service = new $serviceConfig['class'];
			}

			if (isset($serviceConfig['setters'])) {
				foreach ($serviceConfig['setters'] as $name => $serviceRef) {
					$serviceToSet = $this->pluginManager->resolve($serviceRef);
					$setterMethod = 'set' . ucfirst($name);
					$service->$setterMethod($serviceToSet);
				}
			}

			return $service;
		};
	}

	protected function resolveReference($reference, ServiceLocatorInterface $serviceManager) {
		$isServiceRef = substr($reference, 0, 1) === '@';

		if (!$isServiceRef) {
			throw new InvalidConfigException("Invalid reference '$reference'.");
		}

		$serviceName = substr($reference, 1);
		return $serviceManager->get($serviceName);
	}

	/**
	 * @param ConfigPluginManager $pluginManager
	 */
	public function setPluginManager(ConfigPluginManager $pluginManager) {
		$this->pluginManager = $pluginManager;
	}

}