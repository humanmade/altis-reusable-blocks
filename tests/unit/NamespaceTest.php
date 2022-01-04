<?php

namespace Altis\ReusableBlocks\Tests\Unit;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks as Testee;
use const Altis\ReusableBlocks\Connections\POST_POST_TYPE;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class NamespaceTest extends TestCase {

	/**
	 * Tests `bootstrap` function to ensure all actions and filters are added correctly.
	 *
	 * @return void
	 */
	public function test_bootstrap() {

		Actions\expectAdded( 'enqueue_block_editor_assets' )
			->with( 'Altis\ReusableBlocks\enqueue_block_editor_assets' );

		Actions\expectAdded( 'admin_bar_menu' )
			->with( 'Altis\ReusableBlocks\add_block_admin_bar_menu_items', 100 );

		Actions\expectAdded( 'admin_menu' )
			->with( 'Altis\ReusableBlocks\admin_menu', 9 );

		Filters\expectAdded( 'wp_insert_post_data' )
			->with( 'Altis\ReusableBlocks\insert_reusable_block_post_data', 10, 2 );

		Filters\expectAdded( 'register_post_type_args' )
			->with( 'Altis\ReusableBlocks\show_wp_block_in_menu', 10, 2 );

		Filters\expectAdded( 'allowed_block_types_all' )
			->with( 'Altis\ReusableBlocks\filter_allowed_block_types', 20 );

		Testee\bootstrap();
	}

	public function test_enqueue_block_editor_assets() {
		Functions\expect( 'plugin_dir_path' )
			->with(
				Testee\PLUGIN_FILE
			)
			->andReturn( 'filepath/' );

		Functions\expect( 'Asset_Loader\enqueue_asset' )
			->with(
				'filepath/build/asset-manifest.json',
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

		Functions\expect( 'admin_url' )
			->with( 'post.php?post=%d&action=edit' )
			->andReturn( 'http://altis.local/wp-admin/post.php?post=%d&action=edit' );

		Functions\expect( 'get_the_ID' )
			->andReturn( 1 );

		Functions\expect( 'get_post_type' )
			->andReturn( 'wp_block' );

		Functions\expect( 'wp_localize_script' )
			->with(
				'altis-reusable-blocks',
				'altisReusableBlocksSettings',
				[
					'editPostUrl' => 'http://altis.local/wp-admin/post.php?post=%d&action=edit',
					'context' => [
						'postId'   => 1,
						'postType' => 'wp_block',
					],
					'relationshipsPerPage' => Testee\RELATIONSHIPS_PER_PAGE,
				]
			 )
			->andReturn( true );

		Testee\enqueue_block_editor_assets();
	}

	/**
	 * Tests `filter_allowed_block_types` to ensure we're enabling the block type successfully when a bool is passed in.
	 *
	 * @return void
	 */
	public function test_filter_allowed_block_types_bool() {
		$this->assertTrue( Testee\filter_allowed_block_types( true ) );
	}

	/**
	 * Tests `filter_allowed_block_types` to ensure we're enabling the block type successfully when an array is passed in.
	 *
	 * @return void
	 */
	public function test_filter_allowed_block_types_array() {
		$this->assertSame( Testee\filter_allowed_block_types( [] ), [ 'altis/reusable-block' ] );
	}

	/**
	 * Tests `insert_reusable_block_post_data` with an invalid post type.
	 *
	 * @return void
	 */
	public function test_insert_reusable_block_post_data_invalid_post_type() {
		$data = [
			'post_type'  => POST_POST_TYPE,
			'post_title' => 'Test Block Title'
		];

		$new_data = Testee\insert_reusable_block_post_data( $data, [ 'ID' => 1 ] );

		$this->assertSame( $data, $new_data );
	}

	/**
	 * Tests `insert_reusable_block_post_data` with invalid post data (type or title missing).
	 *
	 * @return void
	 */
	public function test_insert_reusable_block_post_data_invalid_post_data() {
		$data = [
			'post_status' => 'auto-draft'
		];

		$new_data = Testee\insert_reusable_block_post_data( $data, [] );

		$this->assertSame( $data, $new_data );
	}

	/**
	 * Tests `insert_reusable_block_post_data` with the valid post type.
	 *
	 * @return void
	 */
	public function test_insert_reusable_block_post_data_valid_data() {
		$data = [
			'post_type'  => Testee\BLOCK_POST_TYPE,
			'post_title' => 'Test Block Title',
			'post_status' => 'publish'
		];

		$post_name = 'test-block-title';

		Functions\expect( 'sanitize_title' )
			->with( $data['post_title'] )
			->andReturn( $post_name );

		Functions\expect( 'wp_unique_post_slug' )
			->with(
				$post_name,
				1,
				'publish',
				Testee\BLOCK_POST_TYPE,
				0
			 )
			->andReturn( $post_name );

		$new_data = Testee\insert_reusable_block_post_data( $data, [ 'ID' => 1 ] );

		$this->assertSame( $post_name, $new_data['post_name'] );
	}

	/**
	 * Tests `add_block_admin_bar_menu_item`.
	 *
	 * @return void
	 */
	public function test_add_block_admin_bar_menu_items() {
		Functions\stubTranslationFunctions();

		$new_post_url = 'https://altis.local/wordpress/wp-admin/post-new.php?post_type=wp_block';

		Functions\expect( 'admin_url' )
			->with( 'post-new.php?post_type=wp_block' )
			->andReturn( $new_post_url );


		$admin_bar = \Mockery::mock( \WP_Admin_Bar::class );
		$admin_bar->shouldReceive( 'add_menu' )
			->with( [
				'parent' => 'new-content',
				'id'     => 'new-wp_block',
				'title'  => 'Reusable Block',
				'href'   => $new_post_url,
			] )
			->once();

		Testee\add_block_admin_bar_menu_items( $admin_bar );
	}

	/**
	 * Test `show_wp_block_in_menu` if invalid post type is being filtered.
	 *
	 * @return void
	 */
	public function test_show_wp_block_in_menu_invalid_post_type() {
		Functions\stubTranslationFunctions();

		$this->assertSame( [], Testee\show_wp_block_in_menu( [], 'post' ) );
	}

	/**
	 * Test `show_wp_block_in_menu` if correct args are returned.
	 *
	 * @return void
	 */
	public function test_show_wp_block_in_menu() {
		Functions\stubTranslationFunctions();

		$args = [];
		$expected_args = [
			'show_in_menu' => true,
			'menu_position' => 24,
			'menu_icon' => 'dashicons-screenoptions',
		];
		
		$actual_args = Testee\show_wp_block_in_menu( $args, 'wp_block' );
		
		foreach ( $expected_args as $key => $value ) {
			$this->assertArrayHasKey( $key, $actual_args );
			$this->assertSame( $value, $actual_args[ $key ] );
		}
	}

	/**
	 * Test `show_wp_block_in_menu` if user has no capability to manage Blocks.
	 *
	 * @return void
	 */
	public function test_show_wp_block_in_menu_no_caps() {
		Functions\expect( 'wp_get_current_user' )
			->never();

		Functions\expect( 'current_user_can' )
			->with( 'edit_posts' )
			->andReturn( false );

		$this->assertSame( [], Testee\show_wp_block_in_menu( [], 'wp_block' ) );
	}

	/**
	 * Tests `admin_menu` if `show_in_menu` was filtered to be false.
	 *
	 * @return void
	 */
	public function test_admin_menu_show_in_menu_false() {
		$post_type_obj = (object) [
			'show_in_menu' => false,
		];

		Functions\expect( 'get_post_type_object' )
			->with( 'wp_block' )
			->andReturn( $post_type_obj );

		$this->assertFalse( Testee\admin_menu() );
	}

	/**
	 * Tests `admin_menu` additions with Blocks menu.
	 *
	 * @return void
	 */
	public function test_admin_menu() {
		global $menu, $submenu, $_wp_last_object_menu;

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$menu = [];

		$submenu = [];

		$_wp_last_object_menu = 19;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		$post_type_obj = (object) [
			'cap'          => (object) [
				'edit_posts'   => 'edit_wp_block',
				'create_posts' => 'create_wp_block',
			],
			'labels'       => (object) [
				'menu_name'    => 'Blocks',
				'add_new'      => 'Add New Block',
				'all_items'    => 'All Blocks',
			],
			'show_in_menu' => true,
			'menu_position' => 20,
			'menu_icon' => 'dashicons-fake-icon',
		];

		$fake_taxonomy_obj = (object) [ 'show_ui' => false ];

		$cat_taxonomy_obj = (object) [
			'cap'          => (object) [
				'manage_terms' => 'manage_category',
			],
			'labels'       => (object) [
				'menu_name'    => 'Categories',
			],
			'name'         => 'category',
			'object_type'  => [ 'post', 'wp_block' ],
			'show_ui'      => true,
			'show_in_menu' => true,
		];

		Functions\stubEscapeFunctions();

		Functions\expect( 'get_post_type_object' )
			->with( 'wp_block' )
			->andReturn( $post_type_obj );

		Functions\expect( 'sanitize_html_class' )
			->with( 'wp_block' )
			->andReturn( 'wp_block' );

		Functions\expect( 'get_taxonomies' )
			->with( [], 'objects' )
			->andReturn( [
				$cat_taxonomy_obj,
				$fake_taxonomy_obj,
			] );


		Testee\admin_menu();

		$expected_menu = [
			20 => [
				'Blocks',
				'edit_wp_block',
				'edit.php?post_type=wp_block',
				'',
				'menu-top menu-icon-wp_block',
				'menu-posts-wp_block',
				'dashicons-fake-icon',
			]
		];

		$expected_submenu = [
			'edit.php?post_type=wp_block' => [
				5 => [
					0 => 'All Blocks',
					1 => 'edit_wp_block',
					2 => 'edit.php?post_type=wp_block',
				],
				10 => [
					0 => 'Add New Block',
					1 => 'create_wp_block',
					2 => 'post-new.php?post_type=wp_block',
				],
				15 => [
					0 => 'Categories',
					1 => 'manage_category',
					2 => 'edit-tags.php?taxonomy=category&amp;post_type=wp_block',
				],
			],
		];

		$this->assertSame( $expected_menu, $menu );
		$this->assertSame( $expected_submenu, $submenu );
	}

	/**
	 * Tests `admin_menu` additions with Blocks menu.
	 *
	 * @return void
	 */
	public function test_admin_menu_custom_icon() {
		global $menu, $submenu, $_wp_last_object_menu;

		// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
		$menu = [];

		$submenu = [];

		$_wp_last_object_menu = 19;
		// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

		$post_type_obj = (object) [
			'cap'          => (object) [
				'edit_posts'   => 'edit_wp_block',
				'create_posts' => 'create_wp_block',
			],
			'labels'       => (object) [
				'menu_name'    => 'Blocks',
				'add_new'      => 'Add New Block',
				'all_items'    => 'All Blocks',
			],
			'show_in_menu' => true,
			'menu_position' => 59,
			'menu_icon' => 'https://altis.local/icon.png',
		];

		$fake_taxonomy_obj = (object) [ 'show_ui' => false ];

		$cat_taxonomy_obj = (object) [
			'cap'          => (object) [
				'manage_terms' => 'manage_category',
			],
			'labels'       => (object) [
				'menu_name'    => 'Categories',
			],
			'name'         => 'category',
			'object_type'  => [ 'post', 'wp_block' ],
			'show_ui'      => true,
			'show_in_menu' => true,
		];

		Functions\stubEscapeFunctions();

		Functions\expect( 'get_post_type_object' )
			->with( 'wp_block' )
			->andReturn( $post_type_obj );

		Functions\expect( 'sanitize_html_class' )
			->with( 'wp_block' )
			->andReturn( 'wp_block' );

		Functions\expect( 'get_taxonomies' )
			->with( [], 'objects' )
			->andReturn( [
				$cat_taxonomy_obj,
				$fake_taxonomy_obj,
			] );


		Testee\admin_menu();

		$expected_menu = [
			61 => [
				'Blocks',
				'edit_wp_block',
				'edit.php?post_type=wp_block',
				'',
				'menu-top menu-icon-wp_block',
				'menu-posts-wp_block',
				'https://altis.local/icon.png',
			]
		];

		$expected_submenu = [
			'edit.php?post_type=wp_block' => [
				5 => [
					0 => 'All Blocks',
					1 => 'edit_wp_block',
					2 => 'edit.php?post_type=wp_block',
				],
				10 => [
					0 => 'Add New Block',
					1 => 'create_wp_block',
					2 => 'post-new.php?post_type=wp_block',
				],
				15 => [
					0 => 'Categories',
					1 => 'manage_category',
					2 => 'edit-tags.php?taxonomy=category&amp;post_type=wp_block',
				],
			],
		];

		$this->assertSame( $expected_menu, $menu );
		$this->assertSame( $expected_submenu, $submenu );
	}

}
