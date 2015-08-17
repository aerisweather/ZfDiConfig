<?php
return [
	'zf_di_config' => [
		'default_plugin' => '$factory',
		'plugins' => [
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin',
				'name' => '$factory',
				'short_name' => '$factory:',
			],
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin',
				'name' => '$service',
				'short_name' => '@',
			],
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigParamPlugin',
				'name' => '$param',
				'short_name' => '%'
			],
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceManagerPlugin',
				'name' => '$serviceManager',
			]
		]
	],
	'service_manager' => [
		'factories' => [
			'Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginManager' => '\Aeris\ZfDiConfig\Factory\ConfigPluginManagerFactory',
			'Aeris\ZfDiConfig\ServiceManager\DiConfig' => '\Aeris\ZfDiConfig\Factory\DiConfigFactory',
			'Aeris\ZfDiConfig\Options\ZfDiConfigOptions' => '\Aeris\ZfDiConfig\Factory\ZfDiConfigOptionsFactory',
			'Aeris\ZfDiConfig\ServiceManager' => '\Aeris\ZfDiConfig\Factory\ServiceManagerFactory',
		],
	],
];