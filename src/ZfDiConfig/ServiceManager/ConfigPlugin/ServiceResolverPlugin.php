<?php

namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


class ServiceResolverPlugin extends AbstractConfigPlugin {

	public function resolve($config) {
		$serviceName = $config['name'];

		return $this->serviceLocator->get($serviceName);
	}

	/**
	 * Create a string configuration
	 * into a config array
	 *
	 * @param string $string
	 * @return array
	 */
	public function configFromString($string) {
		return [
			'name' => $string
		];
	}
}