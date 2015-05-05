<?php


namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Zend\ServiceManager\ServiceManager;

class ConfigPluginTestCase extends \PHPUnit_Framework_TestCase {

	/** @var ServiceManager */
	protected $serviceManager;

	protected function setUp() {
		parent::setUp();

		$this->serviceManager = new ServiceManager();
	}
}