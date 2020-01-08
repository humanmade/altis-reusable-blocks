<?php

namespace EnhancedReusableBlocks\REST_API;

/**
 * Bootstrap it up!
 */
function bootstrap() {
	// Relationships endpoint
	$relationships_rest = new Relationships\REST_Endpoint();

	add_action( 'rest_api_init', [ $relationships_rest, 'register_routes' ] );
}
