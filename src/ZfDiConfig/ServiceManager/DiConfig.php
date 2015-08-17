<?php

namespace Aeris\ZfDiConfig\ServiceManager;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceManager as ZendServiceManager;

class DiConfig implements ConfigInterface {

	/** @var ConfigPluginManager */
	protected $pluginManager;

	/** @var array */
	protected $config;

	/** @var string */
	protected $defaultPlugin;

	public function __construct(array $config = []) {
		$this->config = $config;
	}


	public function configureServiceManager(ZendServiceManager $serviceManager) {
		foreach ($this->config as $serviceName => $serviceConfig) {
			$serviceManager->setFactory($serviceName, function () use ($serviceConfig) {
				$serviceConfig = $this->pluginManager->canResolve($serviceConfig) ?
					$serviceConfig : [$this->defaultPlugin => $serviceConfig];

				return $this->pluginManager->resolve($serviceConfig);
			});
		}
	}


	/**
	 * @param ConfigPluginManager $pluginManager
	 */
	public function setPluginManager(ConfigPluginManager $pluginManager) {
		$this->pluginManager = $pluginManager;
	}

	/**
	 * @param string $defaultPlugin Name of the default plugin
	 */
	public function setDefaultPlugin($defaultPlugin) {
		$this->defaultPlugin = $defaultPlugin;
	}

}