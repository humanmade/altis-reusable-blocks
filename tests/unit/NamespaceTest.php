<?php

namespace EnhancedReusableBlocks\Tests\Unit;

use EnhancedReusableBlocks\Tests\Unit\TestCase;
use EnhancedReusableBlocks as Testee;
use const EnhancedReusableBlocks\Connections\POST_POST_TYPE;

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
			->with( 'EnhancedReusableBlocks\enqueue_block_editor_assets' );

		Actions\expectAdded( 'admin_bar_menu' )
			->with( 'EnhancedReusableBlocks\add_block_admin_bar_menu_items', 100 );

		Filters\expectAdded( 'wp_insert_post_data' )
		 	->with( 'EnhancedReusableBlocks\insert_reusable_block_post_data', 10, 2 );

		Filters\expectAdded( 'allowed_block_types' )
			 ->with( 'EnhancedReusableBlocks\filter_allowed_block_types', 20 );

		Testee\bootstrap();
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
		$this->assertSame( Testee\filter_allowed_block_types( [] ), [ 'erb/reusable-block' ] );
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

	public function test_add_block_admin_bar_menu_items() {
		Functions\stubTranslationFunctions();

		$new_post_url = 'https://erb.local/wordpress/wp-admin/post-new.php?post_type=wp_block';

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
}
