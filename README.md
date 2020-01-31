# Altis Reusable Blocks

Altis Reusable Blocks provides enterprise workflows and added functionality for reusable blocks.

The main goals of Altis Reusable Blocks are to:

* provide a much more seamless implementation of reusable blocks into enterprise-level setups and workflows.
* provide an improved user interface that allows for better block discovery, including search and filtering.

![](https://github.com/humanmade/altis-reusable-blocks/workflows/CI%20Check/badge.svg)
![](https://img.shields.io/github/v/release/humanmade/altis-reusable-blocks)

----

## Table of Contents

* [Features](#features)
  * [Relationship and usage tracking](#relationship-and-usage-tracking)
  * [Admin Bar and Menu](#admin-bar-and-menu)
  * [Categories](#categories)
  * [Filtering](#filtering)
  * [Search](#search)
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
  * [Versioning](#versioning)
  * [Publishing a Release](#publishing-a-release)

----

## Features

Altis Reusable Blocks includes new features and improvements both for the creation and the discovery/usage of reusable blocks.

#### Relationship and usage tracking

Keep track of all usages of reusable blocks within your posts. Within the edit screen for your reusable blocks, you will find the Relationships sidebar with a paginated view of all the posts that are using the reusable block that you are currently editing.

On the reusable blocks post list table, you can see at a quick glance the usage count for that reusable block.

#### Admin Bar and Menu

By default, reusable blocks are somewhat hidden and can only be accessed from a submenu item in the block editor.
With Altis Reusable Blocks, however, reusable blocks are upgraded to first-party citizens in the admin area.

Like for every other content type, the admin menu on the left now contains a dedicated submenu for reusable blocks, offering shortcuts to see all existing reusable blocks, to create a new reusable block, and to see and manage categories, as well as any other publicly available taxonomy registered for reusable blocks.
Also, the admin bar at the top now contains a shortcut to create a new reusable block, just like it is possible to do for posts, media, pages or users.

#### Categories

Just like posts or pages, reusable blocks can have one or more categories assigned to them.
This helps in discovering relevant blocks by making use of the dedicated Category filter included in the block picker.

#### Filtering

When looking for an existing reusable block to insert into a post, the new block picker allows to search/filter based on a category.

By default, the Category filter is set to the (main) category of the current post.
However, this can be changed, without affecting the post's categories.

#### Search

In addition to the Category filter, the block picker also provides a search field.
The search query is used to find reusable blocks with either a matching title or content, or both.
Search results are sorted based on a smart algorithm using different weights for title matches vs. content matches, and exact matches vs. partial matches.
As a result, more relevant blocks are displayed first.

The search input also supports numeric ID lookups.
By entering a block ID, the result set will be just that one according block, ready to be inserted.
If the provided ID is a post ID, the results will be all reusable blocks referenced by that post, if any.

----

## Installation

Install with [Composer](https://getcomposer.org):

```sh
composer require humanmade/altis-reusable-blocks
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

----

## Usage

### PHP Filters

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

----

## Release Process

### Versioning

This plugin follows [Semantic Versioning](https://semver.org/).

In a nutshell, this means that **patch releases**, for example, 1.2.3, only contain **backwards compatible bug fixes**.
**Minor releases**, for example, 1.2.0, may contain **enhancements, new features, tests**, and pretty much everything that **does not break backwards compatibility**.
Every **breaking change** to public APIs—be it renaming or even deleting structures intended for reuse, or making backwards-incompatible changes to certain functionality—warrants a **major release**, for example, 2.0.0.

If you are using Composer to pull this plugin into your website build, choose your version constraint accordingly.

### Publishing a Release

Release management is done using GitHub's built-in Releases functionality.
Each release is tagged using the according version number, for example, the version 1.2.3 of this plugin would have the tag name `v1.2.3`.
Releases should be created off the `master` branch, and tagged in the correct format.
When a release is tagged in the correct format of `v*.*.*`, the GitHub actions release workflow creates a new built release based on the original release you just created.
It will copy the tag's current state to a new tag of `original/v.*.*.*` and then build the project and push the built version to the original tag name `v*.*.*`.
This allows composer to pull in a built version of the project without the need to run webpack to use it.

For better information management, every release should come with complete, but high-level Release Notes, detailing all _New Features_, _Enhancements_, _Bug Fixes_ and potential other changes included in the according version.
