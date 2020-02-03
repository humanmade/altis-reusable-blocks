<?php

namespace Altis\ReusableBlocks\Tests\Unit\REST_API\Search;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks\REST_API\Search\REST_Endpoint as Testee;
use const Altis\ReusableBlocks\BLOCK_POST_TYPE;

use Brain\Monkey\Functions;

class RESTEndpointTest extends TestCase {

	protected function setUp() {
		parent::setup();

		$this->testee = new Testee();
	}

	public function test_register_rest_routes() {
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();

		$mock = \Mockery::mock( 'overload:' . \WP_REST_Blocks_Controller::class );
		$mock->shouldReceive( 'get_item_schema' );

		Functions\expect( 'register_rest_route' )
			->with(
				'altis-reusable-blocks/v1',
				'search',
				\Mockery::subset(
					[
						'methods'  => 'GET',
						'callback' => [ $this->testee, 'get_items' ],
						'args'     => [
							'context' => [
								'default'  => 'view',
							],
							'searchID' => [
								'required' => true,
							],
						],
					]
				)
			);

		$this->testee->register_routes();
	}

	/**
	 * Test if error is returned when not supplying a search ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_empty_block_id() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( null );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if error is returned when supplying an invalid search ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_invalid_search_id() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 'test' );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if error is returned when an invalid post_id for search ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_invalid_post_search_id() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 1 );

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( null );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if response is returned when a valid post_id for search ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_wp_block_post() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 1 );

		$request->shouldReceive( 'set_query_params' )
			->with( [
				'include'        => [ 1 ],
				'posts_per_page' => 100
			] )
			->once();

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'is_error' )
			->andReturn( false );

		Functions\expect( 'rest_do_request' )
			->with( $request )
			->andReturn( $response );

		$this->assertSame( $this->testee->get_items( $request ), $response );
	}

	/**
	 * Test if response is returned when a valid post_id for search ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_non_wp_block_post() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 1 );

		$request->shouldReceive( 'set_query_params' )
			->with( [
				'include'        => [ 2 ],
				'posts_per_page' => 100
			] )
			->once();

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_type = 'post';
		$post->post_content = '<!-- wp:block {"ref":2} /--><!-- wp:paragraph --><p>Test123</p><!-- /wp:paragraph -->';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'is_error' )
			->andReturn( false );

		Functions\expect( 'parse_blocks' )
			->with( $post->post_content )
			->andReturn( [
				[
					'blockName' => 'core/block',
					'attrs'     => [
						'ref' => 2,
					],
				],
				[
					'blockName' => 'core/paragraph',
				]
			] );

		Functions\expect( 'rest_do_request' )
			->with( $request )
			->andReturn( $response );

		$this->assertSame( $this->testee->get_items( $request ), $response );
	}

	/**
	 * Test if response is returned when a valid post_id for search ID in `$request` but empty blocks, when running `$testee->get_items()`.
	 */
	public function test_get_items_invalid_post_type() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 1 );
		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_type = 'page';
		$post->post_content = '<!-- wp:paragraph --><p>Test123</p><!-- /wp:paragraph -->';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		$this->assertSame( $this->testee->get_items( $request ), [] );
	}

	/**
	 * Test if error is returned when the WP_REST_Response object has an error, in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_is_error() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->with( 'searchID' )
			->once()
			->andReturn( 1 );

		$request->shouldReceive( 'set_query_params' )
			->with( [
				'include'        => [ 1 ],
				'posts_per_page' => 100
			] )
			->once();

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'is_error' )
			->andReturn( true );

		Functions\expect( 'rest_do_request' )
			->with( $request )
			->andReturn( $response );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}
}
