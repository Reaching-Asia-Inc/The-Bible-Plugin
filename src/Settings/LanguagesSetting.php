<?php

namespace CodeZone\Bible\Settings;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\Locale;
use function CodeZone\Bible\config;

/**
 * Class LanguagesSetting provides functionality to manage language configurations, including guessing,
 * searching, and validating language settings based on locale or other predefined criteria.
 */
class LanguagesSetting
{
    private Locale $locale;
    private OptionsInterface $options;

    public function __construct( Locale $locale, OptionsInterface $options )
    {
        $this->locale = $locale;
        $this->options = $options;
    }

    /**
     * Retrieves and formats all available language configurations.
     *
     * @return array|null Returns an array of formatted language configurations if available, or null if no configurations are found, are not in array format, or an exception occurs.
     */
    public function all(): ?array {
        try {
            $languages = $this->options->get( 'languages', null, true );
            if ( ! is_array( $languages ) ) {
                return null;
            }
            return array_map(function($language) {
                return $this->format($language);
            }, $languages);
        } catch ( BiblePluginException $e ) {
            return null;
        }
    }

    /**
     * Attempts to determine the appropriate configuration based on a provided or default locale code.
     *
     * @param string|null $code The locale code to use for guessing. If null, the default locale code is derived from the current locale.
     *
     * @return mixed Returns the configuration matching the locale code if supported, or the default configuration if no match is found.
     */
    public function guess( $iso = null ) {
        $iso = $iso ?? $this->locale->iso();

        if ( $this->supported( $iso ) ) {
            return $this->search_or_default( $iso );
        }

        return $this->default();
    }

    /**
     * Attempts to find a language configuration based on the given value, or guesses a default configuration if no match is found.
     *
     * @param string|null $value The value to search for, which can match the 'value', 'id', or 'code' of a language configuration.
     *
     * @return mixed Returns the matching language configuration if found, the guessed configuration if no match is found, or null if no configuration is available.
     */
    public function search_or_guess( $value ) {
        $language = $value ? $this->search( $value ) : null;
        if ( ! $language ) {
            $language = $this->guess();
        };

        return $language;
    }

    /**
     * Retrieves a language by its code or returns the default language if not found.
     *
     * @param string|null $code The code of the language to find. If null, the default language will be returned.
     *
     * @return array|null Returns the language array if found, the default language array if not found, or null on failure.
     */
    public function search_or_default( $value ) {
        $language = $this->search( $value );
        if ( ! $language ) {
            $language = $this->default();
        }

        return $language;
    }

    /**
     * Get the resolved locale from the translations object.
     *
     * @return string The resolved locale.
     */
    /**
     * Checks if the given value is supported by any language configuration.
     *
     * @param mixed $value The value to check, which can match the 'code', 'value', or 'id' of a language configuration.
     *
     * @return bool Returns true if the value is supported, false otherwise or if an exception occurs.
     */
    public function supported( $value ) {
        return !!$this->find($value);
    }

    /**
     * Gets the default language from the options or returns the default value if it's not configured.
     */
    public function default(): ?array {
        $languages = $this->all();
        if ( ! is_array( $languages ) ) {
            return config( 'options.defaults.language' );
        }
        $default_language = null;
        foreach ( $languages as $config ) {
            if ( !empty( $config['is_default'] ) ) {
                $default_language = $config;
                break;
            }
        }
        if ( !$default_language ) {
            $default_language = reset( $languages );
        }

        return $default_language ?? config( 'options.defaults.language' );
    }

    /**
     * Finds a language configuration based on a given value.
     *
     * @param mixed $value The value to search for, which can match the 'value', 'id', or 'code' of a language configuration.
     *
     * @return array|null Returns the matching language configuration as an array if found, or null if no match is found or an exception occurs.
     */
    public function search( $value ): ?array {
        try {
            $languages = $this->all();
            if ( ! is_array( $languages ) ) {
                return null;
            }

            foreach ( $languages as $config ) {
                foreach($config as $property) {
                    if (is_string($property) && is_string($value)) {
                        if (strtolower($value) === strtolower($property)) {
                            return $config;
                        }
                    } else {
                        if ($property === $value) {
                            return $config;
                        }
                    }
                }
            }
            return null;
        } catch ( BiblePluginException $e ) {
            return null;
        }
    }

    /**
     * Finds a language configuration based on a specified value.
     *
     * @param mixed $value The specified value to search for, which can match the 'value' or 'id' fields of a language configuration.
     *
     * @return array|null Returns the matching language configuration as an array if found, or null if no match is found or an exception occurs.
     */
    public function find( $value ): ?array {
        try {
            $languages = $this->all();
            if ( ! is_array( $languages ) ) {
                return null;
            }

            foreach ( $languages as $config ) {
                if ( strtolower($config['value'] ?? '') === strtolower($value)
                    || ($config['id'] ?? null) === $value ) {
                    return $this->format($config);
                }
            }
            return null;
        } catch ( BiblePluginException $e ) {
            return null;
        }
    }

    /**
     * Formats a language configuration by ensuring it contains 'id' and 'code' keys.
     *
     * @param array $language The language configuration to format. It can include 'id', 'code', or 'value' keys.
     *
     * @return array Returns the formatted language configuration with 'id' and 'code' keys set, using 'value' if necessary.
     */
    public function format( $language ) {
        if (!isset($language['id'])) {
            $language['id'] = $language['value'];
        }
        if (!isset($language['code'])) {
            $language['code'] = $language['value'];
        }
        return $language;
    }
}
