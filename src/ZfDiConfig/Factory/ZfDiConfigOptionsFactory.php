<?php


namespace Aeris\ZfDiConfig\Factory;


use Aeris\ZfDiConfig\Options\ZfDiConfigOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ZfDiConfigOptionsFactory implements FactoryInterface {

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return ZfDiConfigOptions
	 */
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$config = @$serviceLocator->get('config')['zf_di_config'];

		return new ZfDiConfigOptions($config);
	}
}