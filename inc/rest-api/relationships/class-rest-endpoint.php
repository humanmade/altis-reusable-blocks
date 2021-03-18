<?php
/**
 * Custom REST API endpoint for block relationship data.
 */

namespace Altis\ReusableBlocks\REST_API\Relationships;

use Altis\ReusableBlocks\Connections;
use Altis\ReusableBlocks;

use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Posts_Controller;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class REST_Endpoint.
 *
 * Create REST API endpoints for returning block relationship data.
 */
class REST_Endpoint {

	/**
	 * Namespace for the endpoint.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Base URL for endpoint.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'altis-reusable-blocks/v1';
		$this->rest_base = 'relationships';
	}

	/**
	 * Register relationship routes for WP API.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => function() {
						return current_user_can( 'read_wp_block' );
					},
					'args'                => [
						'context' => [
							'default'  => 'view',
						],
						'block_id' => [
							'description' => esc_html__( 'Block ID to get the relationship data for.', 'altis-reusable-blocks' ),
							'required'    => true,
							'type'        => 'integer',
						],
					],
				],
				'schema'                  => [ $this, 'get_item_schema' ],
			]
		);
	}

	/**
	 * Gets the schema for a single relationship item.
	 *
	 * @return array $schema
	 */
	public function get_item_schema() : array {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Block relationships', 'altis-reusable-blocks' ),
			'type'       => 'object',
			'properties' => [
				'id'     => [
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'status' => [
					'description' => __( 'A named status for the object.' ),
					'type'        => 'string',
					'enum'        => array_keys( get_post_stati( [ 'internal' => false ] ) ),
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'type'   => [
					'description' => __( 'Type of Post for the object.' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'title'  => [
					'description' => __( 'The title for the object.' ),
					'type'        => 'object',
					'context'     => [ 'view' ],
					'readonly'    => true,
					'properties'  => [
						'rendered' => [
							'description' => __( 'HTML title for the object, transformed for display.' ),
							'type'        => 'string',
							'context'     => [ 'view' ],
							'readonly'    => true,
						],
					],
				],
			],
		];

		/**
		 * Filters the additional fields array which starts as an empty array.
		 *
		 * @param array $additional_fields Array of schema data for additional fields to include in the REST response.
		 */
		$additional_fields = apply_filters( 'rest_get_relationship_item_additional_fields_schema', [] );

		$schema['properties'] = array_merge( $schema['properties'], $additional_fields );

		return $schema;
	}

	/**
	 * Prepares a response for insertion into a collection.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array|mixed Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();
		$links  = $server::get_compact_response_links( $response );

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Get related posts data.
	 *
	 * @param  WP_REST_Request $request Request information from the request.
	 * @return WP_REST_Response|Array|WP_Error REST response with relationship data if exists, empty array if no related posts,
	 *                                         or WP_Error if error is returned in query or REST requests for post data.
	 */
	public function get_items( $request ) {
		$block_id = $request->get_param( 'block_id' );

		if ( empty( $block_id ) ) {
			return new WP_Error(
				'altis.reusable_blocks.no_block_id_provided',
				__( 'No `block_id` parameter provided.', 'altis-reusable-blocks' ),
				[ 'status' => 404 ]
			);
		}

		if ( ! $post = get_post( $block_id ) ) {
			// translators: %d is the post ID that relationships were requested via REST API.
			return new WP_Error(
				'altis.reusable_blocks.not_block_found',
				sprintf( __( 'The requested post ID of %d not found.', 'altis-reusable-blocks' ), $block_id ),
				[ 'status' => 404 ]
			);
		}

		if ( $post->post_type !== 'wp_block' ) {
			return new WP_Error(
				'altis.reusable_blocks.not_block_post_type',
				__( 'The requested post ID was not of the post type `wp_block`.', 'altis-reusable-blocks' ),
				[ 'status' => 404 ]
			);
		}

		$page = $request->get_param( 'page' );

		$term_id = Connections\get_associated_term_id( $block_id );

		// Return a blank array if no term_id is found.
		if ( ! $term_id ) {
			return [];
		}

		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		$query_args = [
			'posts_per_page' => ReusableBlocks\RELATIONSHIPS_PER_PAGE,
			'paged'          => $page ?? 1,
			'post_type'      => Connections\get_post_types_with_reusable_blocks(),
			'post_status'    => 'any',
			'tax_query' => [
				[
					'taxonomy' => Connections\RELATIONSHIP_TAXONOMY,
					'field' => 'term_id',
					'terms' => $term_id,
				]
			]
		];
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );
		$total_posts  = $posts_query->found_posts;

		if ( ! $total_posts ) {
			return [];
		}

		$max_pages = ceil( $total_posts / ReusableBlocks\RELATIONSHIPS_PER_PAGE );

		// Return error if requested invalid page number.
		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'altis-reusable-blocks' ),
				array( 'status' => 400 )
			);
		}

		$posts = [];

		foreach ( $query_result as $post ) {
			$data    = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $posts );

		// Handle if error.
		if ( $response->is_error() ) {
			return new WP_Error(
				'altis.reusable_blocks.relationships_error',
				__( 'Encountered error retrieving relationship data.', 'altis-reusable-blocks' ),
				[ 'status' => 404 ]
			);
		}

		// Add total and total pages headers.
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( "{$this->namespace}/{$this->rest_base}" ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			$prev_link = add_query_arg( 'page', $prev_page, $base );

			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Prepares a single post output for response.
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( WP_Post $post, WP_REST_Request $request ) : WP_REST_Response {
		// Base fields for every post.
		$data = [
			'id' => $post->ID,
			'status' => $post->post_status,
			'type' => $post->post_type,
			'title' => [
				'rendered' => get_the_title( $post->ID ),
			],
		];

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the post data for a response.
		 *
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'rest_prepare_relationships_response', $response, $post, $request );
	}

}
