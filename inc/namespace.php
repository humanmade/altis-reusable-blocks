<?php

namespace EnhancedReusableBlocks;

use EnhancedReusableBlocks\Connections;
use Asset_Loader;

const RELATIONSHIPS_PER_PAGE = 10;

/**
 * EnhancedReusableBlocks Bootstrap.
 */
function bootstrap() {
	Categories\bootstrap();
	Connections\bootstrap();
	REST_API\bootstrap();

	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets' );
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
