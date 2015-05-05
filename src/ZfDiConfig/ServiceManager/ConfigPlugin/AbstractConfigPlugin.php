<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractConfigPlugin implements ConfigPluginInterface {

	/** @var ServiceLocatorInterface */
	protected $serviceLocator;

	/** @var ConfigPluginManager */
	protected $pluginManager;

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

	public function setPluginManager(ConfigPluginManager $pluginManager) {
		$this->pluginManager = $pluginManager;
	}
}