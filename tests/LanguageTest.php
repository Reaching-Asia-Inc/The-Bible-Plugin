<?php

namespace Tests;

use CodeZone\Bible\Controllers\LanguageController;
use CodeZone\Bible\Illuminate\Support\Str;
use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use CodeZone\Bible\Services\RequestInterface;
use function CodeZone\Bible\container;

/**
 * Class LanguageTest
 *
 * This class is responsible for testing the Language controller.
 *
 * @test
 */
class LanguageTest extends TestCase {
	/**
	 * @test
	 */
	public function it_can_fetch_a_language() {
		// Create a mock Request object
		$request = $this->createMock( RequestInterface::class );

		// Configure the mock to return '6414' for the 'id' parameter
		$request->method( 'get' )
			->willReturnMap([
				[ 'id', null, '6414' ]
			]);

		// Create the controller
		$controller = new LanguageController();

		// Call the show method
		$result = $controller->show( $request );

		// Assert that the result contains the expected data
		$this->assertEquals( '6414', $result['id'] );
	}

	/**
	 * Test that the controller can fetch language options.
	 * @test
	 */
	public function it_can_fetch_language_options() {
		// Create a mock Request object
		$request = $this->createMock( RequestInterface::class );

		// Configure the mock to return limit=2
		$request->method( 'get' )
			->willReturnMap([
				[ 'limit', 50, 2 ],
				[ 'paged', 1, 1 ],
				[ 'search', null, null ]
			]);

		// Create the controller
		$controller = new LanguageController();

		// Call the options method
		$result = $controller->options( $request );

		// Assert that the result contains the expected data
		$this->assertArrayHasKey( 'data', $result );
		$this->assertCount( 2, $result['data'] );
		foreach ( $result['data'] as $language ) {
			$this->assertArrayHasKey( 'value', $language );
			$this->assertArrayHasKey( 'itemText', $language );
		}
	}

	/**
	 * Test that the BibleBrains settings page loads.
	 * @test
	 */
	public function it_can_search() {
		$languages = container()->make( Languages::class );
		$result    = $languages->search( 'Spanish' );
		$this->assertGreaterThan( 0, count( $result['data'] ) );
	}
}
