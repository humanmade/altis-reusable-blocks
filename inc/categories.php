<?php

namespace EnhancedReusableBlocks\Categories;

/**
 * Bootstrap it up!
 */
function bootstrap() {
	add_action( 'init', __NAMESPACE__ . '\\register_block_categories' );
}

/**
 * Create the block categories taxonomy.
 */
function register_block_categories() {
	register_taxonomy( 'wp_block_category', 'wp_block', [
		'label' => __( 'Block Categories', 'enhanced-reusable-blocks' ),
		'labels' => [
			'name'                       => _x( 'Block Categories', 'taxonomy general name', 'enhanced-reusable-blocks' ),
			'singular_name'              => _x( 'Block Category', 'taxonomy singular name', 'enhanced-reusable-blocks' ),
			'search_items'               => __( 'Search Block Categories', 'enhanced-reusable-blocks' ),
			'popular_items'              => __( 'Popular Block Categories', 'enhanced-reusable-blocks' ),
			'all_items'                  => __( 'All Block Categories', 'enhanced-reusable-blocks' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Block Category', 'enhanced-reusable-blocks' ),
			'update_item'                => __( 'Update Block Category', 'enhanced-reusable-blocks' ),
			'add_new_item'               => __( 'Add New Block Category', 'enhanced-reusable-blocks' ),
			'new_item_name'              => __( 'New Block Category Name', 'enhanced-reusable-blocks' ),
			'separate_items_with_commas' => __( 'Separate block categories with commas', 'enhanced-reusable-blocks' ),
			'add_or_remove_items'        => __( 'Add or remove block categories', 'enhanced-reusable-blocks' ),
			'choose_from_most_used'      => __( 'Choose from the most used block categories', 'enhanced-reusable-blocks' ),
			'not_found'                  => __( 'No block categories found.', 'enhanced-reusable-blocks' ),
			'menu_name'                  => __( 'Categories', 'enhanced-reusable-blocks' ),
		],
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_in_rest' => true,
		'hierarchical' => true,
		'show_admin_column' => true,
		'rewrite' => false,
	] );
}
