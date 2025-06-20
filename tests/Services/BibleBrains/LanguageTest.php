<?php

namespace Tests\Services\BibleBrains;

use Brain\Monkey\Functions;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\Services\BibleBrains\Api\Languages;
use CodeZone\Bible\Services\BibleBrains\Language;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\WhiteCube\Lingua\Service as Lingua;
use Tests\TestCase;
use function CodeZone\Bible\config;

class LanguageTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_locale_from_translations()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test getting locale
        $result = $language->locale();

        // Assert that the result is the expected locale
        $this->assertEquals( 'en-US', $result );
    }

    /**
     * @test
     */
    public function it_converts_locale_to_iso_code()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test getting ISO code
        $result = $language->iso();

        // Assert that the result is the expected ISO code
        // Note: This test assumes that Lingua::create('en-US')->toISO_639_3() returns 'eng'
        // If this is not the case, the test will need to be adjusted
        $this->assertEquals( 'eng', $result );
    }

    /**
     * @test
     */
    public function it_resolves_language_when_iso_code_is_supported()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test resolving language
        $result = $language->resolve();

        // Assert that the result is the expected language config
        $this->assertEquals( $language_config[0], $result );
    }

    /**
     * @test
     */
    public function it_returns_default_language_when_iso_code_is_not_supported()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock to return a locale that will convert to an unsupported ISO code
        $translations->method( 'resolve_locale' )
            ->willReturn( 'fr-FR' ); // This will convert to 'fra' which is not in our supported languages

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Mock the supported method to return false for 'fra'
        $language_mock = $this->getMockBuilder( Language::class )
            ->setConstructorArgs( [ $options, $languages, $translations ] )
            ->onlyMethods( [ 'supported', 'iso' ] )
            ->getMock();

        $language_mock->method( 'supported' )
            ->with( 'fra' )
            ->willReturn( false );

        $language_mock->method( 'iso' )
            ->willReturn( 'fra' );

        // Test resolving language
        $result = $language_mock->resolve();

        // Assert that the result is the default language config
        $this->assertEquals( $language_config[0], $result );
    }

    /**
     * @test
     */
    public function it_finds_language_by_code()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test finding a language by code
        $result = $language->find( 'spa' );

        // Assert that the result is the expected language config
        $this->assertEquals( $language_config[1], $result );
    }

    /**
     * @test
     */
    public function it_returns_null_when_language_not_found()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test finding a non-existent language
        $result = $language->find( 'fra' );

        // Assert that the result is null
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_returns_false_when_options_throw_exception()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock to throw an exception
        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willThrowException( new \Exception( 'Options error' ) );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test finding a language when options throw an exception
        $result = $language->find( 'eng' );

        // Assert that the result is false
        $this->assertFalse( $result );
    }

    /**
     * @test
     */
    public function it_checks_if_language_is_supported()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test supported languages
        $this->assertTrue( $language->supported( 'eng' ) );
        $this->assertTrue( $language->supported( 'spa' ) );

        // Test unsupported language
        $this->assertFalse( $language->supported( 'fra' ) );
    }

    /**
     * @test
     */
    public function it_returns_default_language()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'is_default' => true
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test getting default language
        $result = $language->default();

        // Assert that the result is the language marked as default
        $this->assertEquals( $language_config[1], $result );
    }

    /**
     * @test
     */
    public function it_returns_first_language_when_no_default_is_set()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test getting default language when none is marked as default
        $result = $language->default();

        // Assert that the result is the first language in the config
        $this->assertEquals( $language_config[0], $result );
    }

    /**
     * @test
     */
    public function it_returns_config_default_when_no_languages_configured()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock to return null
        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( null );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Mock the config function to return a default language
        $default_language = [ 'value' => 'eng', 'itemText' => 'English' ];
        Functions\expect( 'CodeZone\Bible\config' )
            ->andReturnUsing(function ( $path, $default = null ) use ( $default_language ) {
                if ( $path === 'options.defaults.language' ) {
                    return $default_language;
                }
                return $default;
            });

        // Test getting default language when no languages are configured
        $result = $language->default();

        // Assert that the result is the default from config
        $this->assertEquals( $default_language, $result );
    }

    /**
     * @test
     */
    public function it_finds_language_or_returns_default()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test finding an existing language
        $result = $language->find_or_default( 'spa' );

        // Assert that the result is the found language
        $this->assertEquals( $language_config[1], $result );

        // Test finding a non-existent language
        $result = $language->find_or_default( 'fra' );

        // Assert that the result is the default language
        $this->assertEquals( $language_config[0], $result );
    }

    /**
     * @test
     */
    public function it_determines_text_direction()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $languages = $this->createMock( Languages::class );
        $translations = $this->createMock( Translations::class );

        // Create the Language service
        $language = new Language( $options, $languages, $translations );

        // Test LTR text
        $ltr_text = 'This is English text';
        $this->assertEquals( 'ltr', $language->get_direction( $ltr_text ) );

        // Test RTL text (using Hebrew characters)
        $rtl_text = 'שלום עולם'; // "Hello world" in Hebrew
        $this->assertEquals( 'rtl', $language->get_direction( $rtl_text ) );

        // Test with array of verses
        $verses = [
            [ 'verse_text' => 'This is verse 1' ],
            [ 'verse_text' => 'This is verse 2' ]
        ];
        $this->assertEquals( 'ltr', $language->get_direction( $verses ) );

        // Test with empty array
        $this->assertEquals( 'auto', $language->get_direction( [] ) );
    }
}
