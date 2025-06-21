<?php

namespace Tests\Services;

use CodeZone\Bible\Services\BibleBrains\Scripture;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group services
 * @group scriptures
 */
class ScriptureTest extends TestCase {
	/**
	 * @test
	 */
	public function it_can_query_by_reference() {
		$scripture = container()->get( Scripture::class );
		$result    = $scripture->by_reference( "John 3:16" );
		$this->assertEquals( 1, count( $result['media']['text']['content']['data'] ) );
		foreach ( $result['media']['text']['content']['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'JHN' );
		}
	}

	/**
	 * @test
	 */
	public function it_can_query_by_language() {
		$scripture = container()->get( Scripture::class );
		$result    = $scripture->by_reference( "John 3:16", [
			"language" => 5160
		] );
		$this->assertEquals( 1, count( $result['media']['text']['content']['data'] ) );
		foreach ( $result['media']['text']['content']['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'JHN' );
		}
	}

	/**
	 * @test
	 */
	public function it_can_query_by_bible() {
		$scripture = container()->get( Scripture::class );
		$result    = $scripture->by_reference( "Haggai 1:1-5", [
			"bible" => "DEUD05"
		] );
		$this->assertEquals( 5, count( $result['media']['text']['content']['data'] ) );
		$this->assertStringContainsString( "zweiten", $result['media']['text']['content']['data'][0]['verse_text'] );
		foreach ( $result['media']['text']['content']['data'] as $verse ) {
			$this->assertEquals( $verse['book_id'], 'HAG' );
		}
	}
}
