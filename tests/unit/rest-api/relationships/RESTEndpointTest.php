<?php

namespace Altis\ReusableBlocks\Tests\Unit\REST_API\Relationships;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks\REST_API\Relationships\REST_Endpoint as Testee;
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

		Functions\expect( 'register_rest_route' )
			->with(
				'altis-reusable-blocks/v1',
				'relationships',
				\Mockery::subset(
					[
						[
							'methods'  => 'GET',
							'callback' => [ $this->testee, 'get_items' ],
							'args'     => [
								'context' => [
									'default'  => 'view',
								],
								'block_id' => [
									'description' => 'Block ID to get the relationship data for.',
									'required'    => true,
									'type'        => 'integer',
								],
							],
						],
						'schema' => [ $this->testee, 'get_item_schema' ],
					]
				)
			);

		$this->testee->register_routes();
	}

	public function test_get_item_schema() {
		Functions\stubTranslationFunctions();

		Functions\expect( 'get_post_stati' )
			->with( [ 'internal' => false ] )
			->andReturn( [
				'publish' => 'Publish',
				'draft' => 'Draft',
				'pending' => 'Pending',
				'trash' => 'Trash',
			] );

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'Block relationships',
			'type'       => 'object',
			'properties' => [
				'id'     => [
					'description' => 'Unique identifier for the object.',
					'type'        => 'integer',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'status' => [
					'description' => 'A named status for the object.',
					'type'        => 'string',
					'enum'        => [
						'publish',
						'draft',
						'pending',
						'trash',
					],
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'type'   => [
					'description' => 'Type of Post for the object.',
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'title'  => [
					'description' => 'The title for the object.',
					'type'        => 'object',
					'context'     => [ 'view' ],
					'readonly'    => true,
					'properties'  => [
						'rendered' => [
							'description' => 'HTML title for the object, transformed for display.',
							'type'        => 'string',
							'context'     => [ 'view' ],
							'readonly'    => true,
						],
					],
				],
			],
		];

		$this->assertSame( $schema, $this->testee->get_item_schema() );
	}

	/**
	 * Test if invalid response is passed into `prepare_response_for_collection`.
	 */
	public function test_prepare_response_for_collection_incorrect_response() {
		$expected = 'invalid';

		$this->assertSame( $this->testee->prepare_response_for_collection( 'invalid' ), $expected );
	}

	/**
	 * Test if valid response is passed into `prepare_response_for_collection` and empty links returned from `WP_REST_Server::get_compact_response_links`.
	 */
	public function test_prepare_response_for_collection_correct_response_empty_links() {
		$data = [
			[
				'ID' => 1,
			]
		];

		$links = [];

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'get_data' )
			->once()
			->andReturn( $data );

		$server = \Mockery::mock( 'overload:' . \WP_REST_Server::class );
		$server->shouldReceive( 'get_compact_response_links' )
			->with( $response )
			->andReturn( $links );

		Functions\expect( 'rest_get_server' )
			->once()
			->andReturn( $server );

		$this->assertSame( $data, $this->testee->prepare_response_for_collection( $response ) );
	}

	/**
	 * Test if valid response is passed into `prepare_response_for_collection` and two links returned from `WP_REST_Server::get_compact_response_links`.
	 */
	public function test_prepare_response_for_collection_correct_response_with_links() {
		$data = [
			[
				'ID' => 1,
			]
		];

		$links = [
			'prevLink' => 'https://altis.local/prevPage',
			'nextLink' => 'https://altis.local/nextPage',
		];

		$expected_data = [
			[
				'ID' => 1,
			],
			'_links' => $links
		];

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'get_data' )
			->once()
			->andReturn( $data );

		$server = \Mockery::mock( 'overload:' . \WP_REST_Server::class );
		$server->shouldReceive( 'get_compact_response_links' )
			->with( $response )
			->andReturn( $links );

		Functions\expect( 'rest_get_server' )
			->once()
			->andReturn( $server );

		$this->assertSame( $expected_data, $this->testee->prepare_response_for_collection( $response ) );
	}

	/**
	 * Test if error is returned when not supplying a block ID in `$request` when running `$testee->get_items()`.
	 */
	public function test_get_items_empty_block_id() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->once()
			->andReturn( null );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if error is returned when supplying a block ID in `$request` but returning false from `get_post` when running `$testee->get_items()`
	 */
	public function test_get_items_no_post() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->once()
			->andReturn( 1 );

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( null );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if error is returned when supplying a block ID in `$request` but returning invalid post type from `get_post` when running `$testee->get_items()`
	 */
	public function test_get_items_invalid_post_type() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );
		$request->shouldReceive( 'get_param' )
			->once()
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'post';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if empty array is returned when no associated term_id is returned when running `$testee->get_items()`.
	 */
	public function test_get_items_no_associated_term_id() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( false );

		$this->assertSame( $this->testee->get_items( $request ), [] );
	}

	/**
	 * Test if empty array is returned when no total posts is returned from `WP_Query` when running `$testee->get_items()`.
	 */
	public function test_get_items_no_total_posts() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		$query = \Mockery::mock( 'overload:' . \WP_Query::class )
			->shouldReceive( 'query' )
			->andSet( 'found_posts', 0 )
			->andReturn( [] );

		$this->assertSame( $this->testee->get_items( $request ), [] );
	}

	/**
	 * Test if `WP_Error` is returned when invalid page is requested when running `$testee->get_items()`.
	 */
	public function test_get_items_invalid_page() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 3 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		$query = \Mockery::mock( 'overload:' . \WP_Query::class )
			->shouldReceive( 'query' )
			->andSet( 'found_posts', 1 )
			->andReturn( [
				[
					'ID' => 1
				],
			] );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if `WP_Error` is returned when the response is an error, when running `$testee->get_items()`.
	 */
	public function test_get_items_response_error() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_status = 'publish';
		$post->post_title = 'Post title';
		$post->post_type = 'post';

		$query = \Mockery::mock( 'overload:' . \WP_Query::class )
			->shouldReceive( 'query' )
			->andSet( 'found_posts', 1 )
			->andReturn( [ $post ] );

		$data = [
			'id' => 1,
			'status' => 'publish',
			'type' => 'post',
			'title' => [
				'rendered' => 'Post title',
			],
		];

		$links = [];

		Functions\expect( 'get_the_title' )
			->with( 1 )
			->andReturn( 'Post title' );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'get_data' )
			->once()
			->andReturn( $data );

		$response->shouldReceive( 'is_error' )
			->andReturn( true );

		$server = \Mockery::mock( 'overload:' . \WP_REST_Server::class );
		$server->shouldReceive( 'get_compact_response_links' )
			->with( $response )
			->andReturn( $links );

		Functions\expect( 'rest_get_server' )
		->once()
		->andReturn( $server );

		Functions\expect( 'rest_ensure_response' )
			->with( $data )
			->andReturn( $response );

		\Mockery::mock( 'overload:' . \WP_Error::class );

		$this->assertTrue( is_a( $this->testee->get_items( $request ), 'WP_Error' ) );
	}

	/**
	 * Test if valid response is returned when the first page is requested and there are no other pages, when running `$testee->get_items()`.
	 */
	public function test_get_items_valid() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_query_params' )
			->andReturn( [] );

		Functions\expect( 'add_query_arg' )
			->with( '' )
			->andReturn( '' )
			->once();

		Functions\expect( 'urlencode_deep' )
			->with( [] )
			->andReturn( '' )
			->once();

		Functions\expect( 'rest_url' )
			->with( 'altis-reusable-blocks/v1/relationships' )
			->once();

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_status = 'publish';
		$post->post_title = 'Post title';
		$post->post_type = 'post';

		$query = \Mockery::mock( 'overload:' . \WP_Query::class )
			->shouldReceive( 'query' )
			->andSet( 'found_posts', 1 )
			->andReturn( [ $post ] );

		$data = [
			'id' => 1,
			'status' => 'publish',
			'type' => 'post',
			'title' => [
				'rendered' => 'Post title',
			],
		];

		$links = [];

		Functions\expect( 'get_the_title' )
			->with( 1 )
			->andReturn( 'Post title' );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'get_data' )
			->once()
			->andReturn( $data );

		$response->shouldReceive( 'is_error' )
			->andReturn( false );

		$response->shouldReceive( 'header' )
			->with( \Mockery::anyOf( 'X-WP-Total', 'X-WP-TotalPages' ), 1 )
			->andReturn( true );

		$server = \Mockery::mock( 'overload:' . \WP_REST_Server::class );
		$server->shouldReceive( 'get_compact_response_links' )
			->with( $response )
			->andReturn( $links );

		Functions\expect( 'rest_get_server' )
		->once()
		->andReturn( $server );

		Functions\expect( 'rest_ensure_response' )
			->with( $data )
			->andReturn( $response );

		$this->testee->get_items( $request );
	}

	/**
	 * Test if valid response is returned when the first page is requested and there are no other pages, when running `$testee->get_items()`.
	 */
	public function test_get_items_valid_with_pagination() {
		Functions\stubTranslationFunctions();

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		$request->shouldReceive( 'get_param' )
			->with( 'block_id' )
			->andReturn( 1 );

		$request->shouldReceive( 'get_param' )
			->with( 'page' )
			->andReturn( 2 );

		$request->shouldReceive( 'get_query_params' )
			->andReturn( [] );

		Functions\expect( 'add_query_arg' )
			->with( '' )
			->andReturn( '' );

		Functions\expect( 'urlencode_deep' )
			->with( [] )
			->andReturn( '' )
			->once();

		Functions\expect( 'rest_url' )
			->with( 'altis-reusable-blocks/v1/relationships' )
			->once();

		$post = \Mockery::mock( \WP_Post::class );
		$post->post_type = 'wp_block';

		Functions\expect( 'get_post' )
			->with( 1 )
			->andReturn( $post );

		Functions\expect( 'get_post_meta' )
			->with( 1, 'shadow_term_id', true )
			->andReturn( 1 );

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_status = 'publish';
		$post->post_title = 'Post title';
		$post->post_type = 'post';

		$query = \Mockery::mock( 'overload:' . \WP_Query::class )
			->shouldReceive( 'query' )
			->andSet( 'found_posts', 32 )
			->andReturn( [ $post ] );

		$data = [
			'id' => 1,
			'status' => 'publish',
			'type' => 'post',
			'title' => [
				'rendered' => 'Post title',
			],
		];

		$links = [
			'prevLink' => 'https://altis.local/prevPage',
			'nextLink' => 'https://altis.local/nextPage',
		];

		Functions\expect( 'get_the_title' )
			->with( 1 )
			->andReturn( 'Post title' );

		$response = \Mockery::mock( 'overload:' . \WP_REST_Response::class );
		$response->shouldReceive( 'get_data' )
			->once()
			->andReturn( $data );

		$response->shouldReceive( 'is_error' )
			->andReturn( false );

		$response->shouldReceive( 'header' )
			->with( \Mockery::anyOf( 'X-WP-Total', 'X-WP-TotalPages' ), \Mockery::any() )
			->andReturn( true );

		$response->shouldReceive( 'link_header' )
			->with( \Mockery::anyOf( 'prev', 'next' ), \Mockery::any() )
			->andReturn( true );

		$server = \Mockery::mock( 'overload:' . \WP_REST_Server::class );
		$server->shouldReceive( 'get_compact_response_links' )
			->with( $response )
			->andReturn( $links );

		Functions\expect( 'rest_get_server' )
		->once()
		->andReturn( $server );

		Functions\expect( 'rest_ensure_response' )
			->with( $data )
			->andReturn( $response );

		$this->testee->get_items( $request );
	}

	public function test_prepare_item_for_response() {
		$data = [
			'id' => 1,
			'status' => 'publish',
			'type' => 'post',
			'title' => [
				'rendered' => 'Post title',
			],
		];

		$post = \Mockery::mock( \WP_Post::class );
		$post->ID = 1;
		$post->post_status = 'publish';
		$post->post_title = 'Post title';
		$post->post_type = 'post';

		$request = \Mockery::mock( 'overload:' . \WP_REST_Request::class );

		Functions\expect( 'get_the_title' )
			->with( 1 )
			->andReturn( 'Post title' );

		$response = \Mockery::mock( \WP_REST_Response::class );

		Functions\expect( 'rest_ensure_response' )
			->with( $data )
			->andReturn( $response );

		$this->testee->prepare_item_for_response( $post, $request );
	}
}
