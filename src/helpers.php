<?php

namespace CodeZone\Bible;

use CodeZone\Bible\Services\RestApi;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\CodeZone\WPSupport\Config\ConfigInterface;
use CodeZone\Bible\CodeZone\WPSupport\Container\ContainerFactory;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\CodeZone\WPSupport\Rewrites\RewritesInterface;
use CodeZone\Bible\CodeZone\WPSupport\Router\ResponseFactory;
use CodeZone\Bible\League\Container\Container;
use CodeZone\Bible\League\Plates\Engine;
use CodeZone\Bible\Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * @var $container Container
 */

/**
 * Returns the singleton instance of the Plugin class.
 *
 * @return Plugin The singleton instance of the Plugin class.
 */
function plugin(): Plugin {
	return container()->get( Plugin::class );
}

/**
 * Return the container instance.
 *
 * @return Container The container instance.
 * @see https://container.thephpleague.com/4.x/
 */
function container(): Container {
	return ContainerFactory::singleton();
}

/**
 * Returns the ConfigInterface object or the value of a specific configuration key.
 * If a key is provided, the method will return the value of the specified key from the ConfigInterface object.
 * If no key is provided, the method will return the ConfigInterface object itself.
 *
 * @param string|null $key (optional) The configuration key to retrieve the value for.
 *
 * @return mixed The ConfigInterface object if no key is provided, or the value of the specified configuration key.
 * @see https://config.thephpleague.com/
 */
function config( $key = null, $default = null ) {
	$service = container()->get( ConfigInterface::class );

	if ( $key ) {
		return $service->get( $key, $default );
	}

	return $service;
}

/**
 * Sets a configuration value for a specified key.
 *
 * @param string $key The configuration key to be set.
 * @param mixed $value The value to be associated with the specified configuration key.
 *
 * @return mixed The result of the configuration set operation.
 */
function set_config( $key, $value ) {
	$service = container()->get( ConfigInterface::class );

	return $service->set( $key, $value );
}

/**
 * Retrieves the URL of a file or directory within the plugin directory.
 *
 * @param string $path Optional. The path of the file or directory within the Bible Plugin directory. Defaults to empty string.
 *
 * @return string The URL of the specified file or directory within the Bible Plugin directory.
 */
function plugin_url( string $path = '' ): string {
    return plugins_url() . '/' . basename( dirname( __DIR__ ) ) . '/' . ltrim( $path, '/' );
}

/**
 * Constructs and returns the full API URL for a given path.
 *
 * @param string $path The API endpoint path to append to the base API URL. Defaults to an empty string.
 *
 * @return string The full API URL including the provided path or the base API URL if no path is specified.
 */
function api_url( string $path = "" ) {
    if ( !$path ) {
       return rest_url( RestApi::PATH );
    }
	return rest_url( RestApi::PATH . '/' . $path );
}

/**
 * Returns the path of a plugin file or directory, relative to the plugin directory.
 *
 * @param string $path The path of the file or directory relative to the plugin directory. Defaults to an empty string.
 *
 * @return string The full path of the file or directory, relative to the plugin directory.
 * @see https://developer.wordpress.org/reference/functions/plugin_dir_path/
 */
function plugin_path( string $path = '' ): string {
	return Plugin::dir_path() . '/' . trim( $path, '/' );
}

/**
 * Get the source path using the given path.
 *
 * @param string $path The path to append to the source directory.
 *
 * @return string The complete source path.
 */
function src_path( string $path = '' ): string {
	return plugin_path( config( 'plugin.paths.src' ) . '/' . $path );
}

/**
 * Returns the path to the resources directory.
 *
 * @param string $path Optional. Subdirectory path to append to the resources directory.
 *
 * @return string The path to the resources directory, with optional subdirectory appended.
 */
function resources_path( string $path = '' ): string {
	return plugin_path( config( 'plugin.paths.resources' ) . '/' . $path );
}


/**
 * Returns the path relative to the wordpress admin directory.
 */
function admin_path( string $path = '' ): string {
    $full_url = get_admin_url( null, $path );
    return trim( parse_url( $full_url )['path'], '/' );
}

/**
 * Get the languages path using the given path.
 *
 * @param string $path The path to append to the languages directory.
 *
 * @return string The complete languages path.
 */
function languages_path( string $path = '' ): string {
	return plugin_path( 'languages/' . $path );
}

/**
 * Returns the path to the routes directory.
 *
 * @param string $path Optional. Subdirectory path to append to the routes directory.
 *
 * @return string The path to the routes directory, with optional subdirectory appended.
 */
function routes_path( string $path = '' ): string {
	return plugin_path( config( 'plugin.paths.routes' ) . '/' . $path );
}

/**
 * Returns the path to the views directory.
 *
 * @param string $path Optional. Subdirectory path to append to the views directory.
 *
 * @return string The path to the views directory, with optional subdirectory appended.
 */
function views_path( string $path = '' ): string {
	return plugin_path( config( 'plugin.paths.views' ) . '/' . $path );
}

/**
 * Renders a view and returns a response.
 *
 * @param string $view Optional. The name of the view to render. Defaults to an empty string.
 * @param array $args Optional. An array of data to pass to the view. Defaults to an empty array.
 *
 * @return ResponseInterface The rendered view if a view name is provided, otherwise the view engine object.
 * @see https://platesphp.com/v3/
 */
function view( string $view = "", array $args = [] ): mixed {
    $engine = container()->get( Engine::class );

    // Return engine if no view specified
    if ( !$view ) {
        return $engine;
    }

    $data = [
        'view' => $view,
        'args' => $args,
        'html' => ''
    ];

    // Allow pre-render modifications
    $data = apply_filters( namespace_string( 'before_render_view' ), $data );

    // Only render if html hasn't been set by a filter
    if ( empty( $data['html'] ) ) {
        $data['html'] = $engine->render( $data['view'], $data['args'] );
    }

    // Allow post-render modifications
    return apply_filters( namespace_string( 'after_render_view' ), $data['html'], $data['view'], $data['args'] );
}

