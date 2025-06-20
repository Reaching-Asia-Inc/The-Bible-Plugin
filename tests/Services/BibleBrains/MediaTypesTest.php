<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\MediaTypes;
use Tests\TestCase;

class MediaTypesTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_media_types()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Get all media types
        $result = $media_types->all();

        // Assert that the result is an array
        $this->assertIsArray( $result );

        // Assert that the result contains the expected media types
        $this->assertArrayHasKey( 'audio', $result );
        $this->assertArrayHasKey( 'video', $result );
        $this->assertArrayHasKey( 'text', $result );

        // Check structure of a media type
        $audio = $result['audio'];
        $this->assertArrayHasKey( 'key', $audio );
        $this->assertArrayHasKey( 'label', $audio );
        $this->assertArrayHasKey( 'fileset_types', $audio );
        $this->assertArrayHasKey( 'group', $audio );

        // Check values
        $this->assertEquals( 'audio', $audio['key'] );
        $this->assertEquals( 'Audio', $audio['label'] );
        $this->assertIsArray( $audio['fileset_types'] );
        $this->assertContains( 'audio', $audio['fileset_types'] );
    }

    /**
     * @test
     */
    public function it_returns_media_type_options()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Get media type options
        $result = $media_types->options();

        // Assert that the result is an array
        $this->assertIsArray( $result );

        // Assert that the result contains options for each media type
        $this->assertCount( 3, $result ); // audio, video, text

        // Check structure of an option
        $option = $result[0];
        $this->assertArrayHasKey( 'value', $option );
        $this->assertArrayHasKey( 'itemText', $option );

        // Check that all expected media types are present
        $values = array_column( $result, 'value' );
        $this->assertContains( 'audio', $values );
        $this->assertContains( 'video', $values );
        $this->assertContains( 'text', $values );

        // Check that labels are correct
        $text_option = array_filter($result, function ( $option ) {
            return $option['value'] === 'text';
        });
        $text_option = reset( $text_option );
        $this->assertEquals( 'Text', $text_option['itemText'] );
    }

    /**
     * @test
     */
    public function it_finds_media_type_by_key()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Find a media type
        $result = $media_types->find( 'audio' );

        // Assert that the result is an array
        $this->assertIsArray( $result );

        // Check structure and values
        $this->assertEquals( 'audio', $result['key'] );
        $this->assertEquals( 'Audio', $result['label'] );
        $this->assertIsArray( $result['fileset_types'] );
        $this->assertContains( 'audio', $result['fileset_types'] );
    }

    /**
     * @test
     */
    public function it_throws_exception_for_invalid_media_type()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Expect an exception when finding an invalid media type
        $this->expectException( BibleBrainsException::class );

        // Try to find an invalid media type
        $media_types->find( 'invalid_media_type' );
    }

    /**
     * @test
     */
    public function it_checks_if_media_type_exists()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Check valid media types
        $this->assertTrue( $media_types->exists( 'audio' ) );
        $this->assertTrue( $media_types->exists( 'video' ) );
        $this->assertTrue( $media_types->exists( 'text' ) );

        // Check invalid media type
        $this->assertFalse( $media_types->exists( 'invalid_media_type' ) );
    }

    /**
     * @test
     */
    public function it_has_correct_fileset_types_for_each_media_type()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Get all media types
        $all_types = $media_types->all();

        // Check audio fileset types
        $this->assertContains( 'audio', $all_types['audio']['fileset_types'] );
        $this->assertContains( 'audio_drama', $all_types['audio']['fileset_types'] );

        // Check video fileset types
        $this->assertContains( 'video_stream', $all_types['video']['fileset_types'] );

        // Check text fileset types
        $this->assertContains( 'text_format', $all_types['text']['fileset_types'] );
        $this->assertContains( 'text_plain', $all_types['text']['fileset_types'] );
    }

    /**
     * @test
     */
    public function it_has_correct_groups_for_each_media_type()
    {
        // Create the MediaTypes service
        $media_types = new MediaTypes();

        // Get all media types
        $all_types = $media_types->all();

        // Check groups
        $this->assertEquals( 'dbp-prod', $all_types['audio']['group'] );
        $this->assertEquals( 'dbp-vid', $all_types['video']['group'] );
        $this->assertEquals( 'dbp-prod', $all_types['text']['group'] );
    }
}
