<?php

namespace CodeZone\Bible\Settings;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface;
use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\Locale;
use CodeZone\Bible\Sources\Aggregators\LanguageAggregator;
use function CodeZone\Bible\config;
use function CodeZone\Bible\container;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class LanguagesSetting provides functionality to manage language configurations, including guessing,
 * searching, and validating language settings based on locale or other predefined criteria.
 */
class LanguagesSetting
{
    private Locale $locale;
    private OptionsInterface $options;
    private LanguageAggregator $aggregator;

    const OPTION_KEY = 'languages';

    public function __construct( Locale $locale, OptionsInterface $options, LanguageAggregator $language_aggregator )
    {
        $this->locale = $locale;
        $this->options = $options;
        $this->aggregator = $language_aggregator;
    }

    /**
     * Retrieves and formats all available language configurations.
     *
     * @return array|null Returns an array of formatted language configurations if available, or null if no configurations are found, are not in array format, or an exception occurs.
     */
    public function all(): ?array {
        try {
            $languages = $this->options->get( self::OPTION_KEY, null, true );

            if ( !is_array( $languages ) ) {
                return null;
            }

            $hydrated = $this->format( $languages );

            if ( $hydrated !== $languages ) {
                $this->save( $hydrated );
            }

            return $hydrated;

        } catch ( BiblePluginException $e ) {

            return null;
        }
    }

    /**
     * Saves the provided languages configuration to the plugin options.
     *
     * @param mixed $languages The languages configuration to be saved.
     *
     * @return bool Indicates whether the languages configuration was successfully saved.
     */
    public function save( $languages ) {
        return set_plugin_option( self::OPTION_KEY, $languages );
    }

    /**
     * Attempts to guess the configuration based on the provided ISO code or falls back to the default configuration.
     *
     * @param string|null $iso Optional ISO code to determine the configuration. Defaults to the current locale's ISO code if not provided.
     *
     * @return mixed Returns the configuration matching the ISO code if supported, otherwise returns the default configuration.
     */
    public function guess( $iso = null ) {
        $iso = $iso ?: $this->locale->iso();

        if ( $this->supported( $iso ) ) {
            return $this->search_or_default( $iso );
        }

        return $this->default();
    }

    /**
     * Searches for a language configuration based on a given value, or guesses one if no match is found.
     *
     * @param mixed $value The value to search for, which can be used to identify a language configuration.
     *
     * @return array|null Returns the matching language configuration as an array if found, the guessed configuration if no match is found, or null if no configuration is determined.
     */
    public function search_or_guess( $value ) {
        $language = $value ? $this->search( $value ) : null;
        if ( ! $language ) {
            $language = $this->guess();
        };

        return $language;
    }

    /**
     * Searches for a language configuration based on the given value. If no match is found, returns the default language configuration.
     *
     * @param mixed $value The value to search for, which may match specific properties of a language configuration.
     *
     * @return array|null Returns the matching language configuration as an array if found, or the default configuration if no match is found. Null is returned if an error or exception occurs.
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
        return (bool) $this->find( $value );
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
                foreach ( $config as $property ) {
                    if ( is_string( $property ) && is_string( $value ) ) {
                        if ( strtolower( $value ) === strtolower( $property ) ) {
                            return $config;
                        }
                    } elseif ( $property === $value ) {
                            return $config;
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
                if ( strtolower( $config['value'] ?? '' ) === strtolower( $value )
                    || ( $config['id'] ?? null ) === $value ) {
                    return $config;
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
     * @param array $items The language configuration to format. It can include 'id', 'code', or 'value' keys.
     *
     * @return array Returns the formatted language configuration with 'id' and 'code' keys set, using 'value' if necessary.
     */
    public function format( array $items ) {
        foreach ( $items as $idx => $item ) {
            $items[$idx] = $this->hydrate_language(
                $this->format_language( $item )
            );
        }
        return $items;
    }

    /**
     * Formats a language item configuration.
     *
     * @param array $item The language item to be formatted.
     *
     * @return array Returns the formatted language item as an array.
     */
    private function format_language( array $item ): array {
        // Migrate 'bible' => 'bibles' for backward compatibility
        if ( (isset( $item['bible'] ) && $item['bible'] !== null && $item['bible'] !== '')
            && (!isset( $item['bibles'] ) || empty( $item['bibles'] )) ) {
            $item['bibles'] = [$item['bible']];
            unset($item['bible']);
        }

        // Normalize 'bibles' to an array of unique, non-empty strings
        if ( isset( $item['bibles'] ) ) {
            if ( is_string( $item['bibles'] ) ) {
                // Support comma-separated storage formats
                $parts = array_map('trim', explode(',', $item['bibles']));
                $item['bibles'] = $parts;
            }
            if ( !is_array( $item['bibles'] ) ) {
                $item['bibles'] = [];
            }

            $item['bibles'] = array_values(array_unique(array_filter($item['bibles'], static function ($v) {
                return is_string($v) && $v !== '';
            })));
        } else {
            $item['bibles'] = [];
        }

        // Ensure default_bible presence/consistency when is_default is true
        $isDefault = !empty($item['is_default']);
        $defaultBible = $item['default_bible'] ?? null;

        // If default selected but no default_bible set, pick first available bible
        if ( $isDefault && (empty($defaultBible)) && !empty($item['bibles']) ) {
            $item['default_bible'] = $item['bibles'][0];
            $defaultBible = $item['default_bible'];
        }

        // If a default_bible exists but not within bibles, add it to bibles
        if ( !empty($defaultBible) && is_string($defaultBible) && !in_array($defaultBible, $item['bibles'], true) ) {
            $item['bibles'] = array_values(array_unique(array_merge([$defaultBible], $item['bibles'])));
        }

        return $item;
    }

    /**
     * Populates a language array with missing properties by retrieving data from an external source.
     *
     * @param array $item The language array, which may contain incomplete data. Expected keys are 'id', 'code', 'source', and 'value'.
     *
     * @return array Returns the updated language array with the 'id', 'code', and 'source' properties populated if they were initially missing and found in the external source.
     */
    private function hydrate_language( array $item ): array {
        if ( empty( $item['id'] )
            || empty( $item['code'] )
            || empty( $item['source'] ) ) {
            $language = $this->aggregator->find( $item['value'] );
            if ( $language ) {
                $item['id'] = $language['id'];
                $item['code'] = $language['code'];
                $item['source'] = $language['source'];
            }
        }

        if ( !empty( $item['bibles'] ) ) {
            $bible_resource = container()->get( \CodeZone\Bible\Resources\Bible::class );
            $bible_id = $item['bibles'];
            $bible = $bible_resource->find( $bible_id );
            if ( $bible ) {
                if ( is_array( $bible_id ) ) {
                    $names = array_map( fn( $b ) => $b['name'] ?? $b['abbr'] ?? $b['code'] ?? '', $bible );
                    $item['bible_name'] = implode( ', ', array_filter( $names ) );
                    $item['bibles_data'] = array_map( fn( $b ) => [
                        'id' => $b['id'] ?? $b['abbr'] ?? $b['code'] ?? '',
                        'name' => $b['name'] ?? $b['abbr'] ?? $b['code'] ?? '',
                    ], $bible );
                } else {
                    $name = $bible['name'] ?? $bible['abbr'] ?? $bible['code'] ?? $bible_id;
                    $item['bible_name'] = $name;
                    $item['bibles_data'] = [ [
                        'id' => $bible['id'] ?? $bible['abbr'] ?? $bible['code'] ?? $bible_id,
                        'name' => $name,
                    ] ];
                }
            }
        }

        return $item;
    }
}
