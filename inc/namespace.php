<?php

namespace EnhancedReusableBlocks;

use EnhancedReusableBlocks\Connections;
use Asset_Loader;

const BLOCK_POST_TYPE = 'wp_block';
const RELATIONSHIPS_PER_PAGE = 10;

/**
 * EnhancedReusableBlocks Bootstrap.
 */
function bootstrap() {
	Categories\bootstrap();
	Connections\bootstrap();
	REST_API\bootstrap();

	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets' );
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_block_admin_bar_menu_items', 100 );

	add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\insert_reusable_block_post_data', 10, 2 );
	// Add to allowed blocks. Running a late to ensure that any block whitelist is defined.
	add_filter( 'allowed_block_types', __NAMESPACE__ . '\\filter_allowed_block_types', 20 );
}

/**
 * Enqueue the JS and CSS for blocks in the editor.
 *
 * @return void
 */
function enqueue_block_editor_assets() {
	Asset_Loader\autoenqueue(
		plugin_dir_path( PLUGIN_FILE ) . 'build/asset-manifest.json',
		'index.js',
		[
			'handle'  => 'enhanced-reusable-blocks',
			'scripts' => [
				'wp-api-fetch',
				'wp-blocks',
				'wp-components',
				'wp-compose',
				'wp-data',
				'wp-edit-post',
				'wp-editor',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
				'wp-plugins',
				'wp-url',
			]
		]
	);

	$erb_settings = [
		'editPostUrl' => admin_url( 'post.php?post=%d&action=edit' ),
		'context' => [
			'postId'   => get_the_ID(),
			'postType' => get_post_type(),
		],
		'relationshipsPerPage' => RELATIONSHIPS_PER_PAGE,
	];

	wp_localize_script( 'enhanced-reusable-blocks', 'enhancedReusableBlocksSettings', $erb_settings );
}

/**
 * Filter the allowed block types. If an array is provided, add `erb/reusable-block` to it, otherwise return the bool value that was passed in.
 *
 * @param bool|array $allowed_block_types Array of allowed block types or bool if it has not been filtered yet.
 * @return bool|array
 */
function filter_allowed_block_types( $allowed_block_types ) {
	if ( is_array( $allowed_block_types ) ) {
		$allowed_block_types[] = 'erb/reusable-block';
	}

	return $allowed_block_types;
}

/**
 * Filter callback for `wp_insert_post_data`. Sets the post_name with the post_title for `wp_block` posts before inserting post data.
 *
 * @param array $data An array of slashed post data.
 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
 *
 * @return array Filtered array of post data.
 */
function insert_reusable_block_post_data( array $data, array $postarr ) : array {
	if ( ! isset( $data['post_type'] ) || ! isset( $data['post_title'] ) ) {
		return $data;
	}

	if ( $data['post_type'] === BLOCK_POST_TYPE ) {
		$post_id = (int) $postarr['ID'] ?? 0;

		$data['post_name'] = wp_unique_post_slug(
			sanitize_title( $data['post_title'], $post_id ),
			$post_id,
			$data['post_status'],
			BLOCK_POST_TYPE,
			$data['post_parent'] ?? 0
		);
	}

	return $data;
}

/**
 * Add Blocks to "Add New" menu.
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function add_block_admin_bar_menu_items( \WP_Admin_Bar $wp_admin_bar ) {
	$wp_admin_bar->add_menu(
		[
			'parent' => 'new-content',
			'id'     => 'new-wp_block',
			'title'  => __( 'Reusable Block', 'enhanced-reusable-blocks' ),
			'href'   => admin_url( 'post-new.php?post_type=wp_block' ),
		]
	);
}
