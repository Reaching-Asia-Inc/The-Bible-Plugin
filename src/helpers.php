<?php

namespace CodeZone\Bible;

use CodeZone\Bible\Illuminate\Http\RedirectResponse;
use CodeZone\Bible\Illuminate\Http\Request;
use CodeZone\Bible\Illuminate\Support\Str;
use CodeZone\Bible\League\Plates\Engine;
use CodeZone\Bible\Services\Template;

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
function view( string $view = "", array $args = [] ): string|Engine {
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
function template( string $template = "", array $args = [] ): mixed {
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
