<?php

namespace Altis\ReusableBlocks\Tests\Unit\REST_API\Search;

use Altis\ReusableBlocks\Tests\Unit\TestCase;
use Altis\ReusableBlocks\REST_API\Search\REST_Endpoint as Testee;
use const Altis\ReusableBlocks\BLOCK_POST_TYPE;

use Brain\Monkey\Functions;

class RESTEndpointTest extends TestCase {

	public function test_register_rest_routes() {

		$testee = new Testee();

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
						'callback' => [ $testee, 'get_items' ],
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

		$testee->register_routes();
	}
}
