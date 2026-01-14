<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Settings\LocalBiblesSetting;
use CodeZone\Bible\Sources\Paratext\ParatextBibles;
use function CodeZone\Bible\container;
use function CodeZone\Bible\view;

/**
 * Controller to manage Custom Paratext Bible uploads and settings
 */
class LocalBiblesController
{
    /**
     * Render the Custom Bibles settings UI
     */
    public function show( Request $request )
    {
        $tab = 'local_bibles';
        $nonce = wp_create_nonce( 'wp_rest' );
        $success_message = null;
        $error_message = null;

        $local_bibles = container()->get( ParatextBibles::class )->all();

        return view( 'settings/local-bibles-form', compact( 'tab', 'nonce', 'local_bibles', 'success_message', 'error_message' ) );
    }

    /**
     * Handles the file upload.
     * Corresponds to the 'local_bibles_upload' route.
     */
    public function upload( Request $request )
    {
        // REST: permission handled by route. Only validate inputs and process.
        // Support both multipart/form-data uploads and JSON base64 payloads
        $language_id = null;
        $success_message = esc_html__( 'Paratext Bible uploaded successfully.', 'bible-plugin' );

        // Branch 1: multipart/form-data via direct $_FILES handling (no File_Upload_Upgrader inside REST)
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- REST uses X-WP-Nonce header; permission is enforced by the route; here we only validate the upload
        $filename = ( isset( $_FILES['file']['name'] ) ) ? sanitize_file_name( wp_unslash( (string) $_FILES['file']['name'] ) ) : '';
        if ( $filename !== '' ) {
            // Validate file type by sanitized filename extension (avoid trusting MIME type)
            $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
            if ( $ext !== 'zip' ) {
                return new \WP_Error(
                    'invalid_type',
                    esc_html__( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ),
                    [ 'status' => 400 ]
                );
            }


            // Use the temporary uploaded file path directly
            // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- tmp_name is server-provided path; validated below with is_uploaded_file()
            $tmp_path = isset( $_FILES['file']['tmp_name'] ) ? (string) $_FILES['file']['tmp_name'] : '';
            if ( ! $tmp_path || ! file_exists( $tmp_path ) || ! is_uploaded_file( $tmp_path ) ) {
                return new \WP_Error(
                    'no_file',
                    esc_html__( 'No file was uploaded.', 'bible-plugin' ),
                    [ 'status' => 400 ]
                );
            }

            try {
                container()->get( ParatextBibles::class )->extract_and_add( $tmp_path );
            } catch ( \Throwable $e ) {
                return new \WP_Error( 'upload_failed', $e->getMessage(), [ 'status' => 400 ] );
            }
        } else {
            // Branch 2: JSON upload with base64-encoded file content
            // Note: Do not sanitize base64 payload via Request::get(), it would corrupt it.
            $file_name = sanitize_file_name( (string) $request->file_name );
            $file_data = (string) $request->file_data; // raw base64

            if ( $file_name === '' || $file_data === '' ) {
                return new \WP_Error(
                    'no_file',
                    esc_html__( 'No file was uploaded.', 'bible-plugin' ),
                    [ 'status' => 400 ]
                );
            }

            $ext = strtolower( pathinfo( $file_name, PATHINFO_EXTENSION ) );
            if ( $ext !== 'zip' ) {
                return new \WP_Error(
                    'invalid_type',
                    esc_html__( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ),
                    [ 'status' => 400 ]
                );
            }

            // If data URL provided, strip the prefix
            if ( strpos( $file_data, ',' ) !== false && strpos( $file_data, 'data:' ) === 0 ) {
                $parts = explode( ',', $file_data, 2 );
                $file_data = $parts[1];
            }

            $binary = base64_decode( $file_data, true );
            if ( $binary === false ) {
                return new \WP_Error(
                    'invalid_payload',
                    esc_html__( 'Invalid file payload.', 'bible-plugin' ),
                    [ 'status' => 400 ]
                );
            }

            // Create a temporary file under uploads and write contents
            $tmp = wp_tempnam( $file_name );
            if ( ! $tmp ) {
                return new \WP_Error(
                    'server_error',
                    esc_html__( 'Could not create a temporary file for upload.', 'bible-plugin' ),
                    [ 'status' => 500 ]
                );
            }

            $bytes = file_put_contents( $tmp, $binary );
            if ( $bytes === false ) {
                // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
                @unlink( $tmp );
                return new \WP_Error(
                    'server_error',
                    esc_html__( 'Could not write uploaded file.', 'bible-plugin' ),
                    [ 'status' => 500 ]
                );
            }

            try {
                container()->get( ParatextBibles::class )->extract_and_add( $tmp );
            } catch ( \Throwable $e ) {
                // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
                @unlink( $tmp );
                return new \WP_Error( 'upload_failed', $e->getMessage(), [ 'status' => 400 ] );
            }

            // Cleanup temp file
            // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
            @unlink( $tmp );
        }

        // Return JSON payload with a refreshed list
        return [
            'success' => true,
            'message' => $success_message,
            'bibles'  => container()->get( ParatextBibles::class )->all(),
        ];
    }

