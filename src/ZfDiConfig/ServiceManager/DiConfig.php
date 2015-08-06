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


	/**
	 * @param ConfigPluginManager $pluginManager
	 */
	public function setPluginManager(ConfigPluginManager $pluginManager) {
		$this->pluginManager = $pluginManager;
	}

}