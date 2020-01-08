<?php

namespace EnhancedReusableBlocks\Tests\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Abstract base class for all unit test case implementations.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Prepare the test environment before each test.
	 *
	 * @return void
	 */
	protected function setUp() {

		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Clean up the test environment after each test.
	 *
	 * @return void
	 */
	protected function tearDown() {

		Monkey\tearDown();
		parent::tearDown();
	}
}
