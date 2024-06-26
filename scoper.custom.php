<?php

/**
 * WordPress global function are already exposed by wpify/scoper.
 * We still need to expose laravel helpers to keep them
 * from being namespaced.
 *
 * @param array $config
 *
 * @return array
 */
function customize_php_scoper_config( array $config ): array {
	$replacements = [
		[
			'in'      => 'symfony/polyfill-php80/PhpToken.php',
			'needle'  => '\Stringable',
			'replace' => '\CodeZone\Bible\Stringable'
		]
	];

	$config['patchers'] = array_merge( $config['patchers'] ?? [], [
		function ( string $file_path, string $prefix, string $content ) use ( $replacements ): string {
			foreach ( $replacements as $config ) {
				if ( strpos( $file_path, $config['in'] ) !== false ) {
					$content = str_replace( $config['needle'], $config['replace'], $content );
				}
			}

			return $content;
		},
	] );

	$config['exclude-files'] = array_merge( $config['exclude-files'] ?? [], [
		'vendor/symfony/polyfill-php80/Resources/stubs/Stringable.php',
	] );

	$config['expose-functions'] = array_merge( $config['expose-functions'] ?? [], [
		'abort',
		'abort_if',
		'abort_unless',
		'action',
		'app',
		'app_path',
		'asset',
		'auth',
		'back',
		'bcrypt',
		'broadcast',
		'cache',
		'config',
		'config_path',
		'cookie',
		'collect',
		'csrf_field',
		'csrf_token',
		'data_get',
		'dd',
		'dispatch',
		'dispatch_now',
		'dump',
		'e',
		'event',
		'factory',
		'filled',
		'flash',
		'get',
		'head',
		'info',
		'logger',
		'method_field',
		'mix',
		'now',
		'old',
		'optional',
		'policy',
		'post',
		'put',
		'query',
		'redirect',
		'report',
		'request',
		'rescue',
		'resolve',
		'resource',
		'response',
		'retry',
		'route',
		'secure_asset',
		'secure_url',
		'session',
		'storage_path',
		'tap',
		'today',
		'trans',
		'trans_choice',
		'url',
		'validator',
		'value',
		'view',
		'with',
	] );

	return $config;
}
