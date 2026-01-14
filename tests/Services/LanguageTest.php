<?php

namespace Services;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\Locale;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\Settings\LanguagesSetting;
use CodeZone\Bible\Sources\Aggregators\LanguageAggregator;
use Tests\TestCase;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;

/**
 * @group languages
 */
class LanguageTest extends TestCase
{
    /**
     * @test
     */
    public function it_gets_locale_from_translations()
    {
        // Create mock dependencies
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Create the Language service
        $language = new Locale( $translations );

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
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Create the Language service
        $language = new Locale( $translations );

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
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock
        $translations->method( 'resolve_locale' )
            ->willReturn( 'en-US' );

        // Configure the options mock
        $language_config = [
            [
                'value' => '6414',
                'code' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true,
                'id' => 5414,
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test resolving language
        $result = $language->guess();

        $expected = [
            'value' => '6414',
            'code' => 'eng',
            'itemText' => 'English',
            'bibles' => [ 'ENGESV' ],
            'is_default' => true,
            'id' => 5414,
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains',
            'default_bible' => 'ENGESV',
            'bible_name' => 'English Standard Version®',
            'bibles_data' => [
                [
                    'id' => 'ENGESV',
                    'name' => 'English Standard Version®',
                ]
            ],
        ];

        // Assert that the result is the expected language config
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_returns_default_language_when_iso_code_is_not_supported()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );
        $translations = $this->createMock( Translations::class );

        // Configure the translations mock to return a locale that will convert to an unsupported ISO code
        $translations->method( 'resolve_locale' )
            ->willReturn( 'fr-FR' ); // This will convert to 'fra' which is not in our supported languages

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'code' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true,
                'id' => 5414,
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        $locale_mock = $this->getMockBuilder( Locale::class )
            ->setConstructorArgs( [ $translations ] )
            ->onlyMethods( [ 'iso' ] )
            ->getMock();

        // Mock the supported method to return false for 'fra'
        $language_mock = $this->getMockBuilder( LanguagesSetting::class )
            ->setConstructorArgs( [ $locale_mock, $options, container()->get( LanguageAggregator::class ) ] )
            ->onlyMethods( [ 'supported' ] )
            ->getMock();

        $language_mock->method( 'supported' )
            ->with( 'fra' )
            ->willReturn( false );

        $locale_mock->method( 'iso' )
            ->willReturn( 'fra' );

        // Test resolving language
        $result = $language_mock->guess();

        $expected = [
            'value' => 'eng',
            'code' => 'eng',
            'itemText' => 'English',
            'bibles' => [ 'ENGESV' ],
            'is_default' => true,
            'id' => 5414,
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains',
            'default_bible' => 'ENGESV',
            'bible_name' => 'English Standard Version®',
            'bibles_data' => [
                [
                    'id' => 'ENGESV',
                    'name' => 'English Standard Version®',
                ]
            ],
        ];

        // Assert that the result is the default language config
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_finds_language_by_code()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock
        $language_config = [
            [
                'id' => 5414,
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'code' => 'eng',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ],
            [
                'id' => 'spa',
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'code' => 'spa',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test finding a language by code
        $result = $language->find( 'spa' );

        $expected = [
            'id' => 'spa',
            'value' => 'spa',
            'itemText' => 'Spanish',
            'bibles' => [ 'SPASBA' ],
            'code' => 'spa',
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains'
        ];

        // Assert that the result is the expected language config
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_returns_null_when_language_not_found()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock
        $language_config = [
            [
                'id' => 5414,
                'code' => 'eng',
                'value' => 'eng',
                'itemText' => 'English',
                'media_types' => 'text,audio-video,video',
                'bibles' => 'ENGESV'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );
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

        // Configure the options mock to throw an exception
        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willThrowException( new BiblePluginException( 'Options error' ) );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test finding a language when options throw an exception
        $result = $language->find( 'eng' );

        // Assert that the result is false
        $this->assertNull( $result );
    }

    /**
     * @test
     */
    public function it_checks_if_language_is_supported()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'id' => 5414,
                'code' => 'eng',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'id' => 'spa',
                'media_types' => 'text,audio-video,video',
                'code' => 'spa',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

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

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'id' => 5414,
                'media_types' => 'text,audio-video,video',
                'code' => 'eng',
                'source' => 'bible-brains'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'is_default' => true,
                'id' => 'spa',
                'media_types' => 'text,audio-video,video',
                'code' => 'spa',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test getting default language
        $result = $language->default();

        $expected = [
            'value' => 'spa',
            'itemText' => 'Spanish',
            'bibles' => [ 'SPASBA' ],
            'is_default' => true,
            'id' => 'spa',
            'media_types' => 'text,audio-video,video',
            'code' => 'spa',
            'source' => 'bible-brains',
            'default_bible' => 'SPASBA',
        ];

        // Assert that the result is the language marked as default
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_returns_first_language_when_no_default_is_set()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'id' => 5414,
                'code' => 'eng',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'id' => 'spa',
                'code' => 'spa',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test getting default language when none is marked as default
        $result = $language->default();

        $expected = [
            'value' => 'eng',
            'itemText' => 'English',
            'bibles' => [ 'ENGESV' ],
            'id' => 5414,
            'code' => 'eng',
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains',
            'bible_name' => 'English Standard Version®',
            'bibles_data' => [
                [
                    'id' => 'ENGESV',
                    'name' => 'English Standard Version®',
                ]
            ],
        ];

        // Assert that the result is the first language in the config
        $this->assertEquals( $expected, $result );
    }

    /**
     * @test
     */
    public function it_returns_config_default_when_no_languages_configured()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock to return null
        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( null );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test getting default language when no languages are configured
        $result = $language->default();

        // Assert that the result is the default from config
        $this->assertEquals( config( 'options.defaults.language' ), $result );
    }

    /**
     * @test
     */
    public function it_finds_language_or_returns_default()
    {
        // Create mock dependencies
        $options = $this->createMock( OptionsInterface::class );

        // Configure the options mock
        $language_config = [
            [
                'value' => 'eng',
                'itemText' => 'English',
                'bibles' => 'ENGESV',
                'is_default' => true,
                'id' => 5414,
                'code' => 'eng',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ],
            [
                'value' => 'spa',
                'itemText' => 'Spanish',
                'bibles' => 'SPASBA',
                'id' => 'spa',
                'code' => 'spa',
                'media_types' => 'text,audio-video,video',
                'source' => 'bible-brains'
            ]
        ];

        $options->method( 'get' )
            ->with( 'languages', null, true )
            ->willReturn( $language_config );

        // Create the Language service
        $language = new LanguagesSetting( container()->get( Locale::class ), $options, container()->get( LanguageAggregator::class ) );

        // Test finding an existing language
        $result = $language->search_or_default( 'spa' );

        $expected = [
            'value' => 'spa',
            'itemText' => 'Spanish',
            'bibles' => [ 'SPASBA' ],
            'id' => 'spa',
            'code' => 'spa',
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains'
        ];

        // Assert that the result is the found language
        $this->assertEquals( $expected, $result );

        // Test finding a non-existent language
        $result = $language->search_or_default( 'fra' );

        $expected_eng = [
            'value' => 'eng',
            'code' => 'eng',
            'itemText' => 'English',
            'bibles' => [ 'ENGESV' ],
            'is_default' => true,
            'id' => 5414,
            'media_types' => 'text,audio-video,video',
            'source' => 'bible-brains',
            'default_bible' => 'ENGESV',
            'bible_name' => 'English Standard Version®',
            'bibles_data' => [
                [
                    'id' => 'ENGESV',
                    'name' => 'English Standard Version®',
                ]
            ],
        ];

        // Assert that the result is the default language
        $this->assertEquals( $expected_eng, $result );
    }

    /**
     * @test
     */
    public function it_determines_text_direction()
    {
        // Create the Language service
        $locale = new Locale( container()->get( Translations::class ) );

        // Test LTR text
        $ltr_text = 'This is English text';
        $this->assertEquals( 'ltr', $locale->guess_text_direction( $ltr_text ) );

        // Test RTL text (using Hebrew characters)
        $rtl_text = 'שלום עולם'; // "Hello world" in Hebrew
        $this->assertEquals( 'rtl', $locale->guess_text_direction( $rtl_text ) );

        // Test with array of verses
        $verses = [
            [ 'verse_text' => 'This is verse 1' ],
            [ 'verse_text' => 'This is verse 2' ]
        ];
        $this->assertEquals( 'ltr', $locale->guess_text_direction( $verses ) );

        // Test with empty array
        $this->assertEquals( 'auto', $locale->guess_text_direction( [] ) );
    }
}
