# v1.3.1

* FIX: `$serviceManager` plugin accepts DI reference as `config`

# v1.3.0

* ADD: New `$serviceManager` plugin

# v1.2.1

* ADD: Configure `Aeris\ZfDiConfig\ServiceManager` service (application service manager)

# v1.2.0

* ADD: Support using plugins other that `$factory` for top-level service definitions.

# v1.1.0

* ADD: Support arrays of references.

# v1.0.2

* FIX: Was missing service manager configuration for all ZfDiConfig services.

# v1.0.1

* FIX: Composer config was missing autoload for `Aeris\ZfDiConfig\Module`


# v1.0.0

Initial Release.

* `DiConfig` component, which creates services using configuration only.
* Plugin architecture for `DiConfig`
* Built-in plugins: `$factory`, `$service`/'@', `$param`/`%`
* Bootstrap `DiConfig` onto application service manager, using
  the ZfDiConfig module.
