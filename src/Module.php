<?php

namespace Aeris\ZfDiConfig;

use Aeris\ZfDiConfig\ServiceManager\DiConfig;
use Zend\Mvc\MvcEvent;

class Module {

	public function onBootstrap(MvcEvent $event) {
		$serviceManager = $event->getApplication()->getServiceManager();

		/** @var DiConfig $diConfig */
		$diConfig = $serviceManager->get('Aeris\ZfDiConfig\ServiceManager\DiConfig');

		$diConfig->configureServiceManager($serviceManager);
	}

	public function getConfig() {
		return include __DIR__ . '/../config/module.config.php';
	}

}