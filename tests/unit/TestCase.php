<?php

namespace Altis\ReusableBlocks\Tests\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Abstract base class for all unit test case implementations.
 */
abstract class TestCase extends \Yoast\PHPUnitPolyfills\TestCases\TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Prepare the test environment before each test.
	 *
	 * @return void
	 */
	protected function set_up() {

		parent::set_up();
		Monkey\setUp();
	}

	/**
	 * Clean up the test environment after each test.
	 *
	 * @return void
	 */
	protected function tear_down() {

		Monkey\tearDown();
		parent::tear_down();
	}
}
