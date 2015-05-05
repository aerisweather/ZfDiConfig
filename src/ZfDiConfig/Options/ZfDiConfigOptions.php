<?php

namespace Aeris\ZfDiConfig\Options;

use Zend\Stdlib\AbstractOptions;

class ZfDiConfigOptions extends AbstractOptions {
	private $plugins = [];

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
}