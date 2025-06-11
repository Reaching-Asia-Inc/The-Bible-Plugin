<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;

/**
 * MediaTypes class represents a collection of media types and their properties.
 */
class MediaTypes {
	/**
	 * Media types and their properties.
	 *
	 * @var array $data
	 * Key-value pairs where the key is the media type and the value is an array of properties.
	 * Properties include the label, fileset types, and group.
	 */
	private $data = [
		'audio' => [
			'key'           => 'audio',
			'label'         => 'Audio',
			'fileset_types' => [ 'audio', 'audio_drama' ],
			'group'         => 'dbp-prod'
		],
//      'video'       => [
//          'key'           => 'video',
//          'label'         => 'Video',
//          'fileset_types' => [ 'video_stream' ],
//          'group'         => 'dbp-vid'
//      ],
		'text'  => [
			'key'           => 'text',
			'label'         => 'Text',
			'fileset_types' => [ "text_format", "text_plain" ],
			'group'         => 'dbp-prod'
		]
	];

	/**
	 * Retrieves the media types supported by the filesets endpoint.
	 *
	 * @return array Returns an array of media types supported by the filesets endpoint.
	 * @throws BibleBrainsException If the request is unsuccessful and returns an error.
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * Retrieves the available media type options.
	 *
	 * This method retrieves an array of media type options based on the media types supported by the filesets endpoint.
	 * It filters the options by a whitelist of predefined media types and adds a "Text" option at the end.
	 * The options are sorted by their value in ascending order.
	 *
	 * @return array Returns an array of available media type options.
	 *               Each option is represented as an associative array with the following keys:
	 *               - 'value': The value of the media type option.
	 *               - 'itemText': The text to be displayed for the media type option.
	 *
	 * @throws BibleBrainsException If the request to retrieve the media types from the filesets endpoint is unsuccessful
	 *                             or returns an error.
	 */
    public function options(): array
    {
        $options = [];
        foreach ($this->all() as $value => $data) {
            $options[] = [
                'value' => $value,
                'itemText' => $data['label']
            ];
        }
        return $options;
	}

    /**
     * Find the first occurrence of a given media type in the collection.
     *
     * @param string $media_type The media type to search for.
     *
     * @throws BibleBrainsException If the media type is not found in the collection.
     */
    public function find($media_type): array {
        $all = $this->all();
        $result = array_filter(
            $all,
            function ($data, $value) use ($media_type) {
                return $value === $media_type;
            },
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($result)) {
            throw new BibleBrainsException(esc_html("Invalid media type: {$media_type}."));
        }

        return reset($result);
    }

    /**
     * Check if a given media type exists in the collection.
     *
     * @param string $media_type The media type to check for existence.
     *
     * @return bool True if the media type exists, false otherwise.
     */
    public function exists(string $media_type): bool {
        $filtered = array_filter(
            $this->all(),
            function ($data, $value) use ($media_type) {
                return $value === $media_type;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return !empty($filtered);
    }

}