/**
 * Creates a new ResponseInterface instance for the given URL.
 *
 * @param string $url The URL to redirect to.
 * @param int $status Optional. The status code for the redirect response. Default is 302.
 *
 * @return ResponseInterface A new RedirectResponse instance.
 * @see https://github.com/guzzle/psr7
 */
function redirect( string $url, int $status = 302, $headers = [] ): ResponseInterface {
	return ResponseFactory::redirect( $url, $status, $headers );
}

/**
 * Set the value of an option.
 *
 * This is a convenience function that checks if the option exists before setting it.
 *
 * @param string $option_name The name of the option.
 * @param mixed $value The value to set for the option.
 *
 * @return bool Returns true if the option was successfully set, false otherwise.
 * @see https://developer.wordpress.org/reference/functions/add_option/
 * @see https://developer.wordpress.org/reference/functions/update_option/
 */
function set_option( string $option_name, $value ): bool {
	if ( get_option( $option_name ) === false ) {
		return add_option( $option_name, $value );
	} else {
		return update_option( $option_name, $value );
	}
}

/**
 * Retrieves the value of an option taking the default value set in the options service provider.
 *
 * @param string $option The name of the option to retrieve.
 * @param mixed $default Optional. The default value to return if the option does not exist. Defaults to false.
 *
 * @return mixed The value of the option if it exists, or the default value if it doesn't.
 */
function get_plugin_option( $option, $default = null, $required = false ) {
	$options = container()->get( OptionsInterface::class );
	return $options->get( $option, $default, $required );
}

/**
 * Sets the value of a plugin option.
 *
 * @param mixed $option The option to set.
 * @param mixed $value The value to set for the option.
 *
 * @return bool True if the option value was successfully set, false otherwise.
 */
function set_plugin_option( $option, $value ): bool {
	$options = container()->get( OptionsInterface::class );
	return $options->set( $option, $value );
}

/**
 * Start a database transaction and execute a callback function within the transaction.
 *
 * @param callable $callback The callback function to execute within the transaction.
 *
 * @return bool|string Returns true if the transaction is successful, otherwise returns the last database error or exception message.
 *
 * @throws Exception If there is a database error before starting the transaction.
 */
function transaction( callable $callback ) {
    global $wpdb;

    $wpdb->last_error = ''; // clear old errors

    $previous_suppress_errors = $wpdb->suppress_errors();
    $wpdb->suppress_errors( false );

    if ( $wpdb->query( 'START TRANSACTION' ) === false ) {
        $wpdb->suppress_errors( $previous_suppress_errors );
        return $wpdb->last_error ?: 'Failed to start transaction.';
    }

    try {
        $result = $callback();

        if ( $result === false || ! empty( $wpdb->last_error ) ) {
            $wpdb->query( 'ROLLBACK' );
            $wpdb->suppress_errors( $previous_suppress_errors );
            return $wpdb->last_error ?: 'Unknown database error in transaction.';
        }

        $wpdb->query( 'COMMIT' );
        $wpdb->suppress_errors( $previous_suppress_errors );
        return $result === null ? true : $result;
    } catch ( \Throwable $e ) {
        $wpdb->query( 'ROLLBACK' );
        $wpdb->suppress_errors( $previous_suppress_errors );
        throw $e;
    }
}

function translate( $text, $context = [] ): string {
	return container()->get( Translations::class )->translate( $text, $context );
}

/**
 * Concatenates the given string to the namespace of the Router class.
 *
 * @param string $string The string to be concatenated to the namespace.
 *
 * @return string The result of concatenating the given string to the namespace of the Router class.
 */
function namespace_string( string $string ): string {
	return config( 'plugin.text_domain' ) . '.' . $string;
}

/**
 * Checks if the provided color is already in the RGB format, and converts it if necessary.
 *
 * @param string $color The color to check or convert. Accepts hexadecimal (#ABCDEF) or RGB (rgb(0, 0, 0)) formats.
 *
 * @return string The color in RGB format. If the provided color is already in RGB format, it is returned unchanged.
 */
function rgb( $color ): string {
    if ( str_contains( $color, 'rgb' ) ) {
        return $color;
	}
	$color = str_replace( '#', '', $color );
	if ( strlen( $color ) == 3 ) {
		$r = hexdec( substr( $color, 0, 1 ) . substr( $color, 0, 1 ) );
		$g = hexdec( substr( $color, 1, 1 ) . substr( $color, 1, 1 ) );
		$b = hexdec( substr( $color, 2, 1 ) . substr( $color, 2, 1 ) );
	} else {
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );
	}

	return "rgb($r, $g, $b)";
}

/**
 * Cast an array of strings to boolean values
 *
 * @param array $map The map containing values to be casted.
 *
 * @return array The map with boolean values casted.
 */
function cast_bool_values( $map ): array {
	return array_map( function ( $value ) {
		if ( $value === "true" ) {
			return true;
		} elseif ( $value === "false" ) {
			return false;
		}

		return $value;
	}, $map );
}

/**
 * Validates the provided data or request against a set of rules.
 *
 * @param mixed $data_or_request The data or request to be validated. Can be an array or an object depending on the implementation.
 * @param array $rules An array of validation rules that the data or request should comply with.
 *
 * @return mixed The result of the validation process. The specific return type may vary based on the validator implementation.
 */
function validate( $data_or_request, array $rules ) {
	$validator = container()->get( Services\Validator::class );
	return $validator->validate( $data_or_request, $rules );
}
