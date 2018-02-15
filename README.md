[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/wp-global-options.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-options/)
[![WordPress](https://img.shields.io/wordpress/v/wp-global-options.svg?maxAge=2592000)](https://wordpress.org/plugins/wp-global-options/)
[![Build Status](https://api.travis-ci.org/felixarntz/wp-global-options.png?branch=master)](https://travis-ci.org/felixarntz/wp-global-options)
[![Latest Stable Version](https://poser.pugx.org/felixarntz/wp-global-options/version)](https://packagist.org/packages/felixarntz/wp-global-options)
[![License](https://poser.pugx.org/felixarntz/wp-global-options/license)](https://packagist.org/packages/felixarntz/wp-global-options)

# WP Global Options

Implements a global option storage in WordPress.

## What it does

* introduces a database table `global_options`
* introduces a CRUD API for global options, including sanitization and validation
* introduces a CRUD API for global transients
* introduces an API for registering/unregistering global settings
* includes a `wp global-options` command for WP-CLI

## How to install

The plugin can either be installed as a network-wide regular plugin or alternatively as a must-use plugin.

## Recommendations

* While it is a best practice to prefix plugin functions and classes, this plugin is a proof-of-concept for WordPress core, and several functions may end up there eventually. This plugin only prefixes functions and classes that are specific to the plugin, internal helper functions for itself or hooks. Non-prefixed functions and classes are wrapped in a conditional so that, if WordPress core adapts them, their core variant will be loaded instead. Therefore, do not define any of the following functions or classes:
  * `sanitize_global_option()`
  * `get_global_option()`
  * `update_global_option()`
  * `add_global_option()`
  * `delete_global_option()`
  * `wp_load_global_alloptions()`
  * `get_global_transient()`
  * `set_global_transient()`
  * `delete_global_transient()`
  * `register_global_setting()`
  * `unregister_global_setting()`
  * `get_registered_global_settings()`
  * `filter_default_global_option()`

## Usage

### Managing Global Options

* Function: `get_global_option( string $option, mixed $default = false ): mixed`
* Function: `update_global_option( string $option, mixed $value, string|bool $autoload = null ): bool`
* Function: `add_global_option( string $option, mixed $value, string|bool $autoload = 'no' ): bool`
* Function: `delete_global_option( string $option ): bool`

### Managing Global Transients

* Function: `get_global_transient( string $transient ): mixed`
* Function: `set_global_transient( string $transient, mixed $value, int $expiration = 0 ): bool`
* Function: `delete_global_transient( string $transient ): bool`

### Managing Global Settings

* Function: `register_global_setting( string $option_group, string $option_name, array $args = array() )`
* Function: `unregister_global_setting( string $option_group, string $option_name )`
* Function: `get_registered_global_settings()`

### Hooks

* Filter: `sanitize_global_option_{$option}`
* Filter: `validate_global_option_{$option}`
* Filter: `pre_global_option_{$option}`
* Filter: `default_global_option_{$option}`
* Filter: `global_option_{$option}`
* Filter: `pre_update_global_option_{$option}`
* Filter: `pre_update_global_option`
* Filter: `pre_global_transient_{$transient}`
* Filter: `global_transient_{$transient}`
* Filter: `pre_set_global_transient_{$transient}`
* Filter: `expiration_of_global_transient_{$transient}`
* Action: `update_global_option`
* Action: `update_global_option_{$option}`
* Action: `updated_global_option`
* Action: `add_global_option`
* Action: `add_global_option_{$option}`
* Action: `added_global_option`
* Action: `pre_delete_global_option_{$option}`
* Action: `delete_global_option_{$option}`
* Action: `deleted_global_option`
* Action: `set_global_transient_{$transient}`
* Action: `setted_global_transient`
* Action: `delete_global_transient_{$transient}`
* Action: `deleted_global_transient`
