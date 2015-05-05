<?php


namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;
use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;


/**
 * Resolves references to configured parameters,
 * using dot-notation.
 */
class ConfigParamPlugin extends AbstractConfigPlugin {

	const NOT_FOUND = 'CONFIG_PARAM_PLUGIN_NOT_FOUND';

	/**
	 * @param string|array $pluginConfig
	 * @return mixed
	 */
	public function resolve($pluginConfig) {
		$serviceManagerConfig = $this->serviceLocator->get('config');

		$path = $pluginConfig['path'];
		$default = array_key_exists('default', $pluginConfig) ? $pluginConfig['default'] : self::NOT_FOUND;

		$value = $this->resolveDotNotation($serviceManagerConfig, $path, $default);

		if ($value === self::NOT_FOUND) {
			throw new InvalidConfigException("Unable to resolve config param '$path'");
		}

		return $value;
	}

	protected function resolveDotNotation(array $arr, $path, $default = null) {
		$current = $arr;
		$p = strtok($path, '.');

		while ($p !== false) {
			if (!is_array($current) || !array_key_exists($p, $current)) {
				return $default;
			}
			$current = $current[$p];
			$p = strtok('.');
		}

		return $current;
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
			'path' => $string
		];
	}
}