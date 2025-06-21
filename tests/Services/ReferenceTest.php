<?php

namespace Services;

use CodeZone\Bible\Services\BibleBrains\Reference;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group controllers
 * @group services
 */
class ReferenceTest extends TestCase {
	/**
	 * @test
	 */
	public function it_can_parse_book_only() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( 'John' );
		$this->assertEquals( 'JHN', $reference['book'] );
		$this->assertEquals( 1, $reference['chapter'] );
		$this->assertEquals( null, $reference['verse_start'] );
		$this->assertEquals( null, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_parse_chapter_only() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( 'John 3' );
		$this->assertEquals( 'JHN', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( null, $reference['verse_start'] );
		$this->assertEquals( null, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_verse_only() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( 'John 3:16' );
		$this->assertEquals( 'JHN', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( 16, $reference['verse_start'] );
		$this->assertEquals( 16, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_parse_verse_range() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( 'John 3:16-18' );
		$this->assertEquals( 'JHN', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( 16, $reference['verse_start'] );
		$this->assertEquals( 18, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_parse_books_with_number_and_chapter() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( '1 Peter 3' );
		$this->assertEquals( '1PE', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( null, $reference['verse_start'] );
		$this->assertEquals( null, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_parse_books_with_number() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( '1 Peter 3:12' );
		$this->assertEquals( '1PE', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( 12, $reference['verse_start'] );
		$this->assertEquals( 12, $reference['verse_end'] );
	}

	/**
	 * @test
	 */
	public function it_can_parse_books_with_number_and_verse_range() {
		$service   = container()->get( Reference::class );
		$reference = $service::parse_string( '1 Peter 3:12-13' );
		$this->assertEquals( '1PE', $reference['book'] );
		$this->assertEquals( 3, $reference['chapter'] );
		$this->assertEquals( 12, $reference['verse_start'] );
		$this->assertEquals( 13, $reference['verse_end'] );
	}
}
