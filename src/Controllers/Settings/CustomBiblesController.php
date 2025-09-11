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
class CustomBiblesController
{
    /**
     * Render the Custom Bibles settings UI
     */
    public function show( Request $request )
    {
        $tab = 'local_bibles';
        $nonce = wp_create_nonce( 'local_upload_nonce' );
        $local_bibles = container()->get( ParatextBibles::class )->all();

        return view( 'settings/custom-bibles-form', compact( 'tab', 'nonce', 'local_bibles' ) );
    }

    /**
     * Handles the file upload.
     * Corresponds to the 'local_bibles_upload' route.
     */
    public function upload()
    {
        // Detect AJAX request (either wp_doing_ajax or standard XMLHttpRequest header)
        $is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || (
                isset( $_SERVER['HTTP_X_REQUESTED_WITH'] )
                && strtolower( sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) ) === 'xmlhttprequest'
            );

        // Nonce and permission checks
        $raw_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
        if ( ! $raw_nonce || ! wp_verify_nonce( $raw_nonce, 'local_upload_nonce' ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => esc_html__( 'Security check failed.', 'bible-plugin' ),
                ];
            }
            wp_die( esc_html__( 'Security check failed.', 'bible-plugin' ) );
        }

        if ( ! current_user_can( 'upload_files' ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => esc_html__( 'You do not have permission to upload files.', 'bible-plugin' ),
                ];
            }
            wp_die( esc_html__( 'You do not have permission to upload files.', 'bible-plugin' ) );
        }

        // Check if file was uploaded (by sanitized filename)
        $filename = ( isset( $_FILES['file']['name'] ) ) ? sanitize_file_name( wp_unslash( (string) $_FILES['file']['name'] ) ) : '';
        if ( $filename === '' ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => esc_html__( 'No file was uploaded.', 'bible-plugin' ),
                ];
            }
            wp_die( esc_html__( 'No file was uploaded.', 'bible-plugin' ) );
        }

        // Validate file type by sanitized filename extension (avoid trusting MIME type)
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        if ( $ext !== 'zip' ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => esc_html__( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ),
                ];
            }
            wp_die( esc_html__( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ) );
        }

        if ( ! class_exists( 'File_Upload_Upgrader' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-file-upload-upgrader.php';
        }

        // The second parameter is for a GET param holding a filename, not used here.
        $upgrader = new \File_Upload_Upgrader( 'file', '' );

        // The constructor handles the upload and will wp_die on errors.
        // The file path is available in $upgrader->package.
        $path = $upgrader->package;
        $language_id = isset( $_POST['language_id'] ) ? sanitize_text_field( wp_unslash( $_POST['language_id'] ) ) : null;

        try {
            container()->get( ParatextBibles::class )->extract_and_add( $path, $language_id );
            // Success, the ArraySource service has processed the file.
            $success_message = esc_html__( 'Paratext Bible uploaded successfully.', 'bible-plugin' );

            // The File_Upload_Upgrader creates an attachment and schedules a cleanup.
            // We can trigger it now that we're done with the file.
            $upgrader->cleanup();

        } catch ( \Exception $e ) {
            // The ArraySource service threw an error.
            $error_message = $e->getMessage();
            // The upgrader should clean up the file.
            $upgrader->cleanup();

            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => $error_message,
                ];
            }
            wp_die( esc_html( $error_message ) );
        }

        // If AJAX, return JSON payload with a refreshed list
        if ( $is_ajax ) {
            return [
                'success' => true,
                'message' => $success_message,
                'bibles'  => container()->get( ParatextBibles::class )->all(),
            ];
        }

        $tab = 'local_bibles';
        $nonce = wp_create_nonce( 'local_upload_nonce' );
        $local_bibles = container()->get( ParatextBibles::class )->all();

        return view( 'settings/custom-bibles-form', compact( 'success_message', 'tab', 'nonce', 'local_bibles' ) );
    }

    /**
     * Update bible metadata (currently allows language_id editing)
     */
    public function update( Request $request )
    {
        // Verify nonce
        $nonce = isset( $request->_wpnonce ) ? $request->_wpnonce : null;
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'local_upload_nonce' ) ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'nonce' => esc_html__( 'Security check failed.', 'bible-plugin' ) ],
                ],
                403
            );
        }

        $id = $request->id;

        $data = [];
        if ( isset( $request->language_id ) ) {
            $data['language_id'] = sanitize_text_field( $request->language_id );
        }

        if ( empty( $data ) ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'general' => esc_html__( 'No changes submitted.', 'bible-plugin' ) ],
                ],
                400
            );
        }

        $setting = container()->get( LocalBiblesSetting::class );
        $ok = $setting->update( $id, $data );

        if ( ! $ok ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'id' => esc_html__( 'Bible not found.', 'bible-plugin' ) ],
                ],
                404
            );
        }

        \wp_send_json(
            [
                'success' => true,
                'bibles'  => container()->get( ParatextBibles::class )->all(),
            ]
        );
    }

    /**
     * Delete a bible by id.
     */
    public function delete( Request $request )
    {
        // Verify nonce
        $nonce = isset( $request->_wpnonce ) ? $request->_wpnonce : null;
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'local_upload_nonce' ) ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'nonce' => esc_html__( 'Security check failed.', 'bible-plugin' ) ],
                ],
                403
            );
        }

        $id = isset( $request->id ) ? sanitize_text_field( $request->id ) : '';
        if ( $id === '' ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'id' => esc_html__( 'Invalid request.', 'bible-plugin' ) ],
                ],
                400
            );
        }

        $setting = container()->get( LocalBiblesSetting::class );
        $all = $setting->all() ?: [];
        $path = isset( $all[$id]['path'] ) ? $all[$id]['path'] : null;

        $ok = $setting->delete( $id );
        if ( ! $ok ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'id' => esc_html__( 'Bible not found.', 'bible-plugin' ) ],
                ],
                404
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

        \wp_send_json(
            [
                'success' => true,
                'message' => esc_html__( 'Bible deleted successfully.', 'bible-plugin' ),
                'bibles'  => container()->get( ParatextBibles::class )->all(),
            ]
        );
    }

    /**
     * Language search by ISO code returning possible sublanguages.
     * Fallback: returns empty results, caller should use ISO code as language_id.
     */
    public function language_search( Request $request ): array
    {
        $code = isset( $request->code ) ? sanitize_text_field( $request->code ) : '';
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

        \wp_send_json(
            [
                'success' => true,
                'results' => is_array( $results ) ? $results : [],
            ]
        );

        // This return is never reached, but preserves signature.
        return [];
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
