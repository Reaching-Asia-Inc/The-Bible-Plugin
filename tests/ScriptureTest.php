<?php

namespace Tests;

use CodeZone\Bible\Services\BibleBrains\Scripture;
use function CodeZone\Bible\container;

class ScriptureTest extends TestCase {
	/**
	 * @test
	 */
	public function it_can_query_by_reference() {
		$scripture = container()->make( Scripture::class );
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
		$scripture = container()->make( Scripture::class );
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
		$scripture = container()->make( Scripture::class );
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
