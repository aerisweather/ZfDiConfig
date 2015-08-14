# ZfDiConfig

Configurable dependency injection for the ZF2 ServiceManager.


## Installation

You can install ZfDiConfig using composer:

```
composer require aeris/zf-di-config
```

Then add the module to your application config:

```
// config/application.config.php
return [
	'modules' => [
    	'ZfDiConfig',
    	// ...
    ],
    // ...
];
```


## Basic Usage

ZfDiConfig allows you to configure services, instead of using service factories. For example:

```php
// module.config.php
return [
	'my_app' => [
    	'foo' => [
        	'bar' => 'qux'
        ]
    ],
    'service_manager' => [
    	// Config for ZfDiConfig
    	'di' => [
        	'SomeService' => '\MyApp\SomeService',
            'FooService' => [
            	'class' => '\MyApp\FooService',
                'args' => ['@SomeService'],
                'setters' => [
                	'bar' => '%my_app.foo.bar'
                ]
            ]
        ]
    ]
];
```

In this example, we:

* Created a service called "SomeService" which is an instance of `\MyApp\SomeService`
* Created a service called "FooService" which is an instance of `\MyApp\FooService`
* Injected `SomeService` into `FooService` as a constructor argument
* Set the configured value of `bar`, using `FooService::setBar()`

Compare this to a typical ZF2 approach for creating services:

```php
// module.config.php
return [
	'my_app' => [
    	'foo' => [
        	'bar' => 'qux'
        ]
    ],
	'service_manager' => [
    	'invokables' => [
        	'SomeService' => '\MyApp\SomeService',
        ]
        'factories' =>" [
        	'FooService' => '\MyApp\Factory\FooServiceFactory.php'
        ]
    ]
];

// MyApp/Factory/FooServiceFactory.php
namespace MyApp\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FooServiceFactory implements FactoryInterface {
	public function createService(ServiceLocatorInterface $serviceLocator) {
    	$someService = $serviceLocator->get('SomeService');
        
        // Inject SomeService into FooService
    	$fooService = new \MyApp\FooService($someService);
        
        // Set the a parameter from the module config
        // onto FooService
        $config = $serviceLocator->get('config');
        $bar = $config['my_app']['foo']['bar'];
        $fooService->setBar($bar);
        
        return $fooService;
    }
}
```

This approach works, but it can become very verbose, and it makes it difficult to see at a glance how your services are wired together.

ZfDiConfig takes a different approach, by allowing you to configure how your services are wired together:



## Plugins

ZfDiConfig uses plugins to resolve configured references. A few plugins are provided to cover basic usage, though you can easily [add your own plugins](#extending-zfdiconfig), too.

Most plugins allow either a short or long form of configuration. For example, the [ServiceResolverPlugin](#serviceresolverplugin) can be referenced as `$service` or using the `@` prefix:

```php
[
	'FooService' => [
    	'class' => '\MyApp\FooService',
        'args' => [
        	// these two arguments are equivalent
        	'@BarService',
           	[
            	'$service' => [
                	'name' => 'BarService'
                ]
            ]
        ]
    ]
]
```

### Default Plugin

By default, ZfDiConfig uses the `$factory` plugin for configuring services, so that:

```php
[
	'FooService' => '\App\Service\Foo'
]
```

...is the same as...

```php
[
	'FooService' => [
		'$factory' => '\App\Service\Foo'
	]
]
```

If you do not want to define your service using the `$factory` plugin, there are a couple of options. For one, you can just use a different plugin, and `DiConfig` will be smart enough to figure it out.

```php
[
	'FooConfig' => '%my_app.options.foo'  // DiConfig won't try to use $factory here
]
```

You can also override the global default plugin, in the `zf_di_config` module config:

```php
[
	'zf_di_config' => [
		'default_plugin' => '$myAwesomePlugin',
		'plugins' => [...]
	]
]
```

### FactoryPlugin

This is the default plugin used when configuring a service. It allows you to create configured objects instances. You have already seen how this plugin is used in the [basic usage examples](#basic-usage). 

One cool thing is that you can actually use this plugin 'inline' from within other plugin configuration. Let me demonstrate:

```
[
	'FooService' => [
    	'class' => '\MyApp\FooService',
        'args' => [
        	// Inject a BarService instance "on the fly"
            [ $factory' => '\MyApp\BarService' ]
        ]
    ]
]
```

#### Configuration Reference

The `FactoryPlugin` can accept the following configurations:

##### Short Form:

```php
[
	'$factory' => '\MyApp\FooService'
]
```

##### Really Short Form:

```php
[
	'$factory:\MyApp\FooService'
]
```

##### Long Form:

```php
[
	'$factory' => [
    	// Object class (required)
    	'class' => '\MyApp\FooService',
        // services to inject in constructor (optional)
        'args' => ['@SomeService'],
        // Service to inject using setters
        'setters' => [	
        	// This will inject 'SomeService'
            // using the FooService::setBar() method
        	'bar' => '@SomeService'
        ]
    ]
]
```


### ServiceResolverPlugin

The service resolver plugin resolves references to existing services.

##### Configuration Reference

###### Short Form:

```php
[
	'@NameOfService`, 		// resolves to $serviceLocator->get('NameOfService')
    '@NameOfService::foo' 	// resolves to $serviceLocator->get('NameOfService')->getFoo()
]
```

##### Long Form:

```php
[
	'$service' => [
    	'name' => 'NameOfService',
        // optional
        'getter' => 'foo'
    ]
]
```


### ConfigParamPlugin

The `ConfigParamPlugin` allows you to access raw values from you application config.

#### Configuration Reference

##### Short Form

```php
[
	// resolves to $serviceLocator->get('config')['foo']['bar']['faz']
	'%foo.bar.faz'
]
```

##### Long Form

```php
[
	'$param' => [
    	'path' => 'foo.bar.faz',
        'default' => 'qux'
    ]
]
```



## Extending ZfDiConfig

You can extend ZfDiConfig using custom plugins. Plugins must implement `\Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\ConfigPluginInterface`, and be registered in your module config.

### Example

Let's say you want to create a plugin to resolve Doctrine `EntityRepository` objects. We want to be able to use it like so:

```php
// module.config.php
return [
	'service_manager' => [
    	'di' => [
        	'FooService' => [
            	'class' => 'MyApp\FooService',
                'setters' => [
                	'barRepo' => '$repo:MyApp\Entity\Bar'
                ]
            ]
        ]
    ]
];
```

First we implement the plugin logic:

```php
namespace MyApp\ServiceManager\ConfigPlugin;

use Aeris\ZfDiConfig\ServiceManager\ConfigPlugin\AbstractConfigPlugin;

class DoctrineRepositoryPlugin extends AbstractConfigPlugin {

	public function resolve($pluginConfig) {
    	// Grab the Doctrine EntityManager from the service locator
        $entityManager = $this->serviceLocator->get('entity_manager');
        
        // Return the configured repository
        return $entityManager->get($pluginConfig['entity_class']);
    }
    
    public function configFromString($string) {
    	// Convert a short-form config into an array
        return [
        	'entity_class' => $string;
        ]
    }

}
```

Now we just need to register the plugin

```php
/// module.config.php
return [
	'zf_di_config' => [
    	'plugins' => [
        	[
            	'class' => 'MyApp\ServiceManager\ConfigPlugin\DoctrineRepositoryPlugin',
                'name' => '$repo',
                'short_name' => '$repo:'
            ]
        ]
    ]
];
```

And you're ready to go!
