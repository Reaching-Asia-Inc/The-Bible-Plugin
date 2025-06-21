<?php
// Load Patchwork first
require_once __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';

// Then load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

if ( !function_exists( 'dd' ) ) {
    function dd( ...$vars ): void {
        foreach ( $vars as $var ) {
            fwrite( STDOUT, print_r( $var, true ) . PHP_EOL );
        }
        exit( 1 );
    }
}

// Setup WordPress test environment variables
$_tests_dir  = getenv( 'WP_TESTS_DIR' ) ?: rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
$_core_dir   = getenv( 'WP_CORE_DIR' ) ?: rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress';
$_plugin_env = isset( $_ENV['WP_PLUGIN_FILE'] ) ? sanitize_text_field( wp_unslash( $_ENV['WP_PLUGIN_FILE'] ) ) : false;

$_plugin_file = $_plugin_env ? $_plugin_env : dirname( __DIR__ ) . '/' . basename( dirname( __DIR__ ) ) . '.php';

if ( !file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo esc_html( "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" ) . PHP_EOL;
    exit( 1 );
}

// Load WP test functions
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
    if ( !empty( $_ENV['TBP_BIBLE_BRAINS_KEYS'] ) ) {
        define(
            'TBP_BIBLE_BRAINS_KEYS',
            esc_attr( sanitize_text_field( wp_unslash( $_ENV['TBP_BIBLE_BRAINS_KEYS'] ) ) )
        );
    }

    require dirname( __DIR__ ) . '/bible-plugin.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
require $_tests_dir . '/includes/bootstrap.php';

// Redefine WordPress test DB cleaner to suppress errors
\Patchwork\redefine( '_delete_all_data', function () {
    $error_level = error_reporting( 0 );

    try {
        global $wpdb;

        if ( ! is_object( $wpdb ) ) {
            return;
        }

        $wpdb_class = get_class( $wpdb );
        if (
            strpos( $wpdb_class, 'Mock_' ) !== false ||
            strpos( $wpdb_class, 'PHPUnit' ) !== false ||
            ! method_exists( $wpdb, 'query' ) ||
            ! method_exists( $wpdb, 'prepare' )
        ) {
            return;
        }

        $safe_query = function ( $query ) use ( $wpdb ) {
            try {
                if ( empty( $query ) ) {
                    return;
                }

                // Execute the query as-is, skipping prepare() to avoid breaking raw deletions
                $wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            //phpcs:ignore
            } catch ( \Throwable $e ) {
                // Intentionally ignoring errors to prevent cleanup failures during tests.
            }
        };

        $get_table = function ( $property ) use ( $wpdb ) {
            if ( ! property_exists( $wpdb, $property ) ) {
                return null;
            }

            try {
                $value = $wpdb->$property;
                return ( is_string( $value ) && ! empty( $value ) ) ? $value : null;
            //phpcs:ignore
            } catch ( \Throwable $e ) {
                return null;
            }
        };

        $posts_table            = $get_table( 'posts' );
        $postmeta_table         = $get_table( 'postmeta' );
        $comments_table         = $get_table( 'comments' );
        $commentmeta_table      = $get_table( 'commentmeta' );
        $term_relationships_tbl = $get_table( 'term_relationships' );
        $termmeta_table         = $get_table( 'termmeta' );

        $tables_to_process = array_filter([
            $posts_table,
            $postmeta_table,
            $comments_table,
            $commentmeta_table,
            $term_relationships_tbl,
            $termmeta_table
        ]);

        foreach ( $tables_to_process as $table ) {
            $safe_query( "DELETE FROM {$table}" );
        }

        $terms_table = $get_table( 'terms' );
        if ( $terms_table ) {
            $safe_query( "DELETE FROM {$terms_table} WHERE term_id != 1" );
        }

        $term_taxonomy_table = $get_table( 'term_taxonomy' );
        if ( $term_taxonomy_table ) {
            $safe_query( "DELETE FROM {$term_taxonomy_table} WHERE term_id != 1" );
            $safe_query( "UPDATE {$term_taxonomy_table} SET count = 0" );
        }

        $users_table = $get_table( 'users' );
        if ( $users_table ) {
            $safe_query( "DELETE FROM {$users_table} WHERE ID != 1" );
        }

        $usermeta_table = $get_table( 'usermeta' );
        if ( $usermeta_table ) {
            $safe_query( "DELETE FROM {$usermeta_table} WHERE user_id != 1" );
        }
    //phpcs:ignore
    } catch ( \Throwable $e ) {
        // Intentionally suppressing errors during cleanup
    } finally {
        error_reporting( $error_level );
    }
});
