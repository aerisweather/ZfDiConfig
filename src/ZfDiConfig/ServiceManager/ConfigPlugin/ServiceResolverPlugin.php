<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;

class ServiceResolverPlugin extends AbstractConfigPlugin {

	public function resolve($config) {
		$serviceName = $config['name'];

		$service = $this->serviceLocator->get($serviceName);

		if (@$config['getter']) {
			$getterMethodName = 'get' . ucfirst($config['getter']);

			if (!method_exists($service, $getterMethodName)) {
				throw new InvalidConfigException(
					"Unable to get property '{$config['getter']}' from '$serviceName': " .
					"no getter method exists for the property."
				);
			}


			return $service->$getterMethodName();
		}

		return $service;
	}

	/**
	 * Create a string configuration
	 * into a config array
	 *
	 * @param string $string
	 * @return array
	 */
	public function configFromString($string) {
		$parts = explode('::', $string);

		return [
			'name' => $parts[0],
			'getter' => @$parts[1]
		];
	}
}