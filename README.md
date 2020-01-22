# Altis Enhanced Reusable Blocks

> Adds functionality to reusable blocks to ease their usage in the Altis environment.

![<img src="https://github.com/kevinlangleyjr/enhanced-reusable-blocks/workflows/CI%20Build/badge.svg" />](https://github.com/kevinlangleyjr/enhanced-reusable-blocks/actions?query=workflow%3A%22CI+Build%22)

----

## Introduction

Enhanced Reusable Blocks provides enterprise workflows and added functionality for reusable blocks.

## Table of Contents

* [Installation](#installation)
  * [Build Process](#build-process)
  * [Requirements](#requirements)
  * [Tests](#tests)
    * [PHP Tests](#php-tests)
* [Usage](#usage)
  * [PHP Filters](#php-filters)
    * [`altis_post_types_with_reusable_blocks`](#altis_post_types_with_reusable_blocks)
    * [`rest_get_relationship_item_additional_fields_schema`](#rest_get_relationship_item_additional_fields_schema)
    * [`rest_prepare_relationships_response`](#rest_prepare_relationships_response)
* [Release Process](#release-process)
  * [Using the `deploy` Script](#using-the-deploy-script)
  * [The Build-for-Deploy Process in Detail](#the-build-for-deploy-process-in-detail)
  * [Versioning](#versioning)
  * [Publishing a Release](#publishing-a-release)

## Installation

Install with [Composer](https://getcomposer.org):

```sh
composer require humanmade/enhanced-reusable-blocks
```

### Build Process

Create a **production** build:

```sh
yarn build
```

Start the interactive **development** server:

```sh
yarn start
```

### Requirements

This plugin requires PHP 7.1 or higher.

### Tests

### PHP Tests

The PHP tests live in the `tests` folder, with subfolders for each individual test level.
Currently, this means unit tests, living in `tests/unit`.

Run the PHP unit tests:

```sh
composer test:unit
```

Under the hood, this is using `PHPUnit`, as specified in the `composer.json` file.
Any arguments passed to the script will then be passed on to the `phpunit` cli, meaning you can target specific files/names, like so:

```sh
composer test:unit -- --filter logging
```

## Usage

### PHP Filters

#### `rest_get_relationship_item_additional_fields_schema`

This filter allows the user to modify the schema for the relationship data before it is returned from the REST API.

**Arguments:**

* `$schema` (`array`): Item schema data.

**Usage Example:**

```php
// Add the post author to the schema.
add_filter( 'rest_get_relationship_item_additional_fields_schema', function ( array $additional_fields ): array {

	$additional_fields['author'] = [
		'description' => __( 'User ID for the author of the post.' ),
		'type'        => 'integer',
		'context'     => [ 'view' ],
		'readonly'    => true,
	];

	return $additional_fields;
} );
```

----

#### `altis_post_types_with_reusable_blocks`

This filter allows the user to manipulate the post types that can use reusable blocks and should have the relationship for the shadow taxonomy.

**Arguments:**

* `$post_types` (`string[]`): List of post type slugs.

**Usage Example:**

```php
// Add the "page" post type.
add_filter( 'altis_post_types_with_reusable_blocks', function ( aray $post_types ): array {

	$post_types[] = 'page';

	return $post_types;
} );
```

----

#### `rest_prepare_relationships_response`

This filter allows the user to modify the relationship data right before it is returned from the REST API.

**Arguments:**

* `$response` (`WP_REST_Response`): Response object.
* `$post` (`WP_Post`): Post object.
* `$request` (`WP_REST_Request`): Request object.

**Usage Example:**

```php
// Add the post author to the REST response.
add_filter( 'rest_prepare_relationships_response', function ( WP_REST_Response $response, WP_Post $post ): WP_REST_Response {

	$response->data['author'] = $post->post_author;

	return $response;
}, 10, 2 );
```

### Versioning

This plugin follows [Semantic Versioning](https://semver.org/).

In a nutshell, this means that **patch releases**, for example, 1.2.3, only contain **backwards compatible bug fixes**.
**Minor releases**, for example, 1.2.0, may contain **enhancements, new features, tests**, and pretty much everything that **does not break backwards compatibility**.
Every **breaking change** to public APIs—be it renaming or even deleting structures intended for reuse, or making backwards-incompatible changes to certain functionality—warrants a **major release**, for example, 2.0.0.

If you are using Composer to pull this plugin into your website build, choose your version constraint accordingly.

### Publishing a Release

Release management is done using GitHub's built-in Releases functionality.
Each release is tagged using the according version number, for example, the version 1.2.3 of this plugin woul have the tag name `v1.2.3`.
Releases can be created off the `master` branch, which is true for scheduled releases, or from a new banch based off the previous tag.

For better information management, every release should come with complete, but high-level Release Notes, detailing all _New Features_, _Enhancements_, _Bug Fixes_ and potential other changes included in the according version.
