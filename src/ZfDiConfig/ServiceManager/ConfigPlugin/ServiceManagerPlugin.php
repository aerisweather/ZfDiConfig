<?php


namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Aeris\ZfDiConfig\ServiceManager\DiConfig;
use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;
use Aeris\ZfDiConfig\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class ServiceManagerPlugin extends AbstractConfigPlugin{

	/**
	 * @param string|array $config
	 * @return mixed
	 */
	public function resolve($config) {
		$serviceManagerConfig = @$config['config'] ?: [];
		$serviceManagerConfig = is_string($serviceManagerConfig) ?
			$this->pluginManager->resolve($serviceManagerConfig) : $serviceManagerConfig;

		$serviceManager = new ServiceManager(new Config($serviceManagerConfig));

		// Parse `di` config for service manager
		$diConfig = $this->createDiConfig(@$serviceManagerConfig['di'] ?: []);
		$diConfig->configureServiceManager($serviceManager);

		$serviceManager->setServiceType(@$config['service_type']);

		$serviceManager->setServiceLocator($this->serviceLocator);

		return $serviceManager;
	}

	protected function createDiConfig($config) {
		$diConfig = new DiConfig($config);

		/** @var ZfDiConfigOptions $options */
		$options = $this->serviceLocator->get('Aeris\ZfDiConfig\Options\ZfDiConfigOptions');
		$diConfig->setDefaultPlugin($options->getDefaultPlugin());

		/** @var ConfigPluginManager $configPluginManager */
		$configPluginManager = $this->serviceLocator->get('Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager');
		$diConfig->setPluginManager($configPluginManager);

		return $diConfig;
	}

	/**
	 * Create a string configuration
	 * into a config array
	 *
	 * @param string $string
	 * @return array
	 */
	public function configFromString($string) {
		throw new InvalidConfigException('ServiceManagerPlugin cannot interpret config from string.');
	}
}