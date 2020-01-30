<?php

namespace Altis\ReusableBlocks\Tests\Unit;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks\Categories as Testee;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;

class CategoriesTest extends TestCase {

	/**
	 * Tests `bootstrap` function to ensure all actions and filters are added correctly.
	 *
	 * @return void
	 */
	public function test_bootstrap() {
		Actions\expectAdded( 'init' )
			->with( 'Altis\ReusableBlocks\Categories\register_block_categories' );

		Testee\bootstrap();
	}

	public function test_register_block_categories() {
		Functions\expect( 'register_taxonomy' )
			->with( 'wp_block_category', 'wp_block', [
				'label' => 'Block Categories',
				'labels' => [
					'name'                       => 'Block Categories',
					'singular_name'              => 'Block Category',
					'search_items'               => 'Search Block Categories',
					'popular_items'              => 'Popular Block Categories',
					'all_items'                  => 'All Block Categories',
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => 'Edit Block Category',
					'update_item'                => 'Update Block Category',
					'add_new_item'               => 'Add New Block Category',
					'new_item_name'              => 'New Block Category Name',
					'separate_items_with_commas' => 'Separate block categories with commas',
					'add_or_remove_items'        => 'Add or remove block categories',
					'choose_from_most_used'      => 'Choose from the most used block categories',
					'not_found'                  => 'No block categories found.',
					'menu_name'                  => 'Categories',
				],
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_nav_menus' => false,
				'show_in_rest' => true,
				'hierarchical' => true,
				'show_admin_column' => true,
				'rewrite' => false,
			] )
			->andReturn( true );
	}
}
