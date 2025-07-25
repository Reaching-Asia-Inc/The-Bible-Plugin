<?php

namespace CodeZone\Bible\Sources\USX;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\Books;
use ZipArchive;
use function CodeZone\Bible\container;
use function CodeZone\Bible\namespace_string;

/**
 * Service for parsing and retrieving data from USX (Unified Scripture XML) files.
 *
 * USX is the XML format used by Paratext for Bible translation. This service provides
 * methods for parsing these files and extracting the relevant Bible data.
 */
class USX
{
    private $directory;
    private $metadata = [];
    private $styles = [];
    private $versification = [];
    private $license = [];
    private Books $books;

    /**
     * Constructor method to initialize the class with a directory.
     *
     * @param string $directory The directory path to be set. Defaults to an empty string.
     * @return void
     */
    public function __construct( string $directory = '', Books $books = null )
    {
        $this->books = $books ?? container()->get( Books::class );
        $this->set_directory( $directory );
;
    }

    /**
     * Creates and returns a new instance of the current class.
     *
     * @param string $directory An optional directory path to be set during instantiation.
     * @return static A new instance of the current class, initialized with the provided directory.
     */
    public function factory( $directory = '' )
    {
        return new static( $directory );
    }

    /**
     * Sets the directory path for the instance and loads metadata files if the directory is valid.
     *
     * @param string $directory The directory path to be set.
     * @return void
     */
    public function set_directory( string $directory ): void
    {
        $this->directory = $directory;

        if ( !empty( $directory ) && is_dir( $directory ) ) {
            $this->load_metadata_files();
        }
    }

    /**
     * Loads metadata files from the specified directory and parses their content.
     *
     * This method processes multiple files such as metadata, styles, versification,
     * and license files, and stores their parsed contents into corresponding properties.
     *
     * @return void
     */
    private function load_metadata_files(): void
    {
        $metadata_file = $this->directory . '/metadata.xml';
        if ( file_exists( $metadata_file ) ) {
            $this->metadata = $this->parse_metadata_file( $metadata_file );
        }

        $styles_file = $this->directory . '/styles.xml';
        if ( file_exists( $styles_file ) ) {
            $this->styles = $this->parse_styles_file( $styles_file );
        }

        $versification_file = $this->directory . '/versification.vrs';
        if ( file_exists( $versification_file ) ) {
            $this->versification = $this->parse_versification_file( $versification_file );
        }

        $license_file = $this->directory . '/license.xml';
        if ( file_exists( $license_file ) ) {
            $this->license = $this->parse_license_file( $license_file );
        }
    }

    /**
     * Parses the given metadata file to extract relevant information.
     *
     * @param string $file_path The file path of the metadata XML file to parse.
     * @return array An associative array containing the parsed metadata. Returns an empty array if the file does not exist or cannot be parsed.
     */
    private function parse_metadata_file( string $file_path ): array
    {
        $metadata = [];

        if ( !file_exists( $file_path ) ) {
            return $metadata;
        }

        $xml = simplexml_load_file( $file_path );
        if ( !$xml ) {
            return $metadata;
        }

        if ( isset( $xml->identification ) ) {
            $metadata['name'] = (string) $xml->identification->name;
            $metadata['abbreviation'] = (string) $xml->identification->abbreviation;
            $metadata['description'] = (string) $xml->identification->description;
            $metadata['scope'] = (string) $xml->identification->scope;
        }

        if ( isset( $xml->language ) ) {
            $metadata['language'] = [
                'iso' => (string) $xml->language->iso,
                'name' => (string) $xml->language->name,
                'script' => (string) $xml->language->script,
                'direction' => (string) $xml->language->scriptDirection
            ];
        }

        if ( isset( $xml->versification ) ) {
            $metadata['versification'] = (string) $xml->versification;
        }

        if ( isset( $xml->type ) ) {
            $metadata['type'] = (string) $xml->type;
        }

        return $metadata;
    }

    /**
     * Parses a styles XML file and extracts style definitions into an associative array.
     *
     * @param string $file_path The path to the XML file containing style definitions.
     * @return array An associative array of styles with their corresponding attributes.
     *               Returns an empty array if the file does not exist or cannot be parsed.
     */
    private function parse_styles_file( string $file_path ): array
    {
        $styles = [];

        if ( !file_exists( $file_path ) ) {
            return $styles;
        }

        $xml = simplexml_load_file( $file_path );
        if ( !$xml ) {
            return $styles;
        }

        foreach ( $xml->style as $style ) {
            $id = (string) $style['id'];
            $styles[$id] = [
                'name' => (string) $style['name'],
                'color' => (string) $style['color'],
                'fontsize' => (int) $style['fontsize'],
                'bold' => (string) $style['bold'] === 'true',
                'italic' => (string) $style['italic'] === 'true',
                'underline' => (string) $style['underline'] === 'true'
            ];
        }

        return $styles;
    }

