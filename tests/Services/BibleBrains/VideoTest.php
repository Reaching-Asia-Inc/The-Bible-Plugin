<?php

namespace Tests\Services\BibleBrains;

use CodeZone\Bible\GuzzleHttp\Client;
use CodeZone\Bible\GuzzleHttp\Psr7\Response;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\Services\BibleBrains\Video;
use Tests\TestCase;

class VideoTest extends TestCase
{
    /**
     * @test
     */
    public function it_hydrates_content_with_video_information()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test content
        $content = [
            'reference' => 'John 3:16',
            'media' => [
                'video' => [
                    'content' => [
                        'data' => [
                            [
                                'path' => 'https://example.com/video/playlist.m3u8'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Configure the HTTP client mock
        $response = new Response( 200, [], "EXTM3U\n#EXT-X-STREAM-INF:BANDWIDTH=1000000,RESOLUTION=1280x720,CODECS=\"avc1.4d001f,mp4a.40.2\"\nstream_720p.m3u8" );
        $http->method( 'get' )
            ->with( 'https://example.com/video/playlist.m3u8' )
            ->willReturn( $response );

        // Hydrate the content
        $result = $video->hydrate_content( $content );

        // Assert that the content was hydrated with playlist information
        $this->assertArrayHasKey( 'media', $result );
        $this->assertArrayHasKey( 'video', $result['media'] );
        $this->assertArrayHasKey( 'content', $result['media']['video'] );
        $this->assertArrayHasKey( 'data', $result['media']['video']['content'] );

        $video_data = $result['media']['video']['content']['data'][0];
        $this->assertArrayHasKey( 'playlist', $video_data );
        $this->assertIsArray( $video_data['playlist'] );

        // Check the playlist structure
        $playlist = $video_data['playlist'][0];
        $this->assertArrayHasKey( 'bandwidth', $playlist );
        $this->assertArrayHasKey( 'resolution', $playlist );
        $this->assertArrayHasKey( 'codecs', $playlist );
        $this->assertArrayHasKey( 'url', $playlist );

        // Check the playlist values
        $this->assertEquals( 1000000, $playlist['bandwidth'] );
        $this->assertEquals( '1280x720', $playlist['resolution'] );
        $this->assertEquals( 'avc1.4d001f,mp4a.40.2', $playlist['codecs'] );
        $this->assertEquals( 'https://example.com/video/stream_720p.m3u8', $playlist['url'] );
    }

    /**
     * @test
     */
    public function it_returns_original_content_when_no_media_key()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test content without media key
        $content = [
            'reference' => 'John 3:16'
        ];

        // Hydrate the content
        $result = $video->hydrate_content( $content );

        // Assert that the original content is returned unchanged
        $this->assertEquals( $content, $result );
    }

    /**
     * @test
     */
    public function it_returns_original_media_when_no_video_key()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test content with media but no video
        $content = [
            'reference' => 'John 3:16',
            'media' => [
                'text' => [
                    'content' => 'For God so loved the world...'
                ]
            ]
        ];

        // Hydrate the content
        $result = $video->hydrate_content( $content );

        // Assert that the original content is returned unchanged
        $this->assertEquals( $content, $result );
    }

    /**
     * @test
     */
    public function it_returns_original_video_when_no_content_key()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test content with video but no content
        $content = [
            'reference' => 'John 3:16',
            'media' => [
                'video' => [
                    'metadata' => 'Some metadata'
                ]
            ]
        ];

        // Hydrate the content
        $result = $video->hydrate_content( $content );

        // Assert that the original content is returned unchanged
        $this->assertEquals( $content, $result );
    }

    /**
     * @test
     */
    public function it_returns_original_content_when_http_request_fails()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test content
        $content = [
            'reference' => 'John 3:16',
            'media' => [
                'video' => [
                    'content' => [
                        'data' => [
                            [
                                'path' => 'https://example.com/video/playlist.m3u8'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Configure the HTTP client mock to return a failed response
        $http->method( 'get' )
            ->with( 'https://example.com/video/playlist.m3u8' )
            ->willReturn( new Response( 404 ) );

        // Hydrate the content
        $result = $video->hydrate_content( $content );

        // Assert that the content was not hydrated with playlist information
        $this->assertArrayHasKey( 'media', $result );
        $this->assertArrayHasKey( 'video', $result['media'] );
        $this->assertArrayHasKey( 'content', $result['media']['video'] );
        $this->assertArrayHasKey( 'data', $result['media']['video']['content'] );

        $video_data = $result['media']['video']['content']['data'][0];
        $this->assertArrayNotHasKey( 'playlist', $video_data );
    }

    /**
     * @test
     */
    public function it_can_parse_m3u8_playlist()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test m3u8 content
        $m3u8_content = <<<EOT
#EXTM3U
#EXT-X-STREAM-INF:BANDWIDTH=1000000,RESOLUTION=1280x720,CODECS="avc1.4d001f,mp4a.40.2"
stream_720p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=500000,RESOLUTION=854x480,CODECS="avc1.4d001e,mp4a.40.2"
stream_480p.m3u8
#EXT-X-STREAM-INF:BANDWIDTH=250000,RESOLUTION=640x360,CODECS="avc1.4d001e,mp4a.40.2"
stream_360p.m3u8
EOT;

        // Parse the m3u8 content
        $result = $video->parse_m3u8( $m3u8_content, 'https://example.com/video/playlist.m3u8' );

        // Assert that the result is an array with 3 entries
        $this->assertIsArray( $result );
        $this->assertCount( 3, $result );

        // Check the first stream
        $this->assertEquals( 1000000, $result[0]['bandwidth'] );
        $this->assertEquals( '1280x720', $result[0]['resolution'] );
        $this->assertEquals( 'avc1.4d001f,mp4a.40.2', $result[0]['codecs'] );
        $this->assertEquals( 'https://example.com/video/stream_720p.m3u8', $result[0]['url'] );

        // Check the second stream
        $this->assertEquals( 500000, $result[1]['bandwidth'] );
        $this->assertEquals( '854x480', $result[1]['resolution'] );
        $this->assertEquals( 'avc1.4d001e,mp4a.40.2', $result[1]['codecs'] );
        $this->assertEquals( 'https://example.com/video/stream_480p.m3u8', $result[1]['url'] );

        // Check the third stream
        $this->assertEquals( 250000, $result[2]['bandwidth'] );
        $this->assertEquals( '640x360', $result[2]['resolution'] );
        $this->assertEquals( 'avc1.4d001e,mp4a.40.2', $result[2]['codecs'] );
        $this->assertEquals( 'https://example.com/video/stream_360p.m3u8', $result[2]['url'] );
    }

    /**
     * @test
     */
    public function it_handles_empty_m3u8_playlist()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Parse an empty m3u8 content
        $result = $video->parse_m3u8( '', 'https://example.com/video/playlist.m3u8' );

        // Assert that the result is an empty array
        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }

    /**
     * @test
     */
    public function it_handles_invalid_m3u8_playlist()
    {
        // Create mock dependencies
        $bibles = $this->createMock( Bibles::class );
        $http = $this->createMock( Client::class );

        // Create the Video service
        $video = new Video( $bibles, $http );

        // Create test invalid m3u8 content
        $m3u8_content = <<<EOT
#EXTM3U
This is not a valid m3u8 playlist
EOT;

        // Parse the invalid m3u8 content
        $result = $video->parse_m3u8( $m3u8_content, 'https://example.com/video/playlist.m3u8' );

        // Assert that the result is an empty array
        $this->assertIsArray( $result );
        $this->assertEmpty( $result );
    }
}
