<?php

namespace CodeZone\Bible\Services;

/**
 * FileFinder provides reusable recursive file indexing with manifest support.
 */
class FileFinder
{
    private string $directory = '';
    private array $index = [];
    private string $manifest_file_name;

    public function __construct( string $directory = '', string $manifest_file_name = '.manifest.json' )
    {
        $this->manifest_file_name = $manifest_file_name;
        if ( $directory ) {
            $this->set_directory( $directory );
        }
    }

    public function set_directory( string $directory ): void
    {
        $this->directory = $directory;
        $this->build_index();
    }

    public function get_directory(): string
    {
        return $this->directory;
    }

    public function ensure_manifest(): void
    {
        if ( empty( $this->directory ) || !is_dir( $this->directory ) ) {
            return;
        }
        $manifest = $this->manifest_path();
        if ( !file_exists( $manifest ) ) {
            $this->write_manifest();
        }
    }

    public function find_by_name( string $needle ): ?string
    {
        if ( isset( $this->index[$needle] ) ) {
            return $this->index[$needle];
        }
        foreach ( $this->index as $rel => $abs ) {
            if ( basename( $rel ) === $needle ) {
                return $abs;
            }
        }
        return null;
    }

    public function has_filename( string $needle ): bool
    {
        return (bool) $this->find_by_name( $needle );
    }

    public function list_by_extensions( array $extensions ): array
    {
        $extensions = array_map( static fn( $e ) => strtolower( $e ), $extensions );
        $files = [];
        foreach ( $this->index as $rel => $abs ) {
            $ext = strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) );
            if ( in_array( $ext, $extensions, true ) ) {
                $files[] = $abs;
            }
        }
        \sort( $files );
        return $files;
    }

    public function get_index(): array
    {
        return $this->index;
    }

    private function build_index(): void
    {
        $this->index = [];
        if ( empty( $this->directory ) || !is_dir( $this->directory ) ) {
            return;
        }
        // Prefer manifest
        $manifest = $this->manifest_path();
        if ( file_exists( $manifest ) ) {
            $list = $this->read_manifest_files( $manifest );
            if ( !empty( $list ) ) {
                foreach ( $list as $rel ) {
                    $abs = rtrim( $this->directory, "/\\" ) . '/' . ltrim( $rel, "/\\" );
                    if ( file_exists( $abs ) ) {
                        $this->index[$rel] = $abs;
                    }
                }
                if ( !empty( $this->index ) ) {
                    return;
                }
            }
        }
        // Fallback: recursive scan
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator( $this->directory, \FilesystemIterator::SKIP_DOTS )
        );
        foreach ( $iterator as $file_info ) {
            if ( $file_info->isFile() ) {
                $abs = $file_info->getPathname();
                $rel = ltrim( str_replace( $this->directory, '', $abs ), '/\\' );
                $this->index[$rel] = $abs;
            }
        }
    }

    private function manifest_path(): string
    {
        return rtrim( $this->directory, "/\\" ) . '/' . $this->manifest_file_name;
    }

    private function read_manifest_files( string $manifest_path ): array
    {
        $list = [];
        $json = @file_get_contents( $manifest_path );
        if ( $json === false ) {
            return $list;
        }
        $data = json_decode( $json, true );
        if ( is_array( $data ) && isset( $data['files'] ) && is_array( $data['files'] ) ) {
            $list = $data['files'];
        }
        return $list;
    }

    public function write_manifest(): void
    {
        if ( empty( $this->directory ) || !is_dir( $this->directory ) ) {
            return;
        }
        $manifest_path = $this->manifest_path();
        $payload = [
            'generated_at' => time(),
            'files' => array_keys( $this->index ),
        ];
        if ( function_exists( 'wp_json_encode' ) ) {
            $json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
        } else {
            $json = json_encode( $payload );
        }
        if ( $json === false ) {
            $json = json_encode( $payload );
        }
        if ( is_writable( $this->directory ) ) {
            @file_put_contents( $manifest_path, $json . "\n" );
        }
    }

    public function where_extension( string $extension ): array
    {
        $extension = strtolower( $extension );
        $matches = [];
        foreach ( $this->index as $rel => $abs ) {
            $ext = strtolower( pathinfo( $rel, PATHINFO_EXTENSION ) );
            if ( $ext === $extension ) {
                $matches[] = $abs;
            }
        }
        return $matches;
    }

    public function has_extension( string $extension ): bool
    {
        return count( $this->where_extension( $extension ) ) > 0;
    }
}
