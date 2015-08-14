<?php

namespace Aeris\ZfDiConfig\Options;

use Zend\Stdlib\AbstractOptions;

class ZfDiConfigOptions extends AbstractOptions {
	private $plugins = [];

	/** @var string Name of the default plugin */
	private $defaultPlugin = '$factory';

	/**
	 * @return array
	 */
	public function getPlugins() {
		return $this->plugins;
	}

	/**
	 * @param array $plugins
	 */
	public function setPlugins(array $plugins) {
		$this->plugins = $plugins;
	}

	/**
	 * @return string
	 */
	public function getDefaultPlugin() {
		return $this->defaultPlugin;
	}

	/**
	 * @param string $defaultPlugin
	 */
	public function setDefaultPlugin($defaultPlugin) {
		$this->defaultPlugin = $defaultPlugin;
	}
}