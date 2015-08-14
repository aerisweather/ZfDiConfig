<?php


namespace Aeris\ZfDiConfig\Factory;


use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfig\ServiceManager\DiConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DiConfigFactory implements FactoryInterface {

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return mixed
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$serviceManagerConfig = @$serviceLocator->get('config')['service_manager']['di'] ?: [];
		$diConfig = new DiConfig($serviceManagerConfig);

		/** @var ZfDiConfigOptions $options */
		$options = $serviceLocator->get('Aeris\ZfDiConfig\Options\ZfDiConfigOptions');
		$diConfig->setDefaultPlugin($options->getDefaultPlugin());

		/** @var ConfigPluginManager $pluginManager */
		$pluginManager = $serviceLocator->get('Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager');
		$diConfig->setPluginManager($pluginManager);

		return $diConfig;
	}
}