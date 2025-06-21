<?php

namespace CodeZone\Bible\Services;

use function CodeZone\Bible\config;
use function CodeZone\Bible\dd;

/**
 * Class ErrorHandler
 *
 * Handles custom error management, specifically for suppressing certain deprecation warnings.
 */
class ErrorHandler {
    /**
     * @var array List of paths/strings to ignore deprecation warnings for
     */
    protected array $blacklist = [
        'CodeZone\Bible\Opis\Closure\unserialize(): Implicitly marking parameter $options as nullable is deprecated, the explicit nullable type must be used instead',
        '::$termmeta',
        'CodeZone\Bible\League\Plates\Template\Template::data(): Implicitly marking parameter $data as nullable is deprecated, the explicit nullable type must be used instead'
    ];

    /**
     * @var callable|null Previous error handler
     */
    protected $previous_handler = null;

    /**
     * Add a string to the blacklist
     *
     * @param string $string
     * @return void
     */
    public function add_to_blacklist( string $string ): void
    {
        $this->blacklist[] = $string;
    }

    /**
     * Get the previous error handler
     *
     * @return callable|null
     */
    public function get_previous_error_handler(): ?callable
    {
        return $this->previous_handler;
    }

    /**
     * Check if a string is in the blacklist
     *
     * @param string $string
     * @return bool
     */
    public function is_blacklisted( string $string ): bool
    {
        foreach ( $this->blacklist as $blacklisted ) {
            if ( strpos( $string, $blacklisted ) !== false ) {
                return true;
            }
        }
        return false;
    }

    public function __construct()
    {
        $this->previous_handler = set_error_handler( [ $this, 'handle' ] );
    }

    /**
     * Custom error handler
     *
     * @param int    $errno   Error number
     * @param string $errstr  Error string
     * @param string $errfile File where the error occurred
     * @param int    $errline Line where the error occurred
     * @return bool
     */
    public function handle( int $errno, string $errstr, string $errfile, int $errline ): bool
    {
        // Only handle deprecation warnings
        if ( $errno === E_DEPRECATED || $errno === E_USER_DEPRECATED ) {
            foreach ( $this->blacklist as $string ) {
                if (
                    strpos( $errstr, $string ) !== false ||
                    strpos( $errfile, $string ) !== false
                ) {
                    // Ignore this deprecation warning
                    return true;
                }
            }

            // If running in PHPUnit, suppress callstack and just print message
            if ( getenv( 'PHPUNIT' ) === '1' ) {
                fwrite( STDERR, "Deprecation Notice: $errstr in $errfile on line $errline\n" );
                return true;
            }
        }

        // If we have a previous error handler, call it
        if ( $this->previous_handler ) {
            return call_user_func(
                $this->previous_handler,
                $errno,
                $errstr,
                $errfile,
                $errline
            );
        }

        // Let default PHP error handler handle it
        return false;
    }

    /**
     * Reset to the previous error handler
     *
     * @return void
     */
    public function restore(): void
    {
        if ( $this->previous_handler ) {
            restore_error_handler();
        }
    }
}
