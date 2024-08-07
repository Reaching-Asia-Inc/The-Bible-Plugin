<?php

namespace CodeZone\Bible;

use CodeZone\Bible\Illuminate\Http\Client\Factory as HTTPFactory;
use CodeZone\Bible\Illuminate\Http\RedirectResponse;
use CodeZone\Bible\Illuminate\Http\Request;
use CodeZone\Bible\Illuminate\Support\Str;
use CodeZone\Bible\Illuminate\Validation\Factory;
use CodeZone\Bible\League\Plates\Engine;
use CodeZone\Bible\Services\Options;
use CodeZone\Bible\Services\Template;
use CodeZone\Bible\Services\Translations;

/**
 * Returns the singleton instance of the Plugin class.
 *
 * @return Plugin The singleton instance of the Plugin class.
 */
function plugin(): Plugin {
	return Plugin::$instance;
}

/**
 * Returns the container object.
 *
 * @return Illuminate\Container\Container The container object.
 */
function container(): Illuminate\Container\Container {
	return plugin()->container;
}

/**
 * Returns the URL of a plugin file or directory, relative to the plugin base URL.
 *
 * @param string $path The path of the file or directory relative to the plugin base URL. Defaults to an empty string.
 *
 * @return string The full URL of the file or directory, relative to the plugin base URL.
 */
function plugin_url( string $path = '' ): string {
	return plugins_url( 'bible-plugin' ) . '/' . ltrim( $path, '/' );
}

/**
 * Checks if the route rewrite rule exists in the WordPress rewrite rules.
 *
 * @return bool Whether the route rewrite rule exists in the rewrite rules.
 * @global WP_Rewrite $wp_rewrite The main WordPress rewrite rules object.
 *
 */
function has_route_rewrite(): bool {
	global $wp_rewrite;

	if ( ! is_array( $wp_rewrite->rules ) ) {
		return false;
	}

	return array_key_exists( 'index.php?' . plugin()::QUERY_VAR . '=$matches[1]', $wp_rewrite->rules );
}

/**
 * Returns the URL of a route.
 *
 * If the route rewriting is enabled, it will return the URL of the route relative to the home
 * route of the plugin. If the route rewriting is not enabled, it will return the URL of the
 * route appended with query parameters indicating that it is a route of the plugin.
 *
 * @param string $path The path of the route.
 *
 * @return string The URL of the route.
 */
function route_url( string $path ): string {
	if ( ! has_route_rewrite() ) {
		return rtrim(site_url(), '/') . '/?' . http_build_query( [ 'bible-plugin' => $path ] );
	} else {
		return '/' . plugin()::$home_route . '/' . ltrim( $path, '/' );
	}
}

/**
 * Returns the path of a plugin file or directory, relative to the plugin directory.
 *
 * @param string $path The path of the file or directory relative to the plugin directory. Defaults to an empty string.
 *
 * @return string The full path of the file or directory, relative to the plugin directory.
 */
function plugin_path( string $path = '' ): string {
	return '/' . implode( '/', [
			trim( Str::remove( '/src', plugin_dir_path( __FILE__ ) ), '/' ),
			trim( $path, '/' ),
    ] );
}

/**
 * Get the source path using the given path.
 *
 * @param string $path The path to append to the source directory.
 *
 * @return string The complete source path.
 */
function src_path( string $path = '' ): string {
	return plugin_path( 'src/' . $path );
}

/**
 * Returns the path to the resources directory.
 *
 * @param string $path Optional. Subdirectory path to append to the resources directory.
 *
 * @return string The path to the resources directory, with optional subdirectory appended.
 */
function resources_path( string $path = '' ): string {
	return plugin_path( 'resources/' . $path );
}

/**
 * Returns the path relative to the wordpress admin directory.
 */
function admin_path( string $path = '' ): string {
    $full_url = get_admin_url( null, $path );
    return trim( parse_url( $full_url )[ 'path' ] , '/' );
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
	return plugin_path( 'routes/' . $path );
}

/**
 * Returns the path to the views directory.
 *
 * @param string $path Optional. Subdirectory path to append to the views directory.
 *
 * @return string The path to the views directory, with optional subdirectory appended.
 */
function views_path( string $path = '' ): string {
	return plugin_path( 'resources/views/' . $path );
}

/**
 * Renders a view using the provided view engine.
 *
 * @param string $view Optional. The name of the view to render. Defaults to an empty string.
 * @param array $args Optional. An array of data to pass to the view. Defaults to an empty array.
 *
 * @return string|Engine The rendered view if a view name is provided, otherwise the view engine object.
 */
function view( string $view = "", array $args = [] ) {
	$engine = container()->make( Engine::class );
	if ( ! $view ) {
		return $engine;
	}

	return $engine->render( $view, $args );
}

