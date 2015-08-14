<?php
namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;
use Aeris\ZfDiConfig\ServiceManager\PluginConfig\PluginConfig;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigPluginManager {

	/** @var ServiceLocatorInterface */
	protected $serviceLocator;

	/** @var ConfigPluginInterface[] */
	protected $plugins = [];

	/**
	 * Map of plugin short names to long names
	 *
	 * @var string[]
	 */
	protected $pluginShortNames = [];

	public function canResolve($ref) {
		$ref = $this->normalizeRef($ref);

		return $ref->name !== null && isset($this->plugins[$ref->name]);
	}

	/** @return PluginConfig */
	protected function normalizeRef($ref) {
		if (is_string($ref)) {
			$shortName = $this->getShortNameFromString($ref);
			$pluginName = @$this->pluginShortNames[$shortName];

			$configString = substr($ref, strlen($shortName));

			$plugin = $this->getPluginByShortName($shortName);
			$pluginConfig = $plugin ? $plugin->configFromString($configString) : null;
		}
		else if (is_array($ref)) {
			$refKeys = array_keys($ref);
			$pluginName = reset($refKeys);
			$pluginConfig = @$ref[$pluginName];
		}
		else {
			throw new InvalidConfigException('Invalid reference of type ' . gettype($ref));
		}

		return new PluginConfig($pluginName, $pluginConfig);
	}

	/**
	 * @param string|array $ref
	 * @return mixed
	 */
	public function resolve($ref) {
		if (!$this->canResolve($ref)) {
			throw new InvalidConfigException("Invalid plugin reference: " . json_encode($ref));
		}

		$config = $this->normalizeRef($ref);
		$plugin = $this->getPlugin($config->name);
		return $plugin->resolve($config->config);
	}

	/**
	 * @param $config
	 * @return [ConfigPluginInterface, array]
	 * @throws InvalidConfigException
	 */

	/**
	 * @param string $configString
	 * @return string
	 */
	protected function getShortNameFromString($configString) {
		$shortNames = array_keys($this->pluginShortNames);

		// Sort by longest first
		usort($shortNames, function ($a, $b) {
			return strlen($b) - strlen($a);
		});

		return array_reduce($shortNames, function ($foundShortName, $thisShortName) use ($configString) {
			if (!is_null($foundShortName)) {
				return $foundShortName;
			}

			// Check for shortName at the start of the config string
			$isMatch = strpos($configString, $thisShortName) === 0;

			return $isMatch ? $thisShortName : null;
		}, null);
	}

	/**
	 * @param string $name
	 * @return ConfigPluginInterface
	 * @throws InvalidConfigException
	 */
	protected function getPlugin($name) {
		$plugin = @$this->plugins[$name];

		return $plugin;
	}

	/** @return ConfigPluginInterface */
	protected function getPluginByShortName($shortName) {
		$pluginName = @$this->pluginShortNames[$shortName];

		return @$this->plugins[$pluginName];
	}

	public function registerPlugin(ConfigPluginInterface $plugin, $name, $shortName = null) {
		$this->plugins[$name] = $plugin;
		if (!is_null($shortName)) {
			$this->pluginShortNames[$shortName] = $name;
		}

		$plugin->setServiceLocator($this->serviceLocator);
		$plugin->setPluginManager($this);
	}

	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}

}