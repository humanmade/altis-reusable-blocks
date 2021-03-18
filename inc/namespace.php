<?php

namespace Altis\ReusableBlocks;

use Altis\ReusableBlocks\Connections;
use Asset_Loader;

const BLOCK_POST_TYPE = 'wp_block';
const RELATIONSHIPS_PER_PAGE = 10;

/**
 * Altis\ReusableBlocks Bootstrap.
 */
function bootstrap() {
	Categories\bootstrap();
	Connections\bootstrap();
	REST_API\bootstrap();

	add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\\enqueue_block_editor_assets' );
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_block_admin_bar_menu_items', 100 );
	add_action( 'admin_menu', __NAMESPACE__ . '\\admin_menu', 9 );

	add_filter( 'wp_insert_post_data', __NAMESPACE__ . '\\insert_reusable_block_post_data', 10, 2 );
	// Add to allowed blocks. Running a late to ensure that any block whitelist is defined.
	add_filter( 'allowed_block_types', __NAMESPACE__ . '\\filter_allowed_block_types', 20 );
	add_filter( 'register_post_type_args', __NAMESPACE__ . '\\show_wp_block_in_menu', 10, 2 );
}

/**
 * Enqueue the JS and CSS for blocks in the editor.
 *
 * @return void
 */
function enqueue_block_editor_assets() {
	Asset_Loader\enqueue_asset(
		plugin_dir_path( PLUGIN_FILE ) . 'build/asset-manifest.json',
		'index.js',
		[
			'handle'  => 'altis-reusable-blocks',
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

	$settings = [
		'editPostUrl' => admin_url( 'post.php?post=%d&action=edit' ),
		'context' => [
			'postId'   => get_the_ID(),
			'postType' => get_post_type(),
		],
		'relationshipsPerPage' => RELATIONSHIPS_PER_PAGE,
	];

	wp_localize_script( 'altis-reusable-blocks', 'altisReusableBlocksSettings', $settings );
}

/**
 * Filter the allowed block types. If an array is provided, add `altis/reusable-block` to it, otherwise return the bool value that was passed in.
 *
 * @param bool|array $allowed_block_types Array of allowed block types or bool if it has not been filtered yet.
 * @return bool|array
 */
function filter_allowed_block_types( $allowed_block_types ) {
	if ( is_array( $allowed_block_types ) ) {
		$allowed_block_types[] = 'altis/reusable-block';
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
 * Update the wp_block post type to display in the admin menu.
 *
 * @param array $args The post type creation args.
 * @param string $post_type The post type name.
 * @return array
 */
function show_wp_block_in_menu( array $args, string $post_type ) : array {
	if ( $post_type !== 'wp_block' ) {
		return $args;
	}

	if ( function_exists( 'wp_get_current_user' ) && ! current_user_can( 'edit_posts' ) ) {
		return $args;
	}

	$args['show_in_menu'] = true;
	$args['menu_position'] = 24;
	$args['menu_icon'] = 'dashicons-screenoptions';

	return $args;
}

/**
 * Add wp_block to main menu global var.
 *
 * Replicates wp-admin/menu.php line 103-163 without built in post type special cases.
 */
function admin_menu() {
	global $menu, $submenu, $_wp_last_object_menu;

	$ptype = 'wp_block';

	$ptype_obj = get_post_type_object( $ptype );

	// Check if it should be a submenu.
	if ( $ptype_obj->show_in_menu !== true ) {
		return false;
	}

	$ptype_menu_position = is_int( $ptype_obj->menu_position ) ? $ptype_obj->menu_position : ++$_wp_last_object_menu; // If we're to use $_wp_last_object_menu, increment it first.
	$ptype_for_id = sanitize_html_class( $ptype );

	$menu_icon = 'dashicons-admin-post';
	if ( is_string( $ptype_obj->menu_icon ) ) {
		// Special handling for data:image/svg+xml and Dashicons.
		if ( 0 === strpos( $ptype_obj->menu_icon, 'data:image/svg+xml;base64,' ) || 0 === strpos( $ptype_obj->menu_icon, 'dashicons-' ) ) {
			$menu_icon = $ptype_obj->menu_icon;
		} else {
			$menu_icon = esc_url( $ptype_obj->menu_icon );
		}
	}

	$menu_class = 'menu-top menu-icon-' . $ptype_for_id;

	$ptype_file = "edit.php?post_type=$ptype";
	$post_new_file = "post-new.php?post_type=$ptype";
	$edit_tags_file = "edit-tags.php?taxonomy=%s&amp;post_type=$ptype";

	$ptype_menu_id = 'menu-posts-' . $ptype_for_id;

	/*
		* If $ptype_menu_position is already populated or will be populated
		* by a hard-coded value below, increment the position.
		*/
	$core_menu_positions = [ 59, 60, 65, 70, 75, 80, 85, 99 ];
	while ( isset( $menu[ $ptype_menu_position ] ) || in_array( $ptype_menu_position, $core_menu_positions ) ) {
		$ptype_menu_position++;
	}

	// Disable globals sniff as it is safe to add to the menu and submenu globals.
	// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
	$menu[ $ptype_menu_position ] = [ esc_attr( $ptype_obj->labels->menu_name ), $ptype_obj->cap->edit_posts, $ptype_file, '', $menu_class, $ptype_menu_id, $menu_icon ];
	$submenu[ $ptype_file ][5] = [ $ptype_obj->labels->all_items, $ptype_obj->cap->edit_posts, $ptype_file ];
	$submenu[ $ptype_file ][10] = [ $ptype_obj->labels->add_new, $ptype_obj->cap->create_posts, $post_new_file ];

	$i = 15;
	foreach ( get_taxonomies( [], 'objects' ) as $tax ) {
		if ( ! $tax->show_ui || ! $tax->show_in_menu || ! in_array( $ptype, (array) $tax->object_type, true ) ) {
			continue;
		}

		$submenu[ $ptype_file ][ $i++ ] = [ esc_attr( $tax->labels->menu_name ), $tax->cap->manage_terms, sprintf( $edit_tags_file, $tax->name ) ];
	}
	// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
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
			'title'  => __( 'Reusable Block', 'altis-reusable-blocks' ),
			'href'   => admin_url( 'post-new.php?post_type=wp_block' ),
		]
	);
}
