<?php


namespace Aeris\ZfDiConfig\ServiceManager;


use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * A configurable AbstractPluginManager.
 *
 * Used by the `
 */
class ServiceManager extends AbstractPluginManager {

	/** @var string FQCN */
	protected $serviceType;

	/**
	 * Validate the plugin
	 *
	 * Checks that the filter loaded is either a valid callback or an instance
	 * of FilterInterface.
	 *
	 * @param  mixed $plugin
	 * @return void
	 * @throws Exception\RuntimeException if invalid
	 */
	public function validatePlugin($plugin) {
		if (!$this->serviceType) {
			return;
		}

		if (!($plugin instanceof $this->serviceType)) {
			$actualType = is_object($plugin) ? get_class($plugin) : gettype($plugin);

			throw new Exception\RuntimeException("Expected plugin to be a {$this->serviceType}, " .
				"but it is a $actualType");
		}
	}

	/**
	 * @param string $serviceType
	 */
	public function setServiceType($serviceType) {
		$this->serviceType = $serviceType;
	}

	public function getAllServices() {
		$cNames = $this->getCanonicalNames();
		return array_map([$this, 'get'], $cNames);
	}
}