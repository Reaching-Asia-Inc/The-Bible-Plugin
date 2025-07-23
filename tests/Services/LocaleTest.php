<?php

namespace Tests\Services;

use CodeZone\Bible\Services\Locale;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\WhiteCube\Lingua\Service as Lingua;
use Tests\TestCase;

/**
 * @group services
 * @group locale
 */
class LocaleTest extends TestCase {
    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var Translations|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translations;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();

        // Mock the Translations dependency
        $this->translations = $this->createMock( Translations::class );

        // Create the Locale service
        $this->locale = new Locale( $this->translations );
    }

    /**
     * @test
     */
    public function it_can_be_instantiated() {
        $this->assertInstanceOf( Locale::class, $this->locale );
    }

    /**
     * @test
     */
    public function it_returns_resolved_locale_from_translations() {
        // Mock the resolve_locale method to return a specific locale
        $this->translations->method( 'resolve_locale' )->willReturn( 'en_US' );

        // Call the method
        $result = $this->locale->locale();

        // Verify the result
        $this->assertEquals( 'en_US', $result );
    }

    /**
     * @test
     */
    public function it_creates_lingua_service_with_current_locale() {
        // Mock the resolve_locale method to return a specific locale
        $this->translations->method( 'resolve_locale' )->willReturn( 'en_US' );

        // Mock the Lingua::create static method
        \Patchwork\redefine('CodeZone\Bible\WhiteCube\Lingua\Service::create', function ( $locale ) {
            // Return a mock Lingua service
            $lingua = $this->createMock( Lingua::class );
            $lingua->locale = $locale;
            return $lingua;
        });

        // Call the method
        $result = $this->locale->lingua();

        // Verify the result is a Lingua service with the correct locale
        $this->assertInstanceOf( Lingua::class, $result );
        $this->assertEquals( 'en_US', $result->locale );
    }

    /**
     * @test
     */
    public function it_guesses_rtl_text_direction() {
        // Test with Arabic text (RTL)
        $rtl_text = 'هذا نص باللغة العربية';
        $result = $this->locale->guess_text_direction( $rtl_text );

        // Verify the result
        $this->assertEquals( 'rtl', $result );
    }

    /**
     * @test
     */
    public function it_guesses_ltr_text_direction() {
        // Test with English text (LTR)
        $ltr_text = 'This is English text';
        $result = $this->locale->guess_text_direction( $ltr_text );

        // Verify the result
        $this->assertEquals( 'ltr', $result );
    }

    /**
     * @test
     */
    public function it_handles_array_of_strings_for_text_direction() {
        // Test with an array of strings
        $text_array = [ 'This is English text', 'More English text' ];
        $result = $this->locale->guess_text_direction( $text_array );

        // Verify the result
        $this->assertEquals( 'ltr', $result );
    }

    /**
     * @test
     */
    public function it_handles_array_of_verse_objects_for_text_direction() {
        // Test with an array of verse objects
        $verse_array = [
            [ 'verse_text' => 'This is English text' ],
            [ 'verse_text' => 'More English text' ]
        ];
        $result = $this->locale->guess_text_direction( $verse_array );

        // Verify the result
        $this->assertEquals( 'ltr', $result );
    }

    /**
     * @test
     */
    public function it_returns_auto_for_empty_array() {
        // Test with an empty array
        $empty_array = [];
        $result = $this->locale->guess_text_direction( $empty_array );

        // Verify the result
        $this->assertEquals( 'auto', $result );
    }

    /**
     * @test
     */
    public function it_handles_mixed_rtl_ltr_text() {
        // Test with mixed RTL and LTR text, with more RTL characters
        $mixed_text_rtl = 'هذا نص باللغة العربية with some English';
        $result = $this->locale->guess_text_direction( $mixed_text_rtl );

        // Verify the result
        $this->assertEquals( 'rtl', $result );

        // Test with mixed RTL and LTR text, with more LTR characters
        $mixed_text_ltr = 'This is English text with some العربية';
        $result = $this->locale->guess_text_direction( $mixed_text_ltr );

        // Verify the result
        $this->assertEquals( 'ltr', $result );
    }
}
