<?php


namespace Aeris\ZfDiConfigTest\Fixture\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\AbstractConfigPlugin;

class StringPlugin extends AbstractConfigPlugin {

	/**
	 * @param string|array $config
	 * @return mixed
	 */
	public function resolve($config) {
		return $config['val'];
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
			'val' => $string
		];
	}
}