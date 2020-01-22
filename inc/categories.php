<?php

namespace EnhancedReusableBlocks\Categories;

/**
 * Bootstrap it up!
 */
function bootstrap() {
	add_action( 'init', __NAMESPACE__ . '\\add_categories_taxonomy_to_blocks' );
}

/**
 * Add categories taxonomy to block post type.
 */
function add_categories_taxonomy_to_blocks() {
	return register_taxonomy_for_object_type( 'category', 'wp_block' );
}