/**
 * Renders a template using the Template service.
 *
 * @param string $template Optional. The template to render. If not specified, the Template service instance is returned.
 * @param array $args Optional. An array of arguments to be passed to the template.
 *
 * @return mixed If $template is not specified, an instance of the Template service is returned.
 *               If $template is specified, the rendered template is returned.
 */
function template( string $template = "", array $args = [] ) {
	$service = container()->make( Template::class );
	if ( ! $template ) {
		return $service;
	}

	return $service->render( $template, $args );
}

/**
 * Returns the Request object.
 *
 * @return Request The Request object.
 */
function request(): Request {
	return container()->make( Request::class );
}

/**
 * Creates a new RedirectResponse instance for the given URL.
 *
 * @param string $url The URL to redirect to.
 * @param int $status Optional. The status code for the redirect response. Default is 302.
 *
 * @return RedirectResponse A new RedirectResponse instance.
 */
function redirect( string $url, int $status = 302 ): RedirectResponse {
	return container()->makeWith( RedirectResponse::class, [
		'url'    => $url,
		'status' => $status,
	] );
}

/**
 * Validate the given data using the provided rules and messages.
 *
 * @param array $data The data to be validated.
 * @param array $rules The validation rules to be applied.
 * @param array $messages The custom error messages to be displayed.
 *
 * @return array The array of validation error messages, if any.
 */
function validate( array $data, array $rules, array $messages = [] ): array {
	$validator = container()->make( Factory::class )->make( $data, $rules, $messages );
	if ( $validator->fails() ) {
		return $validator->errors()->toArray();
	}

	return [];
}

/**
 * Set the value of an option.
 *
 * This function first checks if the option already exists. If it doesn't, it adds a new option with the given name and value.
 * If the option already exists, it updates the existing option with the given value.
 *
 * @param string $option_name The name of the option.
 * @param mixed $value The value to set for the option.
 *
 * @return bool Returns true if the option was successfully set, false otherwise.
 */
function set_option( string $option_name, $value ): bool {
	if ( get_plugin_option( $option_name ) === false ) {
		return add_option( $option_name, $value );
	} else {
		return update_option( $option_name, $value );
	}
}

/**
 * Start a database transaction and execute a callback function within the transaction.
 *
 * @param callable $callback The callback function to execute within the transaction.
 *
 * @return bool|string Returns true if the transaction is successful, otherwise returns the last database error.
 *
 * @throws \Exception If there is a database error before starting the transaction.
 */
function transaction( $callback ) {
	global $wpdb;
	if ( $wpdb->last_error ) {
		return $wpdb->last_error;
	}
	$wpdb->query( 'START TRANSACTION' );
	$callback();
	if ( $wpdb->last_error ) {
		$wpdb->query( 'ROLLBACK' );

		return $wpdb->last_error;
	}
	$wpdb->query( 'COMMIT' );

	return true;
}

function http(): HTTPFactory {
	return container()->make( HTTPFactory::class );
}

function translate( $text, $context = [] ): string {
	return container()->make( Translations::class )->translate( $text, $context );
}

/**
 * Concatenates the given string to the namespace of the Router class.
 *
 * @param string $string The string to be concatenated to the namespace.
 *
 * @return string The result of concatenating the given string to the namespace of the Router class.
 */
function namespace_string( string $string ): string {
	return Plugin::class . '\\' . $string;
}

/**
 * Checks if the provided color is already in the RGB format, and converts it if necessary.
 *
 * @param string $color The color to check or convert. Accepts hexadecimal (#ABCDEF) or RGB (rgb(0, 0, 0)) formats.
 *
 * @return string The color in RGB format. If the provided color is already in RGB format, it is returned unchanged.
 */
function rgb( $color ): string {
	if ( Str::contains( $color, 'rgb' ) ) {
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
 * Retrieves the value of an option from the options container.
 *
 * @param string $option The name of the option to retrieve.
 * @param mixed $default Optional. The default value to return if the option does not exist. Defaults to false.
 *
 * @return mixed The value of the option if it exists, or the default value if it doesn't.
 */
function get_plugin_option( $option, $default = null, $required = false ) {
	$options = container()->make( Options::class );

	return $options->get( $option, $default, $required );
}

/**
 * Sets the value of a plugin option.
 *
 * @param string $option The name of the option to set.
 * @param mixed $value The value to set for the option.
 *
 * @return bool true if the option was successfully set; otherwise, false.
 */
function set_plugin_option( $option, $value ): bool {
	$options = container()->make( Options::class );

	return $options->set( $option, $value );
}

function delete_plugin_option( $option ): bool {
    $options = container()->make( Options::class );

    return $options->delete( $option );
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
