<?php

namespace CodeZone\Bible\Sources\USX;

use CodeZone\Bible\Exceptions\BiblePluginException;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class USXBibles
 *
 * Manages USX Bible translations stored in the plugin options.
 * This class is responsible for keeping state on a usx_bibles option,
 * and providing methods to interact with USX Bible translations.
 */
class USXBibles
{
    const CUSTOM_BIBLES_OPTION = 'usx_bibles';
    const FILTER_HOOK = "usx_bibles";

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
        add_filter( namespace_string( self::FILTER_HOOK ), [ $this, 'register' ], 10, 1 );
    }

    /**
     * Register USX Bibles from config
     *
     * @param array $usx_bibles The collection of USX Bibles to be updated.
     * @return array Updated collection of USX Bibles with parsed data.
     */
    public function register( $usx_bibles ) {
        $dirs = config( 'services.usx.dirs', [] );
        $bibles = $this->parse_dirs( $dirs );
        foreach ( $bibles as $bible ) {
            $usx_bibles[ $bible['id'] ] = $bible;
        }
        return $usx_bibles;
    }

    /**
     * Retrieve the custom Bibles option.
     *
     * @return array|null The custom Bibles data as an array, or null if not available.
     */
    public function custom(): ?array {
        return get_plugin_option( self::CUSTOM_BIBLES_OPTION, [] );
    }

    /**
     * Get all available USX Bible.
     *
     * @return array List of all available USX Bible.
     */
    public function all(): array
    {
        return apply_filters(
            namespace_string( self::FILTER_HOOK ),
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
        return $bibles[ $key ] ?? null;
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
    public function add( $key, array $data ): bool
    {
        $bibles = $this->all();

        // Check if the Bible already exists
        if ( isset( $bibles[$key] ) ) {
            return false;
        }

        // Add the Bible
        $bibles[$key] = $data;

        // Save the updated list
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION, $bibles );
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
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION, $bibles );
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
        return set_plugin_option( self::CUSTOM_BIBLES_OPTION, $bibles );
    }

    /**
     * Represents the name of a folder as a string.
     *
     * This string variable is intended to store the name of a specific folder.
     * It can be used for directory management or operations such as creating,
     * renaming, or referencing folders. By default, it is initialized as an
     * empty string, indicating that no folder name is assigned initially.
     *
     * @var string $folder_name
     */
    public function extract_and_add( string $zip_file, $key, ?string $folder_name = null )
    {
        // Create instance up front
        $usx = $this->usx->factory();

        // Extract and bind the directory
        $path = $usx->extract_zip( $zip_file, $folder_name );
        if ( !$path ) {
            return false;
        }

        return $this->add_dir( $key, $path, $usx );
    }

    /**
     * Add a directory to the system by parsing its contents.
     *
     * @param string $key A unique identifier for the directory.
     * @param string $path The file path to the directory.
     * @param USX|null $usx Optional USX instance for parsing.
     * @return string|false The key of the added directory on success, or false on failure.
     */
    public function add_dir( string $key, string $path, ?USX $usx = null ) {
        $data = $this->parse_dir( $path, $usx );
        if ( $this->add( $key, $data ) ) {
            return $key;
        };
        return false;
    }

    /**
     * Parse a directory to extract translation metadata.
     *
     * @param string $path The path to the directory to be parsed.
     * @param USX|null $usx An optional USX object for processing the directory. If not provided, a new instance will be created.
     * @return array An associative array containing the translation metadata: name, ID, ISO code, language name, and path.
     */
    public function parse_dir( string $path, ?USX $usx = null ): array {
        $usx ? $usx->set_directory( $path ) : $usx = $this->usx->factory( $path );
;

        // Read metadata
        $name = $usx->get_translation_name();
        $abbreviation = $usx->get_translation_abbreviation();
        $iso = $usx->get_language_code();
        $language = $usx->get_language_name();

        return [
            'name' => $name,
            'id' => $abbreviation,
            'iso' => $iso,
            'language' => $language,
            'path' => $path,
            'books' => $usx->books()
        ];
    }

    /**
     * Parse the provided directories and process their contents.
     *
     * @param array $paths List of directory paths to be parsed.
     * @param USX|null $usx Optional USX instance for parsing.
     * @return array Results of parsing each directory.
     */
    public function parse_dirs( array $paths, ?USX $usx = null ): array {
        return array_map( function ( $path ) use ( $usx ) {
            return $this->parse_dir( $path, $usx );
        }, $paths);
    }


    /**
     * Search for Bible by name using fuzzy matching.
     *
     * @param string $query The search query.
     * @param int $similarity Minimum similarity threshold (0-100).
     * @return array List of matching Bible.
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
     * Get Bible by language code.
     *
     * @param string $language_code The language code to filter by.
     * @return array List of Bible in the specified language.
     */
    public function for_language( string $language_code ): array
    {
        return $this->where( 'iso', $language_code );
    }

    /**
     * Get Bible by language code.
     *
     * @param string $name The language code to filter by.
     * @return array List of Bible in the specified language.
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
