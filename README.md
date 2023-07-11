# fotobank/strauss

A tool to prefix namespaces and classnames in PHP files to avoid autoloading collisions.

A fork of [Strauss](https://github.com/BrianHenryIE/strauss) for [Composer](https://getcomposer.org/) for PHP.

The primary use case is WordPress plugins, where different plugins active in a single WordPress install could each include different versions of the same library. The version of the class loaded would be whichever plugin registered the autoloader first, and all subsequent instantiations of the class will use that version, with potentially unpredictable behaviour and missing functionality.    


## Breaking Changes

* v0.14.1 – fixed bugs with file paths in Windows, fixes according to the master branch BrianHenryIE/strauss
* v0.14.0 – `psr/*` packages no longer excluded by default
* v0.12.0 – default output `target_directory` changes from `strauss` to `vendor-prefixed`

## Use

Require as normal with Composer:

`composer require --dev fotobank/strauss`

and use `vendor/bin/strauss` to execute.

Or, download `strauss.phar` from [releases](https://github.com/fotobank/strauss/releases/),

```shell
curl -o strauss.phar -L -C - https://github.com/fotobank/strauss/releases/download/0.14.1/strauss.phar
```

Then run it from the root of your project folder using `php strauss.phar`.

Its use should be automated in Composer scripts.


```json
"scripts": {
    "strauss": [
        "vendor/bin/strauss"
    ],
    "post-install-cmd": [
        "@strauss"
    ],
    "post-update-cmd": [
        "@strauss"
    ]
}
```
or

```json
"scripts": {
    "strauss": [
        "@php strauss.phar"
    ]
}
```

## Configuration

Strauss potentially requires zero configuration, but likely you'll want to customize a little, by adding in your `composer.json` an `extra/strauss` object. The following is the default config, where the `namespace_prefix` and `classmap_prefix` are determined from your `composer.json`'s `autoload` or `name` key and `packages` is determined from the `require` key:

```json
"extra": {
    "strauss": {
        "target_directory": "vendor-prefixed",
        "namespace_prefix": "AlexSoft\\My_Project\\",
        "classmap_prefix": "AlexSoft_My_Project_",
        "constant_prefix": "ASMP_",
        "packages": [
        ],
        "override_autoload": {
        },
        "exclude_from_copy": {
            "packages": [
            ],
            "namespaces": [
            ],
            "file_patterns": [
            ]
        },
        "exclude_from_prefix": {
            "packages": [
            ],
            "namespaces": [
            ],
            "file_patterns": [
                "/^psr.*$/"
            ]
        },
        "namespace_replacement_patterns" : {
        },
        "delete_vendor_packages": false
        "delete_vendor_files": false,
    }
},
```

The following configuration is inferred:

- `target_directory` defines the directory the files will be copied to, default `vendor-prefixed`
- `namespace_prefix` defines the default string to prefix each namespace with
- `classmap_prefix` defines the default string to prefix class names in the global namespace
- `packages` is the list of packages to process. If absent, all packages in the `require` key of your `composer.json` are included
- `classmap_output` is a `bool` to decide if Strauss will create `autoload-classmap.php` and `autoload.php`. If it is not set, it is `false` if `target_directory` is in your project's `autoload` key, `true` otherwise.

The following configuration is default:

- `delete_vendor_packages`: `false` a boolean flag to indicate if the packages' vendor directories should be deleted after being processed. It defaults to false, so any destructive change is opt-in.
- `delete_vendor_files`: `false` a boolean flag to indicate if files copied from the packages' vendor directories should be deleted after being processed. It defaults to false, so any destructive change is opt-in. This is maybe deprecated! Is there any use to this that is more appropriate than `delete_vendor_packages`? 
- `exclude_from_prefix` / [`file_patterns`](https://github.com/fotobank/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/ChangeEnumerator.php#L92-L96)
- `include_modified_date` is a `bool` to decide if Strauss should include a date in the (phpdoc) header written to modified files. Defaults to `true`.
- `include_author` is a `bool` to decide if Strauss should include the author name in the (phpdoc) header written to modified files. Defaults to `true`.

The rest of the configuration:

- `constant_prefix` is for `define( "A_CONSTANT", value );` -> `define( "MY_PREFIX_A_CONSTANT", value );`. If it is empty, constants are not prefixed (this may change to an inferred value).
- `override_autoload` a dictionary, keyed with the package names, of autoload settings to replace those in the original packages' `composer.json` `autoload` property.
- `exclude_from_copy` 
  - [`packages`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/FileEnumerator.php#L77-L79) array of package names to be skipped
  - [`namespaces`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/FileEnumerator.php#L95-L97) array of namespaces to skip (exact match from the package autoload keys)
  - [`file_patterns`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/FileEnumerator.php#L133-L137) array of regex patterns to check filenames against (including vendor relative path) where Strauss will skip that file if there is a match
- `exclude_from_prefix`
  - [`packages`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/ChangeEnumerator.php#L86-L90) array of package names to exclude from prefixing.
  - [`namespaces`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/ChangeEnumerator.php#L177-L181) array of exact match namespaces to exclude (i.e. not substring/parent namespaces)
- [`namespace_replacement_patterns`](https://github.com/BrianHenryIE/strauss/blob/83484b79cfaa399bba55af0bf4569c24d6eb169d/src/ChangeEnumerator.php#L183-L190) a dictionary to use in `preg_replace` instead of prefixing with `namespace_prefix`.

## Autoloading

Strauss uses Composer's own tools to generate a classmap file in the `target_directory` and creates an `autoload.php` alongside it, so in many projects autoloading is just a matter of: 

```php
require_once __DIR__ . '/vendor-prefixed/autoload.php';
```

If you prefer to use Composer's autoloader, add your `target_directory` (default `vendor-prefixed`) to your `autoload` `classmap` and Strauss will not create its own `autoload.php` when run. Then run `composer dump-autoload` to include the newly copied and prefixed files in Composer's own classmap.

```
"autoload": {
    "classmap": [
        "vendor-prefixed/"
    ]
},
```

## Motivation & Comparison to Mozart


Benefits over Mozart:

* A single output directory whose structure matches source vendor directory structure (conceptually easier than Mozart's independent `classmap_directory` and `dep_directory`)
* A generated `autoload.php` to `include` in your project (analogous to Composer's `vendor/autoload.php`)  
* Handles `files` autoloaders – and any autoloaders that Composer itself recognises, since Strauss uses Composer's own tooling to parse the packages
* Zero configuration – Strauss infers sensible defaults from your `composer.json`
* No destructive defaults – `delete_vendor_files` defaults to `false`, so any destruction is explicitly opt-in
* Licence files are included and PHP file headers are edited to adhere to licence requirements around modifications. My understanding is that re-distributing code that Mozart has handled is non-compliant with most open source licences – illegal!
* Extensively tested – PhpUnit tests have been written to validate that many of Mozart's bugs are not present in Strauss
* More configuration options – allowing exclusions in copying and editing files, and allowing specific/multiple namespace renaming
* Respects `composer.json` `vendor-dir` configuration
* Prefixes constants (`define`)
* Handles meta-packages and virtual-packages

Strauss will read the Mozart configuration from your `composer.json` to enable a seamless migration.


## Acknowledgements

[Coen Jacobs](https://github.com/coenjacobs/), [Brian Henry](https://github.com/BrianHenryIE/strauss) and all the [contributors to Mozart](https://github.com/coenjacobs/mozart/graphs/contributors), particularly those who wrote nice issues.
