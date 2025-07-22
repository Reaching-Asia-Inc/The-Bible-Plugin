<?php

namespace CodeZone\Bible\Services;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsLanguages;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\WhiteCube\Lingua\Service as Lingua;

class Locale
{
    protected Translations $translations;

    /**
     * Constructor.
     *
     * @param Options $options The options object.
     * @param BibleBrainsLanguages $languages The languages object.
     * @param Translations $translations The translations object.
     */
    public function __construct( Translations $translations ) {
        $this->translations = $translations;
    }

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
     * Get the ISO 639-3 code for the current locale.
     *
     * @return string The ISO 639-3 code.
     */
    public function iso() {
        return $this->lingua()->toISO_639_3();
    }

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

        // Regular expression to match RTL Unicode characters.
        $rtl_chars = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{0590}-\x{05FF}\x{08A0}-\x{08FF}\x{FB50}-\x{FDCF}\x{FDF0}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';

        // Regular expression to match LTR Unicode characters (excluding RTL ranges).
        $ltr_chars = '/[\x{0000}-\x{058F}\x{0700}-\x{074F}\x{0780}-\x{089F}\x{0900}-\x{FBFF}\x{FE00}-\x{FE6F}]/u';

        // Count the number of RTL and LTR characters in the text.
        $rtl_count = preg_match_all( $rtl_chars, $text );
        $ltr_count = preg_match_all( $ltr_chars, $text );

        // Return the text direction based on the character count.
        if ( $rtl_count > $ltr_count ) {
            return 'rtl';
        } elseif ( $ltr_count >= $rtl_count ) {
            return 'ltr';
        }

        return 'auto';
    }
}