    /**
     * Update bible metadata (currently allows language_id editing)
     */
    public function update( Request $request )
    {
        // Avoid isset($request->id) as magic properties aren't recognized by isset().
        $id = (string) $request->get( 'id', '' );
        $id = $id !== '' ? sanitize_text_field( $id ) : '';
        $data = [];
        if ( $request->get( 'language_id', null ) !== null ) {
            $data['language_id'] = sanitize_text_field( (string) $request->get( 'language_id' ) );
        }

        if ( $id === '' ) {
            return new \WP_Error(
                'invalid_request',
                esc_html__( 'Invalid request.', 'bible-plugin' ),
                [ 'status' => 400 ]
            );
        }

        if ( empty( $data ) ) {
            return new \WP_Error(
                'no_changes',
                esc_html__( 'No changes submitted.', 'bible-plugin' ),
                [ 'status' => 400 ]
            );
        }

        $setting = container()->get( LocalBiblesSetting::class );
        $ok = $setting->update( $id, $data );

        if ( ! $ok ) {
            return new \WP_Error(
                'not_found',
                esc_html__( 'Bible not found.', 'bible-plugin' ),
                [ 'status' => 404 ]
            );
        }

        return [
            'success' => true,
            'bibles'  => container()->get( ParatextBibles::class )->all(),
        ];
    }

    /**
     * Delete a bible by id.
     */
    public function delete( Request $request )
    {
        // Avoid isset($request->id) as magic properties aren't recognized by isset().
        $id = (string) $request->get( 'id', '' );
        $id = $id !== '' ? sanitize_text_field( $id ) : '';

        if ( $id === '' ) {
            return new \WP_Error(
                'invalid_request',
                esc_html__( 'Invalid request.', 'bible-plugin' ),
                [ 'status' => 400 ]
            );
        }

        $setting = container()->get( LocalBiblesSetting::class );
        $path = $setting->find( $id )['path'] ?? null;
        $ok = $setting->delete( $id );
        if ( ! $ok ) {
            return new \WP_Error(
                'not_found',
                esc_html__( 'Bible not found.', 'bible-plugin' ),
                [ 'status' => 404 ]
            );
        }

        // Best-effort cleanup of extracted files if they live under uploads
        if ( $path ) {
            $uploads = wp_upload_dir();
            $base = rtrim( $uploads['basedir'] ?? '', "/\\" );
            $real_path = realpath( $path ) ?: $path;
            $real_base = realpath( $base ) ?: $base;
            if ( $real_base && $real_path && strpos( $real_path, $real_base ) === 0 ) {
                $this->rrmdir( $real_path );
            }
        }

        return [
            'success' => true,
            'message' => esc_html__( 'Bible deleted successfully.', 'bible-plugin' ),
            'bibles'  => container()->get( ParatextBibles::class )->all(),
        ];
    }

    /**
     * Language search by ISO code returning possible sublanguages.
     * Fallback: returns empty results, caller should use ISO code as language_id.
     */
    public function language_search( Request $request ): array
    {
        $code = $request->code ? sanitize_text_field( $request->code ) : '';
        $results = [];

        // Attempt to use a Paratext language service if available
        try {
            $lang_svc = container()->get( \CodeZone\Bible\Sources\Paratext\ParatextLanguages::class );
            if ( method_exists( $lang_svc, 'search' ) ) {
                $results = $lang_svc->search( $code );
            } elseif ( method_exists( $lang_svc, 'for_language_code' ) ) {
                $results = $lang_svc->for_language_code( $code );
            }
        } catch ( \Throwable $e ) {
            // Fallback: ensure predictable response and optionally log
            $results = [];
        }

        return [
            'success' => true,
            'results' => is_array( $results ) ? $results : [],
        ];
    }


    /**
     * Recursively remove a directory
     */
    protected function rrmdir( string $dir ): void
    {
        if ( !is_dir( $dir ) ) {
            return;
        }
        $objects = scandir( $dir );
        if ( is_array( $objects ) ) {
            foreach ( $objects as $object ) {
                if ( $object === '.' || $object === '..' ) {
                    continue;
                }
                $full = $dir . DIRECTORY_SEPARATOR . $object;
                if ( is_dir( $full ) ) {
                    $this->rrmdir( $full );
                } else {
                    // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
                    @unlink( $full );
                }
            }
        }
        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_rmdir
        @rmdir( $dir );
    }
}
