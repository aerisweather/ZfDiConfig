<?php

namespace Aeris\ZfDiConfig\Factory;

use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Zend\ServiceManager\FactoryInterface;

class ConfigPluginManagerFactory implements FactoryInterface {

	/**
	 * Create service
	 *
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return ConfigPluginManager
	 */
	public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
		/** @var ZfDiConfigOptions $options */
		$options = $serviceLocator->get('Aeris\ZfDiConfig\Options\ZfDiConfigOptions');

		$pluginManager = new ConfigPluginManager();

		$pluginFqcns = $options->getPlugins();
		foreach ($pluginFqcns as $pluginConfig) {
			$plugin = new $pluginConfig['class'];

			$pluginManager->registerPlugin($plugin, $pluginConfig['name'], @$pluginConfig['short_name']);
		}

		return $pluginManager;
	}
}