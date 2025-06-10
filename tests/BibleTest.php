<?php

namespace Tests;

use CodeZone\Bible\Illuminate\Support\Str;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use function CodeZone\Bible\container;

/**
 * Class BibleBrainsSettingsTest
 *
 * This class is responsible for testing the BibleBrains settings page.
 *
 * @test
 */
class BibleTest extends TestCase {
	/**
	 * @test
	 */
	public function it_can_fetch_a_bible() {
		$response = $this->get( 'api/bibles/ENGESV' );
		$this->assertEquals( 200, $response->status() );
		$result = json_decode( $response->getContent(), true );
		$this->assertEquals( 'ENGESV', $result['data']['abbr'] );
	}

	/**
	 * Test that the BibleBrains settings page loads.
	 * @test
	 */
	public function it_can_fetch_language_options() {
		$response = $this->get( 'api/bibles/options', [ 'limit' => 2 ] );
		$this->assertEquals( 200, $response->status() );
		$result = json_decode( $response->getContent(), true );
		$this->assertEquals( 2, count( $result['data'] ) );
		foreach ( json_decode( $response->getContent(), true )['data'] as $language ) {
			$this->assertArrayHasKey( 'value', $language );
			$this->assertArrayHasKey( 'itemText', $language );
		}
	}


	/**
	 * Test that the BibleBrains settings page loads.
	 * @test
	 */
	public function it_can_search() {
		$languages = container()->make( Bibles::class );
		$result    = $languages->search( 'New King James Version' );
		$this->assertGreaterThan( 0, $result['data'] );
		$this->assertGreaterThan( 0, count( $result['data'] ) );
	}

	/**
	 * @test
	 */
	public function it_can_get_bible_content() {
		$bibles    = container()->make( Bibles::class );
		$scripture = $bibles->reference( 'John 3', 'ENGESV' );
		$this->assertGreaterThan( 3, count( $scripture['data'] ) );
		foreach ( $scripture['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'JHN' );
		}
		$scripture = $bibles->reference( 'JHN 3:16-17', 'ENGKJV' );
		$this->assertEquals( 2, count( $scripture['data'] ) );
		foreach ( $scripture['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'JHN' );
		}
		$scripture = $bibles->reference( 'john 3:16', 'ENGKJV' );
		$this->assertEquals( 1, count( $scripture['data'] ) );
		foreach ( $scripture['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'JHN' );
		}
	}
}
