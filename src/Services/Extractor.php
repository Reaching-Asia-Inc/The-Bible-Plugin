<?php

namespace CodeZone\Bible\Services;

use ZipArchive;

class Extractor
{
    public function extract( string $path_to_zip ) {
        return $this->extract_to( $path_to_zip );
    }
    public function extract_to( string $path_to_zip, string $to_dir = 'bible-plugin', $folder_name = null )
    {
        if ( !file_exists( $path_to_zip ) ) {
            return false;
        }

        $zip = new ZipArchive();
        $result = $zip->open( $path_to_zip );
        if ( $result !== true ) {
            return false;
        }

        try {
            $base_dir = $this->scope_directory( $to_dir );
            if ( !file_exists( $base_dir ) && !wp_mkdir_p( $base_dir ) ) {
                return false;
            }

            $temp_dir = $base_dir . '/temp_' . uniqid( '', true );
            if ( !file_exists( $temp_dir ) && !wp_mkdir_p( $temp_dir ) ) {
                return false;
            }

            if ( !$zip->extractTo( $temp_dir ) ) {
                $this->recursive_remove_directory( $temp_dir );
                return false;
            }

            if ( $folder_name ) {
                $output_directory = $this->move( $temp_dir, $base_dir . '/' . sanitize_file_name( $folder_name ) );
            } else {
                $output_directory = $temp_dir;
            }

            return $output_directory;
        } finally {
            $zip->close();
        }
    }

    protected function recursive_remove_directory( string $dir ): bool
    {
        if ( !is_dir( $dir ) ) {
            return false;
        }

        $files = array_diff( scandir( $dir ), [ '.', '..' ] );
        foreach ( $files as $file ) {
            $path = $dir . '/' . $file;
            if ( is_dir( $path ) ) {
                $this->recursive_remove_directory( $path );
            } elseif ( !unlink( $path ) ) {
                    error_log( "Failed to delete file: $path" );
                    return false;
            }
        }

        if ( !rmdir( $dir ) ) {
            error_log( "Failed to remove directory: $dir" );
            return false;
        }

        return true;
    }

    public function scope_directory( $dir ): string {
        $uploads_dir = wp_upload_dir();
        return rtrim( $uploads_dir['basedir'], '/\\' ) . '/' . trim( $dir, '/\\' );
    }

    public function move( $from, $to ): string {
        $uploads_dir = wp_upload_dir();
        $base = rtrim( $uploads_dir['basedir'], "/\\" );

        // Only scope if the path is not already under uploads base
        $from_norm = $from;
        if ( strpos( $from_norm, $base ) !== 0 ) {
            $from_norm = $this->scope_directory( $from_norm );
        }

        $to_norm = $to;
        if ( strpos( $to_norm, $base ) !== 0 ) {
            $to_norm = $this->scope_directory( $to_norm );
        }

        // Validate paths are within allowed scope (use realpath when possible)
        $allowed_base = realpath( $base ) ?: $base;
        $from_real = realpath( $from_norm ) ?: $from_norm;
        $to_parent = dirname( $to_norm );
        $to_parent_real = realpath( $to_parent ) ?: $to_parent;

        if ( strpos( $from_real, $allowed_base ) !== 0 || strpos( $to_parent_real, $allowed_base ) !== 0 ) {
            throw new \InvalidArgumentException( 'Path outside allowed scope' );
        }

        if ( file_exists( $to_norm ) ) {
            $this->recursive_remove_directory( $to_norm );
        }

        // Ensure destination parent exists when destination doesn't already exist
        if ( !file_exists( $to_norm ) && !is_dir( $to_parent_real ) && !wp_mkdir_p( $to_parent_real ) ) {
            throw new \RuntimeException( 'Failed to create destination directory' );
        }

        if ( !rename( $from_norm, $to_norm ) ) {
            throw new \RuntimeException( 'Failed to move directory' );
        }

        $local_files = new FileFinder( $to_norm );
        $local_files->write_manifest();

        return $to_norm;
    }
}
