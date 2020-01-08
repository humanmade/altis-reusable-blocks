<?php
/**
 * Custom REST API endpoint for block relationship data.
 */

namespace EnhancedReusableBlocks\REST_API\Relationships;

use EnhancedReusableBlocks\Connections;
use EnhancedReusableBlocks;

use WP_Error;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Posts_Controller;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class REST_Endpoint.
 *
 * Create REST API endpoints for returning block relationship data.
 */
class REST_Endpoint extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'erb/v1';
		$this->rest_base = 'relationships';
	}

	/**
	 * Register homepage routes for WP API.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				'methods'  => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_items' ],
				'schema'   => $this->get_item_schema(),
				'args'     => [
					'context' => [
						'default'  => 'view',
					],
					'block_id' => [
						'required' => true,
					],
				],
			]
		);
	}

	public function get_item_schema() {
		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Block relationships', 'enhanced-reusable-blocks' ),
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
							'context'     => [ 'view', 'edit', 'embed' ],
							'readonly'    => true,
						],
					],
				],
			],
		];
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
				'erb.no_block_id_provided',
				__( 'No `block_id` parameter provided.', 'enhanced-reusable-blocks' ),
				[ 'status' => 404 ]
			);
		}

		if ( ! $post = get_post( $block_id ) ) {
			// translators: %d is the post ID that relationships were requested via REST API.
			return new WP_Error(
				'erb.not_block_found',
				sprintf( __( 'The requested post ID of %d not found.', 'enhanced-reusable-blocks' ), $block_id ),
				[ 'status' => 404 ]
			);
		}

		if ( $post->post_type !== 'wp_block' ) {
			return new WP_Error(
				'erb.not_block_post_type',
				__( 'The requested post ID was not of the post type `wp_block`.', 'enhanced-reusable-blocks' ),
				[ 'status' => 404 ]
			);
		}

		$page = $request->get_param( 'page' );

		$term_id = Connections\get_associated_term_id( $block_id );

		// Return a blank array if no term_id is found.
		if ( ! $term_id ) {
			return [];
		}

		$query_args = [
			'posts_per_page' => EnhancedReusableBlocks\RELATIONSHIPS_PER_PAGE,
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

		$posts_query  = new WP_Query();
		$query_result = $posts_query->query( $query_args );
		$total_posts  = $posts_query->found_posts;

		if ( ! $total_posts ) {
			return [];
		}

		$max_pages = ceil( $total_posts / EnhancedReusableBlocks\RELATIONSHIPS_PER_PAGE );

		// Return error if requested invalid page number.
		if ( $page > $max_pages && $total_posts > 0 ) {
			return new WP_Error(
				'rest_post_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'enhanced-reusable-blocks' ),
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
				'erb.relationships_error',
				__( 'Encountered error retrieving relationship data.', 'enhanced-reusable-blocks' ),
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

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

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
	 * @since 4.7.0
	 *
	 * @param WP_Post         $post    Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post, $request ) {
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
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 4.7.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_Post          $post     Post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "rest_prepare_{$this->rest_base}", $response, $post, $request );
	}

}
