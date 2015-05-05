<?php
return [
	'zf_di_config' => [
		'plugins' => [
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\FactoryPlugin',
				'name' => '$factory',
			],
			[
				'class' => '\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ServiceResolverPlugin',
				'name' => '$service',
				'short_name' => '@',
			]
		]
	]
];