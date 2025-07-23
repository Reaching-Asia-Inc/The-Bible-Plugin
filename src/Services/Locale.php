<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsLanguages;
use CodeZone\Bible\WhiteCube\Lingua\Service as Lingua;

/**
 * Represents a locale and provides various utility methods for working with language-specific data,
 * such as translations, language codes, and text direction determination.
 */
class Locale
{
    protected Translations $translations;

    /**
     * Constructor method for initializing the object with translations.
     *
     * @param Translations $translations An instance of the Translations class.
     * @return void
     */
    public function __construct( Translations $translations ) {
        $this->translations = $translations;
    }

    /**
     * Creates and returns an instance of the Lingua class using the current locale.
     *
     * @return Lingua An instance of the Lingua class initialized with the current locale.
     * @throws \Exception
     */
    public function lingua() {
        return Lingua::create( $this->locale() );
    }

    /**
     * Get the resolved locale from the translations object.
     *
     * @return string The resolved locale.
     */
    public function locale() {
        return $this->translations->resolve_locale();
    }

    /**
     * Converts the current language setting to its ISO 639-3 code representation.
     *
     * @return string Returns the ISO 639-3 language code corresponding to the current language.
     * @throws \Exception
     */
    public function iso() {
        return $this->lingua()->toISO_639_3();
    }

    /**
     * Determines the text direction of the given input (Right-To-Left, Left-To-Right, or Auto).
     *
     * @param mixed $text The input text to analyze. It can be a string, an array of strings,
     *                or an array of associative arrays with a 'verse_text' key.
     *
     * @return string Returns 'rtl' if the majority of detected characters belong to Right-To-Left scripts,
     *                'ltr' if the majority belongs to Left-To-Right scripts,
     *                or 'auto' if no clear direction can be determined.
     */
    public function guess_text_direction( $text )
    {
        if ( is_array( $text ) ) {
            if ( !count( $text ) ) {
                return 'auto';
            }
            if ( is_string( $text[0] ) ) {
                $text = $text[0];
            }
            if ( isset( $text[0]['verse_text'] ) ) {
                $text = array_map(function ( $item ) {
                    return $item['verse_text'];
                }, $text);
                $text = implode( ' ', $text );
            }
        }

        // Remove spaces and common punctuation to focus on actual text
        $text = preg_replace( '/[\s\p{P}]/u', '', $text );

        // Arabic, Hebrew, and other RTL scripts
        $rtl_chars = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\x{0590}-\x{05FF}]/u';

        // Basic Latin and common LTR scripts, excluding spaces and punctuation
        $ltr_chars = '/[\x{0041}-\x{005A}\x{0061}-\x{007A}\x{00C0}-\x{024F}]/u';

        preg_match_all( $rtl_chars, $text, $rtl_matches );
        preg_match_all( $ltr_chars, $text, $ltr_matches );

        $rtl_count = count( $rtl_matches[0] );
        $ltr_count = count( $ltr_matches[0] );

        if ( $rtl_count > $ltr_count ) {
            return 'rtl';
        } elseif ( $ltr_count >= $rtl_count ) {
            return 'ltr';
        }

        return 'auto';
    }
}
