<?php

namespace Altis\ReusableBlocks\Tests\Unit;

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
	protected function setUp(): void {

		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Clean up the test environment after each test.
	 *
	 * @return void
	 */
	protected function tearDown(): void {

		Monkey\tearDown();
		parent::tearDown();
	}
}
