# TYPO3 Config Handling Extensions

This package extends `helhum/typo3-config-handling` with an extension system
that allows to create flexible configuration templates that can be bundled into
Composer packages and thus easily usable in multiple projects.

## Installation

Simply install the desired extensions, there is normally no need to directly
install this package.

Look for available extensions on [Packagist](https://packagist.org/?type=typo3-config-handling-extension).

The package `helhum/typo3-config-handling` is automatically required by this
package and can be omitted in or removed from your project. For safety, however,
it can be additionally required to prevent accidental uninstall.

## Configuration

To enable support for TYPO3 Config Handling Extensions, add an import line to
your to your YAML configuration:

```yaml
imports:
    - { resource: \Gilbertsoft\TYPO3\ConfigHandling\Extensions, type: class }
```

Imports are processed in the specified order, so the position in your
configuration is very important. Later imports may override the configuration
provided by the extensions again, and any previous configuration will be
overridden by the extensions.

### Additional configuration in the project's `composer.json`

Additionally it is possible to change the loading order of the extensions in
your root `composer.json` in the `extra` section like this:

```json
{
    "name": "vendor/project",
    "type": "project",
    "require": {
        "php": "^7.4 || ^8.0",
        "vendor/typo3-config-handling-extension1": "^1.0",
        "vendor/typo3-config-handling-extension2": "^1.0",
        "vendor/typo3-config-handling-extension3": "^1.0",
        "vendor/typo3-config-handling-extension4": "^1.0"
    },
    "extra": {
        "gilbertsoft/typo3-config-handling-extensions": {
            "extensions": {
                "vendor/typo3-config-handling-extension1": {
                    "before": ["vendor/typo3-config-handling-extension2", "vendor/typo3-config-handling-extension3"],
                    "after": ["vendor/typo3-config-handling-extension4"]
                }
            }
        }
    }
}
```

This will make sure `extension1` is loaded after `extension4` and before
`extension2` and `extension3` are loaded.

## Development of Extensions

The extension development is an easy task, best start with the [TYPO3 Config
Handling Extension Template](https://github.com/GsTYPO3/config-handling-extension-template).

### The extension's `composer.json`

The `type` of the package is the most important part and must be set to
`typo3-config-handling-extension`. Only packages with this type will be
considered for processing. Next, a class to be used must be properly defined.
Without a class, the package will try to load the configuration from the
`config` folder using `GlobFileReader`. The extension loading order can be
changed with the `before` and `after` keys by specifying a list of package
names.

```json
{
    "name": "vendor/typo3-config-handling-extension-template",
    "description": "TYPO3 Config Handling Extension Template.",
    "license": "GPL-3.0-or-later",
    "type": "typo3-config-handling-extension",
    "require": {
        "php": "^7.4 || ^8.0",
        "gilbertsoft/typo3-config-handling-extensions": "^0.1.0"
    },
    "autoload": {
        "psr-4": {
            "Vendor\\TYPO3\\ConfigHandling\\Extension\\Template\\": "src"
        }
    },
    "extra": {
        "gilbertsoft/typo3-config-handling-extension": {
            "class": "Vendor\\TYPO3\\ConfigHandling\\Extension\\Template\\ConfigProvider",
            "force-config-dir": false,
            "before": [],
            "after": []
        }
    }
}
```

#### Keys in detail

* `class`: The class to be created to return the configuration. This class must
  implement the `Gilbertsoft\TYPO3\ConfigHandling\Extensions\ConfigProviderInterface`
  interface. Make sure that two backslashes are used according to the JSON definition.

* `force-config-dir`: If a class is set, the `config` folder is ignored by default.
  In the rare case that you want to load the configuration from the provider and
  the `config` folder, set this value to `true`.

* `before`: An array of package names, this extension is loaded before these packages.

* `after`: An array of package names, this extension is loaded after these packages.

### The configuration provider class

The provider is responsible for the return of a configuration array and is the
core of an extension. Here you implement your configuration logic, which of
course can also contain only a simple configuration array or is built
dynamically depending on some conditions.

Some more information is included in the interface definition.

#### User configurable options

An options array is read from the root `composer.json` and passed to provider
methods. This makes it possible for the user to configure the provider as
needed. An option consists of a key and a value. String, integer and boolean
options are supported.

```json
"extra": {
    "gilbertsoft/typo3-config-handling-extensions": {
        "extensions": {
            "vendor/typo3-config-handling-extension-template": {
                "options": {
                    "string-option": "string",
                    "int-option": 123,
                    "bool-option": true
                }
            }
        }
    }
}
```

The options are read during the autoload dump. After changing the root
`composer.json`, `composer dump-autoload` must be executed.

## Feedback / Bug reports / Contribution

Bug reports, feature requests and pull requests are welcome in the [GitHub
repository](https://github.com/GsTYPO3/typo3-config-handling-extensions).

For support questions or other discussions please use the [GitHub
Discussions](https://github.com/GsTYPO3/typo3-config-handling-extensions/discussions).

## License

This package is licensed under the [GNU GENERAL PUBLIC LICENSE](https://github.com/GsTYPO3/typo3-config-handling-extensions/blob/main/LICENSE.md).
