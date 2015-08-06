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
