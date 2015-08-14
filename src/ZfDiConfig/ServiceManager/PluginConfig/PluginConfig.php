<?php


namespace Aeris\ZfDiConfig\ServiceManager\PluginConfig;


class PluginConfig {

	public $name;
	public $config;

	/**
	 * PluginConfig constructor.
	 */
	public function __construct($name, $config = null) {
		$this->name = $name;
		$this->config = $config;
	}

}