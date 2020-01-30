<?php

namespace Altis\ReusableBlocks\Tests\Unit;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks\Connections as Testee;
use const Altis\ReusableBlocks\BLOCK_POST_TYPE;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class ConnectionsTest extends TestCase {

	/**
	 * Tests `bootstrap` function to ensure all actions and filters are added correctly.
	 *
	 * @return void
	 */
	public function test_bootstrap() {
		Actions\expectAdded( 'init' )
			->with( 'Altis\ReusableBlocks\Connections\register_relationship_taxonomy' );

		Actions\expectAdded( 'wp_insert_post' )
			->with( 'Altis\ReusableBlocks\Connections\maybe_create_shadow_term', 10, 2 );

		Actions\expectAdded( 'before_delete_post' )
			->with( 'Altis\ReusableBlocks\Connections\delete_shadow_term' );

		Actions\expectAdded( 'post_updated' )
			->with( 'Altis\ReusableBlocks\Connections\synchronize_associated_terms', 10, 3 );

		Testee\bootstrap();
	}

	/**
	 * Tests `register_relationship_taxonomy` function.
	 *
	 * @return void
	 */
	public function test_register_relationship_taxonomy() {
		Functions\expect( 'register_taxonomy' )
			->with(
				Testee\RELATIONSHIP_TAXONOMY,
				Testee\POST_POST_TYPE,
				[
					'rewrite'       => false,
					'show_tagcloud' => false,
					'hierarchical'  => true,
					'show_in_menu'  => false,
					'meta_box_cb'   => false,
					'public'        => false,
				]
			)
			->andReturn( true );

		Testee\register_relationship_taxonomy();
	}

	/**
	 * Tests `maybe_create_shadow_term` and the scenario where the
	 * post is the invalid post type and returns false.
	 *
	 * @return void
	 */
	public function test_maybe_create_shadow_term_invalid_post_type() {
		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = Testee\POST_POST_TYPE;
		$post->post_status = 'publish';

		$this->assertFalse( Testee\maybe_create_shadow_term( 1, $post ) );
	}

	/**
	 * Tests `maybe_create_shadow_term` and the scenario where the
	 * post has a status of `auto-draft` and returns false.
	 *
	 * @return void
	 */
	public function test_maybe_create_shadow_term_auto_draft_post_status() {
		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_status = 'auto-draft';

		$this->assertFalse( Testee\maybe_create_shadow_term( 1, $post ) );
	}

	/**
	 * Tests `maybe_create_shadow_term` and the scenario where the
	 * shadow term does not exist and needs to be created.
	 *
	 * @return void
	 */
	public function test_maybe_create_shadow_term_not_exist() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';
		$post->post_status = 'publish';

		$shadow_term = [
			'term_id'          => 1,
			'taxonomy_term_id' => 1,
		];

		Functions\expect( 'get_post_meta' )
			->with( $post_id, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'get_term_by' )
			->with( 'id', 1, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( false );

		Functions\expect( 'wp_insert_term' )
			->with(
				$post->post_title,
				Testee\RELATIONSHIP_TAXONOMY,
				[
					'slug' => $post->post_name
				]
			)
			->andReturn( $shadow_term );

		Functions\expect( 'is_wp_error' )
			->with( $shadow_term )
			->andReturn( false );

		Functions\expect( 'update_term_meta' )
			->with( $shadow_term['term_id'], 'shadow_post_id', $post_id )
			->andReturn( true );

		Functions\expect( 'update_post_meta' )
			->with( 1, 'shadow_term_id', $shadow_term['term_id'] )
			->andReturn( true );

		// Test that the shadow term was created.
		$this->assertTrue( Testee\maybe_create_shadow_term( 1, $post ) );
	}

	/**
	 * Tests `maybe_create_shadow_term` and the scenario where the
	 * shadow term is already in sync.
	 *
	 * @return void
	 */
	public function test_maybe_create_shadow_term_already_in_sync() {
		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';
		$post->post_status = 'publish';

		$term = \Mockery::mock( \WP_Term::class );
		$term->term_id = 1;
		$term->slug = 'test-title';
		$term->name = 'Test Title';

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'get_term_by' )
			->with( 'id', 1, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( $term );

		$this->assertFalse( Testee\maybe_create_shadow_term( 1, $post ) );
	}

	/**
	 * Tests `maybe_create_shadow_term` and the scenario where the
	 * shadow term is not in sync and needs to update.
	 *
	 * @return void
	 */
	public function test_maybe_create_shadow_term_not_in_sync() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';
		$post->post_status = 'publish';

		$term = \Mockery::mock( \WP_Term::class );
		$term->term_id = 1;
		$term->slug = 'test-title-123';
		$term->name = 'Test Title 123';

		$shadow_term = [
			'term_id'          => 1,
			'taxonomy_term_id' => 1,
		];

		Functions\expect( 'get_post_meta' )
			->with( $post_id, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'get_term_by' )
			->with( 'id', 1, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( $term );

		Functions\expect( 'wp_update_term' )
			->with(
				$term->term_id,
				Testee\RELATIONSHIP_TAXONOMY,
				[
					'name' => $post->post_title,
					'slug' => $post->post_name,
				]
			)
			->andReturn( $shadow_term );

		// Test that the shadow term was created.
		$this->assertTrue( Testee\maybe_create_shadow_term( 1, $post ) );
	}

	/**
	 * Tests `create_shadow_taxonomy_term` and the scenario where the
	 * shadow term insertion returns a WP_Error.
	 *
	 * @return void
	 */
	public function test_create_shadow_taxonomy_term_false_on_wp_error() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';
		$post->post_status = 'publish';

		$error = \Mockery::mock( \WP_Error::class );

		Functions\expect( 'wp_insert_term' )
			->with(
				$post->post_title,
				Testee\RELATIONSHIP_TAXONOMY,
				[
					'slug' => $post->post_name
				]
			)
			->andReturn( $error );

		Functions\expect( 'is_wp_error' )
			->with( $error )
			->andReturn( true );

		// Test that the function returns false when `wp_insert_term` returns a WP_Error.
		$this->assertFalse( Testee\create_shadow_taxonomy_term( 1, $post ) );
	}

	/**
	 * Tests `create_shadow_taxonomy_term` and the scenario where the
	 * shadow term insertion returns a WP_Error.
	 *
	 * @return void
	 */
	public function test_create_shadow_taxonomy_term_valid() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = BLOCK_POST_TYPE;
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';
		$post->post_status = 'publish';

		$term = \Mockery::mock( \WP_Term::class );
		$term->term_id = 1;
		$term->slug = 'test-title-123';
		$term->name = 'Test Title 123';

		$shadow_term = [
			'term_id'          => 1,
			'taxonomy_term_id' => 1,
		];

		Functions\expect( 'wp_insert_term' )
			->with(
				$post->post_title,
				Testee\RELATIONSHIP_TAXONOMY,
				[
					'slug' => $post->post_name
				]
			)
			->andReturn( $shadow_term );

		Functions\expect( 'is_wp_error' )
			->with( $shadow_term )
			->andReturn( false );

		Functions\expect( 'update_term_meta' )
			->with( $shadow_term['term_id'], 'shadow_post_id', $post_id )
			->andReturn( true );

		Functions\expect( 'update_post_meta' )
			->with( 1, 'shadow_term_id', $shadow_term['term_id'] )
			->andReturn( true );

		// Test that the shadow term was created.
		$this->assertTrue( Testee\create_shadow_taxonomy_term( 1, $post ) );
	}

	/**
	 * Test `delete_shadow_term` and the scenario where the post is not a valid post type.
	 *
	 * @return void
	 */
	public function test_delete_shadow_term_invalid_post_type() {
		$post_id = 1;

		Functions\expect( 'get_post_type' )
			->with( 1 )
			->andReturn( Testee\POST_POST_TYPE );

		$this->assertFalse( Testee\delete_shadow_term( $post_id ) );
	}

	/**
	 * Test `delete_shadow_term` and the scenario where there is no associated term.
	 *
	 * @return void
	 */
	public function test_delete_shadow_term_no_associated_term() {
		$post_id = 1;

		Functions\expect( 'get_post_type' )
			->with( 1 )
			->andReturn( BLOCK_POST_TYPE );

		Functions\expect( 'get_post_meta' )
			->with( $post_id, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'get_term_by' )
			->with( 'id', 1, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( false );

		$this->assertFalse( Testee\delete_shadow_term( $post_id ) );
	}

	/**
	 * Test `delete_shadow_term` and the scenario where the term is fully deleted.
	 *
	 * @return void
	 */
	public function test_delete_shadow_term_deleted_term() {
		$post_id = 1;

		$term = \Mockery::mock( \WP_Term::class );
		$term->term_id = 1;
		$term->slug = 'test-title';
		$term->name = 'Test Title';

		Functions\expect( 'get_post_type' )
			->with( 1 )
			->andReturn( BLOCK_POST_TYPE );

		Functions\expect( 'get_post_meta' )
			->with( $post_id, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'get_term_by' )
			->with( 'id', 1, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( $term );

		Functions\expect( 'wp_delete_term' )
			->with( $term->term_id, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( true );

		$this->assertTrue( Testee\delete_shadow_term( $post_id ) );
	}

	/**
	 * Test `get_associated_post` and the scenario where the post requested is valid.
	 *
	 * @return void
	 */
	public function test_get_associated_post_valid_post() {
		$term_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;

		Functions\expect( 'get_term_meta' )
			->with( $term_id, 'shadow_post_id', true )
			->andReturn( $post->ID );

		Functions\expect( 'get_post' )
			->with( $post->ID )
			->andReturn( $post );

		$this->assertSame( Testee\get_associated_post( $term_id ), $post );
	}

	/**
	 * Test `shadow_term_in_sync` and the scenario where the term and post are synced.
	 *
	 * @return void
	 */
	public function test_shadow_term_in_sync_is_synced() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';

		$term = \Mockery::mock( \WP_Term::class );
		$term->slug = 'test-title';
		$term->name = 'Test Title';

		$this->assertTrue( Testee\shadow_term_in_sync( $term, $post ) );
	}

	/**
	 * Test `shadow_term_in_sync` and the scenario where the term and post are not synced.
	 *
	 * @return void
	 */
	public function test_shadow_term_in_sync_not_synced() {
		$post_id = 1;

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_title = 'Test Title';
		$post->post_name = 'test-title';

		$term = \Mockery::mock( \WP_Term::class );
		$term->slug = 'test-title-123';
		$term->name = 'Test Title 123';

		$this->assertFalse( Testee\shadow_term_in_sync( $term, $post ) );
	}

	/**
	 * Test `synchronize_associated_terms` and the scenario where the post is not a valid post type.
	 *
	 * @return void
	 */
	public function test_synchronize_associated_terms_invalid_post_type() {
		$post_id = 1;

		$post_before = \Mockery::mock( \WP_Post::class );
		$post_before->post_type = 'invalid';

		$post_after = \Mockery::mock( \WP_Post::class );
		$post_after->post_type = 'invalid';

		$this->assertFalse( Testee\synchronize_associated_terms( $post_id, $post_before, $post_after ) );
	}

	/**
	 * Test `synchronize_associated_terms` and the scenario where the post content before and after are the same.
	 *
	 * @return void
	 */
	public function test_synchronize_associated_terms_same_content() {
		$post_id = 1;

		$post_before = \Mockery::mock( \WP_Post::class );
		$post_before->post_title = 'Test Title';
		$post_before->post_name = 'test-title';
		$post_before->post_content = 'Same content';
		$post_before->post_type = BLOCK_POST_TYPE;

		$post_after = \Mockery::mock( \WP_Post::class );
		$post_after->post_title = 'Test Title';
		$post_after->post_name = 'test-title';
		$post_after->post_content = 'Same content';
		$post_after->post_type = BLOCK_POST_TYPE;

		$this->assertFalse( Testee\synchronize_associated_terms( $post_id, $post_before, $post_after ) );
	}

	/**
	 * Test `synchronize_associated_terms` and the scenario where the post content does not contain any reusable blocks.
	 *
	 * @return void
	 */
	public function test_synchronize_associated_terms_different_content_no_reusable_blocks() {
		$post_id = 1;

		$post_before = \Mockery::mock( \WP_Post::class );
		$post_before->post_title = 'Test Title';
		$post_before->post_name = 'test-title';
		$post_before->post_content = 'Content';
		$post_before->post_type = Testee\POST_POST_TYPE;

		$post_after = \Mockery::mock( \WP_Post::class );
		$post_after->post_title = 'Test Title';
		$post_after->post_name = 'test-title';
		$post_after->post_content = 'Different content';
		$post_after->post_type = Testee\POST_POST_TYPE;

		Functions\expect( 'parse_blocks' )
			->with( $post_after->post_content )
			->andReturn( [] );

		Functions\expect( 'wp_set_object_terms' )
			->with( $post_id, null, Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( [ 1, 1] );

		$this->assertTrue( Testee\synchronize_associated_terms( $post_id, $post_before, $post_after ) );
	}

	/**
	 * Test `synchronize_associated_terms` and the scenario where the post content has reusable blocks within it.
	 *
	 * @return void
	 */
	public function test_synchronize_associated_terms_different_content_with_reusable_blocks() {
		$post_id = 1;

		$post_before = \Mockery::mock( \WP_Post::class );
		$post_before->post_title = 'Test Title';
		$post_before->post_name = 'test-title';
		$post_before->post_content = 'Content';
		$post_before->post_type = Testee\POST_POST_TYPE;

		$post_after = \Mockery::mock( \WP_Post::class );
		$post_after->post_title = 'Test Title';
		$post_after->post_name = 'test-title';
		$post_after->post_content = 'Different content';
		$post_after->post_type = Testee\POST_POST_TYPE;

		$parsed_blocks = [
			[
				'blockName' => 'core/paragraph',
				'attrs' => [],
				'innerBlocks' => [],
				'innerHTML' => 'This is a paragraph block!',
				'innerContent' => [
					'This is a paragraph block!',
				],
			],
			[
				'blockName' => 'core/block',
				'attrs' => [
					'ref' => 2,
					'blockHtml' => 'This is a block content!',
				],
				'innerBlocks' => [],
				'innerHTML' => 'This is a block content!',
				'innerContent' => [
					'This is a block content!',
				],
			]
		];

		Functions\expect( 'parse_blocks' )
			->with( $post_after->post_content )
			->andReturn( $parsed_blocks );

		Functions\expect( 'get_post_meta' )
			->with( $post_id, 'shadow_term_id', true )
			->andReturn( 1 );

		Functions\expect( 'wp_set_object_terms' )
			->with( $post_id, [ 1 ], Testee\RELATIONSHIP_TAXONOMY )
			->andReturn( [ 'term_id' => 1, 'taxonomy_term_id' => 1 ] );

		$this->assertTrue( Testee\synchronize_associated_terms( $post_id, $post_before, $post_after ) );
	}
}
