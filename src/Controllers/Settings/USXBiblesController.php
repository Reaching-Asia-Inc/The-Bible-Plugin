<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Requests\RequestInterface as Request;
use CodeZone\Bible\Sources\USX\USXBibles;
use CodeZone\Bible\Settings\USXBiblesSetting;
use function CodeZone\Bible\container;
use function CodeZone\Bible\validate;
use function CodeZone\Bible\view;

/**
 * Controller to manage Custom USX Bible uploads and settings
 */
class USXBiblesController
{
    /**
     * Render the Custom Bibles settings UI
     */
    public function show( Request $request )
    {
        $tab = 'usx_bibles';
        $nonce = wp_create_nonce( 'usx_upload_nonce' );
        $usx_bibles = container()->get( USXBibles::class )->all();

        return view( 'settings/usx-bibles-form', compact( 'tab', 'nonce', 'usx_bibles' ) );
    }

    /**
     * Handles the file upload.
     * Corresponds to the 'usx_bibles_upload' route.
     */
    public function upload()
    {
        // Detect AJAX request (either wp_doing_ajax or standard XMLHttpRequest header)
        $is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            || ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( (string) $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' );

        // Nonce and permission checks
        $raw_nonce = isset( $_POST['_wpnonce'] ) ? wp_unslash( $_POST['_wpnonce'] ) : ( $_REQUEST['_wpnonce'] ?? '' );
        if ( ! $raw_nonce || ! wp_verify_nonce( $raw_nonce, 'usx_upload_nonce' ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => __( 'Security check failed.', 'bible-plugin' ),
                ];
            }
            wp_die( __( 'Security check failed.', 'bible-plugin' ) );
        }

        if ( ! current_user_can( 'upload_files' ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => __( 'You do not have permission to upload files.', 'bible-plugin' ),
                ];
            }
            wp_die( __( 'You do not have permission to upload files.', 'bible-plugin' ) );
        }

        // Check if file was uploaded
        if ( ! isset( $_FILES['file'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => __( 'No file was uploaded.', 'bible-plugin' ),
                ];
            }
            wp_die( __( 'No file was uploaded.', 'bible-plugin' ) );
        }

        // Validate file type
        $file = $_FILES['file'];
        $allowed_types = array( 'application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed' );
        if ( ! in_array( $file['type'], $allowed_types, true ) ) {
            if ( $is_ajax ) {
                return [
                    'success' => false,
                    'message' => __( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ),
                ];
            }
            wp_die( __( 'Invalid file type. Please upload a ZIP file.', 'bible-plugin' ) );
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
            container()->get( USXBibles::class )->extract_and_add( $path, $language_id );
            // Success, the USXBibles service has processed the file.
            $success_message = __( 'USX Bible uploaded successfully.', 'bible-plugin' );

            // The File_Upload_Upgrader creates an attachment and schedules a cleanup.
            // We can trigger it now that we're done with the file.
            $upgrader->cleanup();

        } catch ( \Exception $e ) {
            // The USXBibles service threw an error.
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
                'bibles'  => container()->get( USXBibles::class )->all(),
            ];
        }

        $tab = 'usx_bibles';
        $nonce = wp_create_nonce( 'usx_upload_nonce' );
        $usx_bibles = container()->get( USXBibles::class )->all();

        return view( 'settings/usx-bibles-form', compact( 'success_message', 'tab', 'nonce', 'usx_bibles' ) );
    }

    /**
     * Update bible metadata (currently allows language_id editing)
     */
    public function update( Request $request )
    {
        // Verify nonce
        $nonce = isset( $request->_wpnonce ) ? $request->_wpnonce : null;
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'usx_upload_nonce' ) ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'nonce' => __( 'Security check failed.', 'bible-plugin' ) ],
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
                    'errors'  => [ 'general' => __( 'No changes submitted.', 'bible-plugin' ) ],
                ],
                400
            );
        }

        $setting = container()->get( USXBiblesSetting::class );
        $ok = $setting->update( $id, $data );

        if ( ! $ok ) {
            \wp_send_json(
                [
                    'success' => false,
                    'errors'  => [ 'id' => __( 'Bible not found.', 'bible-plugin' ) ],
                ],
                404
            );
        }

        \wp_send_json(
            [
                'success' => true,
                'bibles'  => container()->get( USXBibles::class )->all(),
            ]
        );
    }

    /**

    /**
     * Language search by ISO code returning possible sublanguages.
     * Fallback: returns empty results, caller should use ISO code as language_id.
     */
    public function language_search( Request $request ): array
    {
        $code = isset( $request->code ) ? sanitize_text_field( $request->code ) : '';
        $results = [];

        // Attempt to use a language resource if available
        try {
            if ( class_exists( '\CodeZone\Bible\Sources\USX\USXLanguages' ) ) {
                $langSvc = new \CodeZone\Bible\Sources\USX\USXLanguages();
                if ( method_exists( $langSvc, 'search' ) ) {
                    $results = $langSvc->search( $code );
                } elseif ( method_exists( $langSvc, 'for_language_code' ) ) {
                    $results = $langSvc->for_language_code( $code );
                }
            }
        } catch ( \Throwable $e ) {
            // Silent fallback
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
