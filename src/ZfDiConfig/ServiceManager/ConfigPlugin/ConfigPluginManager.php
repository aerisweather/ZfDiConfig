<?php
namespace Aeris\ZfDiConfig\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\Exception\InvalidConfigException;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigPluginManager {

	/** @var ServiceLocatorInterface */
	protected $serviceLocator;

	/** @var ConfigPluginInterface[] */
	protected $plugins =[];

	/**
	 * Map of plugin short names to long names
	 *
	 * @var string[]
	 */
	protected $pluginShortNames = [];

	/**
	 * @param string|array $ref
	 * @return mixed
	 */
	public function resolve($ref) {
		if (is_string($ref)) {
			$shortName = $this->getShortNameFromString($ref);
			$configString = substr($ref, strlen($shortName));

			$plugin = $this->getPluginByShortName($shortName);
			$pluginConfig = $plugin->configFromString($configString);
		}
		else {
			// Plugin name should be first key in ref array
			$refKeys = array_keys($ref);
			$pluginName = reset($refKeys);

			$plugin = @$this->plugins[$pluginName];

			if (!$plugin) {
				throw new InvalidConfigException("'$pluginName' is not a valid plugin.");
			}

			$pluginConfig = $ref[$pluginName];
		}


		return $plugin->resolve($pluginConfig);
	}

	/**
	 * @param $config
	 * @return [ConfigPluginInterface, array]
	 * @throws InvalidConfigException
	 */
	protected function getPluginAndConfig($config) {
		if (is_string($config)) {
			$shortName = $this->getShortNameFromString($config);
			$configString = substr($config, strlen($shortName));

			$plugin = $this->getPluginByShortName($shortName);
			return [$plugin, $plugin->configFromString($configString)];
		}

		$configKeys = array_keys($config);
		$pluginName = reset($configKeys);
		$plugin = $this->plugins[$pluginName];

		if (!$plugin) {
			throw new InvalidConfigException("'$pluginName' is not a valid plugin.");
		}

		return [$plugin, $config[$pluginName]];
	}

	/**
	 * @param string $configString
	 * @return string
	 */
	protected function getShortNameFromString($configString) {
		$shortNames = array_keys($this->pluginShortNames);

		// Sort by longest first
		usort($shortNames, function($a, $b) {
			return strlen($b) - strlen($a);
		});

		return array_reduce($shortNames, function($foundShortName, $thisShortName) use ($configString) {
			if (!is_null($foundShortName)) {
				return $foundShortName;
			}

			// Check for shortName at the start of the config string
			$isMatch = strpos($configString, $thisShortName) === 0;

			return $isMatch ? $thisShortName : null;
		}, null);
	}

	/** @return ConfigPluginInterface */
	protected function getPluginByShortName($shortName) {
		$pluginName = @$this->pluginShortNames[$shortName];

		if (is_null($pluginName) || !isset($this->plugins[$pluginName])) {
			throw new InvalidConfigException("'$shortName' is not a valid plugin short name.");
		}

		return $this->plugins[$pluginName];
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