<?php

namespace CodeZone\Bible\Services;

use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\set_plugin_option;
use CodeZone\Bible\Exceptions\BiblePluginException;

/**
 * Class USXBibles
 *
 * Manages USX Bible translations stored in the plugin options.
 * This class is responsible for keeping state on a usx_bibles option,
 * and providing methods to interact with USX Bible translations.
 */
class USXBibles
{
    const CUSTOM_BIBLES_OPTION_KEY = 'usx_bibles';
    const BIBLES_FILTER_KEY = "usx_bibles";

    /**
     * The USX service instance.
     *
     * @var USX
     */
    protected $usx;

    /**
     * Constructor.
     *
     * @param USX $usx The USX service instance.
     */
    public function __construct( USX $usx )
    {
        $this->usx = $usx;
    }

    public function custom(): ?array {
        return get_plugin_option( self::CUSTOM_BIBLES_OPTION_KEY, [] );
    }

    /**
     * Get all available USX Bibles.
     *
     * @return array List of all available USX Bibles.
     */
    public function all(): array
    {
        return apply_filters(
            namespace_string(self::BIBLES_FILTER_KEY),
            $this->custom()
        );
    }

    /**
     * Find a USX Bible by its key.
     *
     * @param string $key The Bible key to find.
     * @return array|null The Bible data or null if not found.
     */
    public function find( string $key )
    {
        $bibles = $this->all();
        return $bibles[$key] ?? null;
    }

    /**
     * Parse a USX Bible using its key.
     *
     * @param string $key The key of the Bible to parse.
     * @return array The parsed Bible data as an array.
     */
    public function parse( string $key ): array {
        $bible = $this->find( $key );
        return $this->usx
            ->factory( $bible )
            ->all();
    }

    /**
     * Get a USX instance for a specific Bible.
     *
     * @param string $key The Bible key.
     * @return USX The USX instance for the Bible.
     * @throws BiblePluginException If the Bible is not found.
     */
    public function usx( string $key ): ?USX
    {
        $bible = $this->find( $key );

        if ( !$bible ) {
            return null;
        }

        // Set the directory for the USX instance
        return $this->usx->factory( $bible['path'] );
    }

    /**
     * Add a new USX Bible.
     *
     * @param string $key The Bible key.
     * @param array $data The Bible data.
     * @return bool True on success, false on failure.
     */
    public function add( string $key, array $data ): bool
    {
        $bibles = $this->all();

        // Check if the Bible already exists
        if ( isset( $bibles[$key] ) ) {
            return false;
        }

        // Add the Bible
        $bibles[$key] = $data;

        // Save the updated list
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION_KEY, $bibles );
    }

    /**
     * Update an existing USX Bible.
     *
     * @param string $key The Bible key.
     * @param array $data The updated Bible data.
     * @return bool True on success, false on failure.
     */
    public function update( string $key, array $data ): bool
    {
        $bibles = $this->all();

        // Check if the Bible exists
        if ( !isset( $bibles[$key] ) ) {
            return false;
        }

        // Update the Bible
        $bibles[$key] = array_merge( $bibles[$key], $data );

        // Save the updated list
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION_KEY, $bibles );
    }

    /**
     * Delete a USX Bible.
     *
     * @param string $key The Bible key.
     * @return bool True on success, false on failure.
     */
    public function delete( string $key ): bool
    {
        $bibles = $this->all();

        // Check if the Bible exists
        if ( !isset( $bibles[$key] ) ) {
            return false;
        }

        // Remove the Bible
        unset( $bibles[$key] );

        // Save the updated list
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION_KEY, $bibles );
    }

    /**
     * Extract a USX Bible from a zip file and add it to the list.
     *
     * @param string $zip_file Path to the zip file.
     * @param string $key The Bible key (optional, will use translation abbreviation if not provided).
     * @return string|bool The key of the added Bible or false on failure.
     */
    public function extract_and_add( string $zip_file, string $key = '' )
    {
        // Create instance up front
        $usx = $this->usx->factory();

        // Extract and bind the directory
        $path = $usx->extract_zip( $zip_file, $key );
        if ( !$path ) {
            return false;
        }

        // Safe: set directory once after zip, before metadata access
        $usx->set_directory( $path );

        // Read metadata
        $name = $usx->get_translation_name();
        $abbreviation = $usx->get_translation_abbreviation();
        $iso = $usx->get_language_code();
        $language = $usx->get_language_name();

        if ( empty( $key ) ) {
            $key = $abbreviation;
        }

        $data = [
            'name' => $name,
            'id' => $abbreviation,
            'iso' => $iso,
            'language' => $language,
            'path' => $path
        ];

        return $this->add( $key, $data ) ? $key : false;
    }

    /**
     * Search for Bibles by name using fuzzy matching.
     *
     * @param string $query The search query.
     * @param int $similarity Minimum similarity threshold (0-100).
     * @return array List of matching Bibles.
     */
    public function search( string $query, int $similarity = 60 ): array
    {
        $bibles = $this->all();
        $results = [];

        foreach ( $bibles as $key => $bible ) {
            similar_text( strtolower( $query ), strtolower( $bible['name'] ), $percent );
            if ( $percent >= $similarity ) {
                $results[$key] = $bible;
            }
        }

        return $results;
    }

    /**
     * Get Bibles by language code.
     *
     * @param string $language_code The language code to filter by.
     * @return array List of Bibles in the specified language.
     */
    public function for_language( string $language_code ): array
    {
        return $this->where( 'language', $language_code );
    }

    /**
     * Get Bibles by language code.
     *
     * @param string $name The language code to filter by.
     * @return array List of Bibles in the specified language.
     */
    public function where( string $name, $value ): array
    {
        $bibles = $this->all();
        $results = [];

        foreach ( $bibles as $key => $bible ) {
            if ( isset( $bible[$name] ) && $bible[$name] === $value ) {
                $results[$key] = $bible;
            }
        }

        return $results;
    }
}
