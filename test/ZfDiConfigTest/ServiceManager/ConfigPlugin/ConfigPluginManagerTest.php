<?php

namespace Aeris\ZfDiConfigTest\ServiceManager\ConfigPlugin;


use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager;
use Aeris\ZfDiConfigTest\Fixture\ConfigPlugin\StringPlugin;
use Zend\ServiceManager\ServiceManager;

class ConfigPluginManagerTest extends \PHPUnit_Framework_TestCase {

	/** @test */
	public function canResolve_shouldReturnTrueIfConfigMatchesAPluginName() {
		$pluginManager = new ConfigPluginManager();
		$pluginManager->setServiceLocator(new ServiceManager);
		$pluginManager->registerPlugin(new StringPlugin(), '$pluginA', '$pB::');
		$pluginManager->registerPlugin(new StringPlugin(), '$pluginB', '$pB::');

		$this->assertTrue($pluginManager->canResolve([
			'$pluginB' => 'foo'
		]), 'Should return true for a long name');

		$this->assertTrue($pluginManager->canResolve('$pB::foo'), 'Should return true for a short name');

		$this->assertFalse($pluginManager->canResolve([
			'$notAPlugin' => 'foo'
		]), 'Should return false for a bad long name');

		$this->assertFalse($pluginManager->canResolve('$XXX::foo'), 'Should return false for a bad short name');
	}

}