    /**
     * Parses a versification file to extract book, chapter, and verse range mappings.
     *
     * @param string $file_path The file path of the versification text file to parse.
     * @return array An associative array containing books as keys, each having chapters as keys, and verse ranges as values.
     */
    private function parse_versification_file( string $file_path ): array
    {
        $versification = [];

        if ( !file_exists( $file_path ) ) {
            return $versification;
        }

        $content = file_get_contents( $file_path );
        if ( $content === false ) {
            return $versification;
        }

        $lines = explode( "\n", $content );
        $current_book = null;

        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) || $line[0] === '#' ) {
                continue;
            }

            $parts = preg_split( '/\s+/', $line, 2 );
            if ( count( $parts ) === 2 ) {
                $book = $parts[0];
                $ranges = $parts[1];

                if ( !isset( $versification[$book] ) ) {
                    $versification[$book] = [];
                }

                preg_match_all( '/(\d+):(\d+-\d+)/', $ranges, $matches, PREG_SET_ORDER );
                foreach ( $matches as $match ) {
                    $chapter = (int) $match[1];
                    $verse_range = $match[2];

                    if ( !isset( $versification[$book][$chapter] ) ) {
                        $versification[$book][$chapter] = [];
                    }

                    $versification[$book][$chapter][] = $verse_range;
                }
            }
        }

        return $versification;
    }

    /**
     * Parses a license file and extracts its details.
     *
     * @param string $file_path The file path of the license file to be parsed.
     * @return array An associative array containing the license details such as title, type, URL, statement, and permissions.
     */
    private function parse_license_file( string $file_path ): array
    {
        $license = [];

        if ( !file_exists( $file_path ) ) {
            return $license;
        }

        $xml = simplexml_load_file( $file_path );
        if ( !$xml ) {
            return $license;
        }

        $license['title'] = (string) $xml->title;
        $license['type'] = (string) $xml->type;
        $license['url'] = (string) $xml->url;
        $license['statement'] = (string) $xml->statement;

        if ( isset( $xml->permissions ) ) {
            $license['permissions'] = [];
            foreach ( $xml->permissions->permission as $permission ) {
                $license['permissions'][] = (string) $permission;
            }
        }

        return $license;
    }

    /**
     * Retrieves the currently set directory path.
     *
     * @return string The directory path.
     */
    public function get_directory(): string
    {
        return $this->directory;
    }

    /**
     * Retrieves the metadata information associated with the class.
     *
     * @return array The metadata array.
     */
    public function get_metadata(): array
    {
        return $this->metadata;
    }

    /**
     * Retrieves the translation name from the metadata.
     *
     * @return string The translation name if available, otherwise an empty string.
     */
    public function get_translation_name(): string
    {
        return $this->metadata['name'] ?? '';
    }

    /**
     * Retrieves the translation abbreviation from the metadata.
     *
     * @return string The translation abbreviation if available, or an empty string if not set.
     */
    public function get_translation_abbreviation(): string
    {
        return $this->metadata['abbreviation'] ?? '';
    }

    /**
     * Retrieves the language metadata.
     *
     * @return array The language metadata as an associative array. Returns an empty array if not set.
     */
    public function get_language(): array
    {
        return $this->metadata['language'] ?? [];
    }

    /**
     * Retrieves the language code from metadata.
     *
     * @return string The ISO language code if available, otherwise an empty string.
     */
    public function get_language_code(): string
    {
        return $this->metadata['language']['iso'] ?? '';
    }

    /**
     * Retrieves the name of the language from the metadata.
     *
     * @return string The name of the language or an empty string if not available.
     */
    public function get_language_name(): string
    {
        return $this->metadata['language']['name'] ?? '';
    }

    /**
     * Retrieves the scope value from the metadata.
     *
     * @return string The scope value, or an empty string if not set.
     */
    public function get_scope(): string
    {
        return $this->metadata['scope'] ?? '';
    }

    /**
     * Retrieves the list of styles.
     *
     * @return array The array of styles.
     */
    public function get_styles(): array
    {
        return $this->styles;
    }

    /**
     * Retrieves the versification data.
     *
     * @return array The versification information.
     */
    public function get_versification(): array
    {
        return $this->versification;
    }

    /**
     * Retrieves the license information.
     *
     * @return array The license data.
     */
    public function get_license(): array
    {
        return $this->license;
    }

    /**
     * Extracts the contents of a ZIP file to a specified directory, processes metadata if available,
     * and organizes the extracted files under a specified or default translation name.
     *
     * @param string $zip_file The path to the ZIP file to be extracted.
     * @param ?string $folder_name Optional. The name of the translation directory. If not provided,
     *                                  it will be determined from the metadata or the ZIP file name.
     * @return string|false Returns the path to the extracted translation directory on success,
     *                      or false if the extraction fails.
     */
    public function extract_zip( string $zip_file, string $folder_name = null )
    {
        if ( !file_exists( $zip_file ) ) {
            return false;
        }

        $zip = new ZipArchive();
        if ( $zip->open( $zip_file ) !== true ) {
            return false;
        }

        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/bible-plugin/paratext';

        if ( !file_exists( $base_dir ) ) {
            wp_mkdir_p( $base_dir );
        }

        $temp_dir = $base_dir . '/temp_' . time();
        if ( !file_exists( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }

        $zip->extractTo( $temp_dir );
        $zip->close();

        $metadata_file = $temp_dir . '/metadata.xml';
        if ( file_exists( $metadata_file ) ) {
            $metadata = $this->parse_metadata_file( $metadata_file );
            $folder_name = $folder_name ?? $metadata['abbreviation'] ?? $metadata['name'];
        }

        if ( empty( $folder_name ) ) {
            $folder_name = pathinfo( $zip_file, PATHINFO_FILENAME );
        }

        $translation_dir = $base_dir . '/' . sanitize_file_name( $folder_name );

        if ( file_exists( $translation_dir ) ) {
            $this->recursive_remove_directory( $translation_dir );
        }

        rename( $temp_dir, $translation_dir );

        $this->set_directory( $translation_dir );

        return $translation_dir;
    }

    /**
     * Recursively removes a directory and all its contents, including subdirectories and files.
     *
     * @param string $dir The path to the directory to be removed.
     * @return bool Returns true on successful removal of the directory, or false if the directory
     *              does not exist or could not be completely removed.
     */
    private function recursive_remove_directory( string $dir ): bool
    {
        if ( !is_dir( $dir ) ) {
            return false;
        }

        $files = array_diff( scandir( $dir ), [ '.', '..' ] );

        foreach ( $files as $file ) {
            $path = $dir . '/' . $file;

            if ( is_dir( $path ) ) {
                $this->recursive_remove_directory( $path );
            } else {
                unlink( $path );
            }
        }

        return rmdir( $dir );
    }

    /**
     * Retrieves and processes all files to extract book data.
     *
     * @return array An associative array containing a 'data' key, which holds a list of parsed book data.
     */
    public function all(): array
    {
        $files = $this->files();
        $books = [];

        foreach ( $files as $file ) {
            $book = $this->parse( $file );
            if ( $book ) {
                $books[] = $book;
            }
        }

        return $books;
    }

    /**
     * Finds and retrieves a book by its ID from the list of files.
     *
     * @param int $id The ID of the book to find.
     * @return array An associative array containing a 'data' key, which holds the details of the found book.
     *
     * @throws BiblePluginException If a book with the specified ID is not found.
     */
    public function find( int $id ): array
    {
        $files = $this->files();

        if ( isset( $files[$id] ) ) {
            $book = $this->parse( $files[$id] );
            return $book;
        }

        throw new BiblePluginException( sprintf( 'Book with ID %d not found', esc_attr( $id ) ) );
    }

    /**
     * Finds and processes multiple books based on the provided IDs.
     *
     * @param array $ids An array of book IDs to be searched and processed.
     * @return array An associative array containing a 'data' key, which holds a list of successfully found and processed book data.
     */
    public function find_many( array $ids ): array
    {
        $books = [];

        foreach ( $ids as $id ) {
            try {
                $book = $this->find( $id );
                if ( !empty( $book ) ) {
                    $books[] = $book;
                }
            } catch ( BiblePluginException $e ) {
                continue;
            }
        }

        return $books;
    }

    /**
     * Filters books based on a given parameter and value.
     *
     * @param string $parameter The key to filter the books by.
     * @param mixed $value The value to match for the specified parameter.
     * @return array An associative array containing a 'data' key, which holds a list of books matching the given criteria.
     */
    public function where( $parameter, $value ): array
    {
        $all_books = $this->all();
        $matching_books = [];

        foreach ( $all_books as $book ) {
            if ( isset( $book[$parameter] ) && $book[$parameter] == $value ) {
                $matching_books[] = $book;
            }
        }

        return $matching_books;
    }

    /**
     * Retrieves specific content from a book based on the provided book ID, chapter, and verse range.
     *
     * @param string $book_id The unique identifier for the book to retrieve content from.
     * @param string|int $chapter The chapter number to retrieve content from.
     * @param string|int|null The starting verse within the specified chapter. Default is 1.
     * @param string|int|null The ending verse within the specified chapter. If null, only the starting verse is retrieved.
     *
     * @return array An associative array containing the retrieved content data, including book ID, chapter, verse range, and the corresponding content.
     *
     * @throws BiblePluginException If the specified book ID is not found.
     */
    public function content( string $book_id, $chapter, $verse_start = 1, $verse_end = null ): array
    {
        $files = $this->files();
        $book_file = null;

        foreach ( $files as $file ) {
            $book = $this->parse( $file, true );
            if ( $book && isset( $book['book_id'] ) && $book['book_id'] === $book_id ) {
                $book_file = $file;
                break;
            }
        }

        if ( !$book_file ) {
            throw new BiblePluginException( sprintf( 'Book %s not found', esc_attr( $book_id ) ) );
        }

        $book_content = $this->parse( $book_file );

        $passage = $this->passage( $book_content, $chapter, $verse_start ?: 1, $verse_end ?: null );

        $result = [];
        foreach ( $passage as $key => $text ) {
            $result[] = [
                'book_id' => $book_id,
                'book_name' => $this->parse_book_name( $book_id ),
                'chapter' => $chapter,
                'verse_start' => $key,
                'verse_end' => $key,
                'verse_text' => $text
            ];
        }
        return $result;
    }

    /**
     * Retrieves a list of USX files from the specified directory.
     *
     * @return array An array of file paths for USX files found in the directory.
     *
     * @throws BiblePluginException If the directory is not set, does not exist, or contains no USX files.
     */
    private function files(): array
    {
        if ( empty( $this->directory ) ) {
            throw new BiblePluginException( 'USX directory not set' );
        }

        if ( !is_dir( $this->directory ) ) {
            throw new BiblePluginException( sprintf( 'Directory %s does not exist', esc_attr( $this->directory ) ) );
        }

        $files = glob( $this->directory . '/*.{usx,USX}', GLOB_BRACE );

        if ( empty( $files ) ) {
            throw new BiblePluginException( sprintf( 'No USX files found in %s', esc_attr( $this->directory ) ) );
        }

        return $files;
    }

    /**
     * Parses a file to extract book data, with an option to only process the header.
     *
     * @param string $file_path The path to the file to be parsed.
     * @param bool $header_only Optional. When true, only the header information is parsed. Default is false.
     * @return array|null The parsed book data as an associative array, or null if the file does not exist or cannot be read.
     */
    private function parse( string $file_path, bool $header_only = false ): ?array
    {
        $cache_key = namespace_string( md5( $file_path . ( $header_only ? '_header' : '_full' ) ) );

        // Try transient first
        $book = get_transient( $cache_key );
        if ( $book !== false ) {
            return $book;
        }

        if ( !file_exists( $file_path ) ) {
            return null;
        }

        $content = file_get_contents( $file_path );
        if ( $content === false ) {
            return null;
        }

        $book = $this->parse_usx( $content, $header_only );

        // Store in transient for 12 hours
        set_transient( $cache_key, $book, HOUR_IN_SECONDS * 12 );

        return $book;
    }

    /**
     * Parses a USX XML string to extract book information such as its ID, name, and chapters.
     *
     * @param string $content The USX-formatted XML string to be parsed.
     * @param bool $header_only If true, only the book's metadata (ID and name) is returned without chapter data.
     * @return array An associative array with the following structure:
     *               - 'book_id': string, the ID of the book (e.g., a code).
     *               - 'name': string, the name of the book.
     *               - 'chapters': array, an associative array of chapter data where keys are chapter numbers and values are arrays of verses.
     */
    private function parse_usx( string $content, bool $header_only = false ): array
    {
        $book = [
            'book_id' => '',
            'name' => '',
            'chapters' => []
        ];

        $xml = simplexml_load_string( $content );
        if ( !$xml ) {
            return $book;
        }

        // Get book ID and name
        $book_element = $xml->xpath( '//book' );
        if ( !empty( $book_element ) ) {
            $book['book_id'] = (string) $book_element[0]['code'];
        }

        $para_elements = $xml->xpath( '//para[@style="h"]' );
        if ( !empty( $para_elements ) ) {
            $book['name'] = (string) $para_elements[0];
        } else {
            $toc1_elements = $xml->xpath( '//para[@style="toc1"]' );
            if ( !empty( $toc1_elements ) ) {
                $book['name'] = (string) $toc1_elements[0];
            }
        }

        if ( $header_only ) {
            return $book;
        }

        $current_chapter = null;

        // Loop through all elements in document order
        foreach ( $xml->children() as $element ) {
            if ( $element->getName() === 'chapter' ) {
                $current_chapter = (int) $element['number'];
                $book['chapters'][$current_chapter] = [];
            }

            if ( $element->getName() === 'para' ) {
                foreach ( $element->children() as $child ) {
                    if ( $child->getName() === 'verse' && $current_chapter !== null ) {
                        $verse_number = (int) $child['number'];
                        $verse_text = trim( (string) $child );

                        // Capture inline text after the <verse> tag
                        $tail = '';
                        $dom = dom_import_simplexml( $child );
                        if ( $dom && $dom->nextSibling && $dom->nextSibling->nodeType === XML_TEXT_NODE ) { //phpcs:ignore
                            $tail = trim( $dom->nextSibling->nodeValue ); //phpcs:ignore
                        }

                        $book['chapters'][$current_chapter][$verse_number] = $verse_text . ( $tail ? ' ' . $tail : '' );
                    }
                }
            }
        }

        return $book;
    }

    /**
     * Extracts a range of verses from a specified chapter within the provided book content.
     *
     * @param array $book_content The associative array containing book data, including chapters and verses.
     * @param int $chapter The chapter number from which to extract verses.
     * @param int $verse_start The starting verse number in the chapter.
     * @param int|null $verse_end The ending verse number in the chapter. If null, only the starting verse is extracted.
     * @return array An associative array of verses, where keys are verse numbers and values are verse content.
     * @throws BiblePluginException If the specified chapter is not found in the book content.
     * @throws BiblePluginException If the specified verses are not found in the chapter.
     */
    private function passage( array $book_content, $chapter, int $verse_start = 1, int $verse_end = null ): array
    {
        if ( !isset( $book_content['chapters'][$chapter] ) ) {
            throw new BiblePluginException( sprintf( 'Chapter %d not found', esc_attr( $chapter ) ) );
        }

        $chapter_content = $book_content['chapters'][$chapter];
        $verse_end = $verse_end ?: array_key_last( $chapter_content );
        $passage = [];

        $verse_end = $verse_end ?? $verse_start;

        for ( $verse = $verse_start; $verse <= $verse_end; $verse++ ) {
            if ( isset( $chapter_content[$verse] ) ) {
                $passage[$verse] = $chapter_content[$verse];
            }
        }

        if ( empty( $passage ) ) {
            throw new BiblePluginException( sprintf( 'Verses %d-%d not found in chapter %d', esc_attr( $verse_start ), esc_attr( $verse_end ), esc_attr( $chapter ) ) );
        }

        return $passage;
    }

    /**
     * Retrieves a list of books with their codes and corresponding names.
     *
     * @return array An array of books, where each book is represented as an associative array with the following structure:
     *               - 'code': string, the unique code identifier for the book.
     *               - 'name': string, the name of the book.
     */
    public function books(): array
    {
        $books = [];
        foreach ( $this->versification as $book_code => $chapters ) {
            $books[] = [
                'code' => $book_code,
                'name' => $this->parse_book_name( $book_code ),
                'chapters' => $chapters,
                'testament' => $this->books->guess_testament( $book_code ),
            ];
        }
        return $books;
    }

    /**
     * Retrieves the details of a specific book based on its code.
     *
     * @param string $book_code The unique code identifying the book.
     * @return array|null An associative array containing book details if a match is found, or null if no match exists.
     */
    public function book( string $book_code ): ?array
    {
        foreach ( $this->books() as $book ) {
            if ( $book['code'] === $book_code ) {
                return $book;
            }
        }
        return null;
    }

    /**
     * Retrieves the name of a book based on its book code by searching through available files.
     *
     * @param string $book_code The code of the book to search for (e.g., a unique identifier).
     * @return string The name of the book if found; otherwise, returns the provided book code.
     */
    private function parse_book_name( string $book_code ): string
    {
        foreach ( $this->files() as $file ) {
            $book = $this->parse( $file, true );
            if ( $book && isset( $book['book_id'] ) && $book['book_id'] === $book_code ) {
                return $book['name'];
            }
        }
        return $book_code;
    }
}
