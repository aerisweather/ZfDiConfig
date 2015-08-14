<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


class FactoryPlugin extends AbstractConfigPlugin {

	/**
	 * @param string|array $config
	 * @return mixed
	 */
	public function resolve($config) {
		if (is_string($config)) {
			$config = $this->configFromString($config);
		}

		if (!isset($config['args'])) {
			$service = new $config['class'];
		}
		else {
			// Create service with constructor arguments
			$rClass = new \ReflectionClass($config['class']);

			$args = array_map([$this, 'resolveArg'], $config['args']);

			$service = $rClass->newInstanceArgs($args);
		}

		// Run setters
		if (isset($config['setters'])) {
			foreach ($config['setters'] as $propName => $arg) {
				$serviceToSet = $this->resolveArg($arg);
				$setterMethod = 'set' . ucfirst($propName);
				$service->$setterMethod($serviceToSet);
			}
		}

		return $service;
	}

	protected function resolveArg($arg) {
		if (!is_array($arg)) {
			return $this->pluginManager->resolve($arg);
		}
		$keys = array_keys($arg);
		$isArrayOfRefs = !is_string(reset($keys));

		if ($isArrayOfRefs) {
			return array_map([$this, 'resolveArg'], $arg);
		}

		return $this->pluginManager->resolve($arg);
	}

	public function configFromString($string) {
		return [
			'class' => $string
		];
	}
}