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
    public function __construct(Bibles $bibles, Client $http = null)
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
    public function hydrate_content(array $content)
    {
        if (!isset($content['media'])) {
            return $content;
        }
        $content['media'] = $this->hydrate_media($content['media']);
        return $content;
    }

    /**
     * Hydrates a single reference with video content.
     *
     * @param array $reference Reference array to hydrate
     * @return array Hydrated reference array
     */
    public function hydrate_media(array $media)
    {
        if (!isset($media['video'])) {
            return $media;
        }

        $media['video'] = $this->hydrate_video($media['video']);

        return $media;
    }

    public function hydrate_video(array $video)
    {
        if (!isset($video['content'])) {
            return $video;
        }
        $video['content'] = $this->hydrate_video_content($video['content']);
        return $video;
    }

    /**
     * Hydrates video content data.
     *
     * @param array $content Video content array to hydrate
     * @return array Hydrated video content array
     */
    public function hydrate_video_content(array $content): array
    {
        if (!isset($content['data'])) {
            return $content;
        }

        $content['data'] = array_map([$this, 'hydrate_files'], $content['data']);
        return $content;

    }

    /**
     * Hydrates a single video entry with playlist information.
     *
     * @param array $video Video array to hydrate
     * @return array Hydrated video array with playlist information
     */
    public function hydrate_files(array $video): array
    {
        if (!isset($video['path'])) {
            return $video;
        }
        $response = $this->http->get($video['path']);

        if (!$response || $response->getStatusCode() !== 200) {
            return $video;
        }
        try {
            $playlist = $this->parse_m3u8($response->getBody()->getContents(), $video['path']);
        } catch (\Exception $e) {
            return $video;
        }

        $video['playlist'] = $playlist;

        return $video;
    }

    function parse_m3u8($content, $path)
    {
        $lines = explode("\n", trim($content));
        $videos = [];
        $currentStream = null;

        // Get the base URL by removing 'playlist.m3u8' and everything after it
        $baseUrl = preg_replace('/playlist\.m3u8.*$/', '', $path);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (strpos($line, '#EXT-X-STREAM-INF:') === 0) {
                // Parse stream information
                $currentStream = [];
                $attributes = substr($line, strlen('#EXT-X-STREAM-INF:'));

                // Parse attributes
                preg_match_all('/([^,=]+)=([^,]+)/', $attributes, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = trim($match[1]);
                    $value = trim($match[2], '"');
                    $currentStream[$key] = $value;
                }
            } elseif ($currentStream !== null && !str_starts_with($line, '#')) {
                // Construct the full URL by combining the base URL with the quality-specific filename
                $url = $baseUrl . $line;

                $videos[] = [
                    'bandwidth' => (int)$currentStream['BANDWIDTH'],
                    'resolution' => $currentStream['RESOLUTION'],
                    'codecs' => trim($currentStream['CODECS'], '"'),
                    'url' => $url
                ];
                $currentStream = null;
            }
        }

        return $videos;
    }
}
