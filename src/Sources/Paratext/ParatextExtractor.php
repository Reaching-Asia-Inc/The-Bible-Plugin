<?php

namespace CodeZone\Bible\Sources\Paratext;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\Extractor;
use CodeZone\Bible\Services\FileFinder;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;
use function str_contains;

class ParatextExtractor extends Extractor
{
    /**
     * Checks if the specified path contains a file named 'versification.vrs'.
     * If the given path is a zip file, it will be extracted and then checked
     * for the presence of the required file. The extracted directory will be
     * removed after the check is completed.
     *
     * @param string $path The file path or directory to check. If the path
     *                     points to a zip archive, it will be extracted temporarily.
     *
     * @return bool Returns true if the 'versification.vrs' file is found.
     *              Otherwise, returns false.
     */
    public function check( $path ): bool
    {
        $extracted_path = null;
        if ( str_contains( $path, '.zip' ) ) {
            $extracted_path = $this->extract( $path );
            $path = $extracted_path;
        }

        $local_files = new FileFinder( $path );
        if ( $extracted_path ) {
            $this->recursive_remove_directory( $extracted_path );
        }
        // Accept both USX and USFM based Paratext content
        return $local_files->has_extension( 'usx' ) || $local_files->has_extension( 'usfm' );
    }

    /**
     * Extracts a ZIP file, validates its contents, and moves it to a configured directory.
     *
     * @param string $path_to_zip The file path to the ZIP archive to be extracted.
     *
     * @return string The path to the final destination directory where extracted files are moved.
     *
     * @throws BiblePluginException If the extracted contents do not meet validation criteria.
     */
    public function extract( string $path_to_zip ): string
    {
        $base_dir = config( 'services.paratext.uploads_path' );
        $temp_dir = parent::extract( $path_to_zip );
        $local_files = new FileFinder( $temp_dir );
        if ( ! $this->check( $temp_dir ) ) {
            $this->recursive_remove_directory( $temp_dir );
            throw new BiblePluginException( "Invalid Paratext file" );
        }
        $paratext = container()->get( Paratext::class );
        $paratext->set_directory( $temp_dir );

        $folder_name = null;
        if ( $local_files->has_filename( "metadata.xml" ) ) {
            $metadata = $paratext->get_metadata();
            $folder_name = $metadata['abbreviation'] ?? $metadata['name'] ?? null;
        }

        if ( empty( $folder_name ) ) {
            $folder_name = pathinfo( $path_to_zip, PATHINFO_FILENAME );
        }

        return $this->move( $temp_dir, rtrim( $base_dir ) . '/' . ltrim( $folder_name ) );
    }
}
