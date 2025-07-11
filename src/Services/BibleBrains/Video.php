<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\GuzzleHttp\Client;

/**
 * Class Video
 *
 * Responsible for hydrating video content from Bible references.
 * This class processes and enriches video-related data structures
 * with additional information from the Bible API.
 */
class Video {
    /**
     * @var Bibles Bible API service instance
     */
    protected $bibles;

    /**
     * Guzzle client
     */
    protected $http;
    /**
     * Constructor.
     *
     * @param Bibles $bibles Bible API service instance
     */
    public function __construct( Bibles $bibles, ?Client $http = null )
    {
        $this->bibles = $bibles;
        $this->http = $http;
    }

    /**
     * Hydrates an array of content with video information.
     *
     * @param array $content Array of references to hydrate
     * @return array Hydrated content array
     */
    public function hydrate_content( array $content )
    {
        if ( !isset( $content['media'] ) ) {
            return $content;
        }
        $content['media'] = $this->hydrate_media( $content['media'] );
        return $content;
    }

    /**
     * Hydrates a media entry by processing its video component.
     *
     * @param array $media Media array containing the video to be hydrated
     * @return array Media array with the video component hydrated
     */
    public function hydrate_media( array $media )
    {
        if ( !isset( $media['video'] ) ) {
            return $media;
        }

        $media['video'] = $this->hydrate_video( $media['video'] );

        return $media;
    }

    public function hydrate_video( array $video )
    {
        if ( !isset( $video['content'] ) ) {
            return $video;
        }
        $video['content'] = $this->hydrate_video_content( $video['content'] );
        return $video;
    }

    /**
     * Hydrates video content data.
     *
     * @param array $content Video content array to hydrate
     * @return array Hydrated video content array
     */
    public function hydrate_video_content( array $content ): array
    {
        if ( !isset( $content['data'] ) ) {
            return $content;
        }

        $content['data'] = array_map( [ $this, 'hydrate_files' ], $content['data'] );
        return $content;
    }

    /**
     * Hydrates a single video entry with playlist information.
     *
     * @param array $video Video array to hydrate
     * @return array Hydrated video array with playlist information
     */
    public function hydrate_files( array $video ): array
    {
        if ( !isset( $video['path'] ) ) {
            return $video;
        }
        $response = $this->http->get( $video['path'] );

        if ( !$response || $response->getStatusCode() !== 200 ) {
            return $video;
        }
        try {
            $playlist = $this->parse_m3u8( $response->getBody()->getContents(), $video['path'] );
        } catch ( \Exception $e ) {
            return $video;
        }

        $video['files'] = $playlist;

        return $video;
    }

    public function parse_m3u8( $content, $path )
    {
        $lines = explode( "\n", trim( $content ) );
        $videos = [];
        $current_stream = null;

        // Get the base URL by removing 'playlist.m3u8' and everything after it
        $base_url = preg_replace( '/playlist\.m3u8.*$/', '', $path );

        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) ) { continue;
            }

            if ( strpos( $line, '#EXT-X-STREAM-INF:' ) === 0 ) {
                // Parse stream information
                $current_stream = [];
                $attributes = substr( $line, strlen( '#EXT-X-STREAM-INF:' ) );

                // Parse attributes
                // First, extract CODECS attribute separately since it can contain commas
                if ( preg_match( '/CODECS="([^"]+)"/', $attributes, $codecs_match ) ) {
                    $current_stream['CODECS'] = $codecs_match[1];
                    // Remove the CODECS part from attributes to avoid double processing
                    $attributes = str_replace( $codecs_match[0], '', $attributes );
                }

                // Parse remaining attributes
                preg_match_all( '/([^,=]+)=([^,]+)/', $attributes, $matches, PREG_SET_ORDER );
                foreach ( $matches as $match ) {
                    $key = trim( $match[1] );
                    $value = trim( $match[2], '"' );
                    $current_stream[$key] = $value;
                }
            } elseif ( $current_stream !== null && !str_starts_with( $line, '#' ) ) {
                // Construct the full URL by combining the base URL with the quality-specific filename
                $url = $base_url . $line;

                $videos[] = [
                    'bandwidth' => (int) $current_stream['BANDWIDTH'],
                    'resolution' => $current_stream['RESOLUTION'],
                    'codecs' => trim( $current_stream['CODECS'], '"' ),
                    'url' => $url
                ];
                $current_stream = null;
            }
        }

        return $videos;
    }
}
