<?php

namespace CodeZone\Bible\Services\BibleBrains\Api;

/**
 * Class Languages
 *
 * Extends the ApiService class to handle operations related to languages.
 * Provides methods to process language data into formats suitable for various use cases.
 */
class Languages extends ApiService {
    protected $endpoint = 'languages';
    protected array $default_options = [
        'include_translations' => false,
        'include_all_names'    => false,
        'limit'                => 500,
    ];

    /**
     * Maps an option record to an associative array.
     *
     * @param array $record The option record to map.
     *
     * @return array The mapped option as an associative array.
     */
    public function map_option( array $record ): array {
        return [
            'value'         => (string) $record['id'],
            'language_code' => (string) $record['iso'],
            'itemText'      => (string) $record['name'],
        ];
    }
}
