<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


class FactoryPlugin extends AbstractConfigPlugin {

	/**
	 * @param string|array $config
	 * @return mixed
	 */
	public function resolve($config) {
		if (!isset($config['args'])) {
			$service = new $config['class'];
		}
		else {
			// Create service with constructor arguments
			$rClass = new \ReflectionClass($config['class']);

			$args = array_map(function($arg) {
				return $this->pluginManager->resolve($arg);
			}, $config['args']);

			$service = $rClass->newInstanceArgs($args);
		}

		// Run setters
		if (isset($config['setters'])) {
			foreach ($config['setters'] as $name => $serviceRef) {
				$serviceToSet = $this->pluginManager->resolve($serviceRef);
				$setterMethod = 'set' . ucfirst($name);
				$service->$setterMethod($serviceToSet);
			}
		}

		return $service;
	}

	public function configFromString($string) {
		return [
			'class' => $string
		];
	}
}