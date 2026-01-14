<?php

namespace CodeZone\Bible\Sources\Paratext;

use CodeZone\Bible\Settings\LocalBiblesSetting;
use CodeZone\Bible\Sources\ArraySource;
use function CodeZone\Bible\config;
use function CodeZone\Bible\namespace_string;
use function CodeZone\Bible\container;

/**
 * Class ParatextBibles
 *
 * Manages Paratext Bible translations stored in the plugin options.
 * This class is responsible for keeping state on the `local_bibles` option
 * and providing methods to interact with Paratext Bible translations.
 */
class ParatextBibles extends ArraySource
{
    const FILTER_HOOK = "local_bibles";
    protected $paratext;
    protected $setting;
    protected $extractor;

    /**
     * Constructor.
     *
     * @param Paratext $paratext The Paratext service instance.
     */
    public function __construct( Paratext $paratext, LocalBiblesSetting $setting, ?ParatextExtractor $extractor = null )
    {
        $this->paratext = $paratext;
        $this->setting = $setting;
        $this->extractor = $extractor ?: container()->get( ParatextExtractor::class );
        add_filter( namespace_string( self::FILTER_HOOK ), [ $this, 'register' ], 10, 1 );
    }

    /**
     * Register and merge custom Bibles with parsed Paratext and USX Bibles.
     *
     * @param array $local_bibles Array of custom Bibles to be merged.
     * @return array The merged array of custom Bibles and parsed Bibles.
     */
    public function register( $local_bibles ) {
        $paratext_bibles = $this->parse_dirs( config( 'services.paratext.dirs', [] ) );

        foreach ( $paratext_bibles as $bible ) {
            if ( !isset( $local_bibles[$bible['id']] ) ) {
                $local_bibles[$bible['id']] = $bible;
            }
        }

        return $local_bibles;
    }

    /**
     * Retrieve all settings.
     *
     * @return array List of all settings after applying filters
     */
    public function all(): array
    {
        return apply_filters(
            namespace_string( static::FILTER_HOOK ),
            $this->setting->all()
        );
    }


    /**
     * Parse a Paratext Bible using its key.
     *
     * @param string $key The key of the Bible to parse.
     * @return array The parsed Bible data as an array.
     */
    public function parse( string $key ): array
    {
        return $this->paratext( $key )
            ->to_array();
    }

    /**
     * Retrieve a Paratext driver instance based on the specified key.
     *
     * @param string|null $key The key identifying the desired Bible.
     * @return Paratext|null The Paratext instance if found, or null if no matching Bible is found.
     */
    public function paratext( ?string $key = null ): ?Paratext
    {
        if ( !$key ) {
            return $this->paratext->factory();
        }

        $bible = $this->find( $key );

        if ( !$bible ) {
            return null;
        }

        // Set the directory for the Paratext instance
        return $this->paratext->factory( $bible['path'] );
    }

    /**
     * Extracts a zip file, retrieves its contents, and adds the extracted directory.
     *
     * @param string $zip_file Path to the zip file to be extracted.
     * @param string|null $folder_name Optional folder name to use when extracting.
     * @return string|false The key of the added directory on success, or false on failure.
     * @throws \Exception
     */
    public function extract_and_add( string $zip_file, ?string $folder_name = null )
    {
        // Use the Paratext instance to extract zip to keep backward compatibility with tests
        $paratext = $this->paratext->factory();
        $path = $paratext->extract_zip( $zip_file, $folder_name );
        $key = $paratext->get_translation_abbreviation();
        if ( !$path ) {
            return false;
        }
        return $this->add_dir( $key, $path, $paratext );
    }

    /**
     * Add a directory to the system by parsing its contents.
     *
     * @param string $key A unique identifier for the directory.
     * @param string $path The file path to the directory.
     * @param Paratext|null $paratext Optional Paratext instance for parsing.
     * @return string|false The key of the added directory on success, or false on failure.
     */
    public function add_dir( string $key, string $path, ?Paratext $paratext = null ) {
        $data = $this->parse_dir( $path, $paratext );
        if ( $this->setting->add( $key, $data ) ) {
            return $key;
        };
        return false;
    }

    /**
     * Parse a directory to extract translation metadata.
     *
     * @param string $path The path to the directory to be parsed.
     * @param Paratext|null $paratext An optional Paratext object for processing the directory. If not provided, a new instance will be created.
     * @return array An associative array containing the translation metadata: name, ID, ISO code, language name, and path.
     */
    public function parse_dir( string $path, ?Paratext $paratext = null ): array {
        $paratext ? $paratext->set_directory( $path ) : $paratext = $this->paratext->factory( $path );
;

        // Read metadata
        $name = $paratext->get_translation_name();
        $abbreviation = $paratext->get_translation_abbreviation();
        $iso = $paratext->get_language_code();
        $language = $paratext->get_language_name();

        return [
            'name' => $name,
            'id' => $abbreviation,
            'iso' => $iso,
            'language' => $language,
            'path' => $path,
            'books' => $paratext->books()
        ];
    }

    /**
     * Convert the object data into an array representation.
     *
     * @return array The parsed array representation of the object data.
     */
    public function to_array(): array {
        return $this->parse_dirs( $this->all() );
    }

    /**
     * Parse an array of directory paths and convert their contents into an array format.
     *
     * @param array $paths An array of directory paths to be parsed.
     * @param Paratext|null $paratext Optional Paratext instance for processing directories; if null, a factory method is used.
     * @return array An array containing the parsed directory contents.
     */
    public function parse_dirs( array $paths, ?Paratext $paratext = null ): array {
        return array_map( function ( $path ) use ( $paratext ) {
            $p = $paratext ? $paratext->set_directory( $path ) : $this->paratext->factory( $path );
            return $p->to_array();
        }, $paths);
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
}
