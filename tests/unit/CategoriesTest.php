<?php

namespace EnhancedReusableBlocks\Tests\Unit;

use EnhancedReusableBlocks\Tests\Unit\TestCase;
use EnhancedReusableBlocks\Categories as Testee;

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
			->with( 'EnhancedReusableBlocks\Categories\add_categories_taxonomy_to_blocks' );

		Testee\bootstrap();
	}

	public function test_add_categories_taxonomy_to_blocks() {
		Functions\expect( 'register_taxonomy_for_object_type' )
			->with( 'category', 'wp_block' )
			->andReturn( true );

		$this->assertTrue( Testee\add_categories_taxonomy_to_blocks() );
	}
}
