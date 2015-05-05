<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Zend\ServiceManager\ServiceLocatorInterface;

interface ConfigPluginInterface {

	/**
	 * @param string|array $config
	 * @return mixed
	 */
	public function resolve($config);

	/**
	 * Create a string configuration
	 * into a config array
	 *
	 * @param string $string
	 * @return array
	 */
	public function configFromString($string);

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator);

	public function setPluginManager(ConfigPluginManager $pluginManager);

}