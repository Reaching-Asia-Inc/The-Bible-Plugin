<?php

namespace CodeZone\Bible\Services;

use function CodeZone\Bible\container;

/**
 * Class References
 *
 * This class provides methods for parsing and spreading book references.
 */
class References {

    /**
     * Splits the given reference into individual components and returns them as an array.
     *
     * @param mixed $reference The reference to be split.
     *
     * @return array An array containing the book, chapter, verse start, and verse end components of the reference.
     */
    public static function spread( $reference ): array {
        $reference = self::parse( $reference );
        return [
            $reference['book'],
            $reference['chapter'],
            $reference['verse_start'],
            $reference['verse_end']
        ];
    }

	/**
	 * Parses the given reference and returns an array.
	 *
	 * @param mixed $reference The reference to be parsed.
	 *
	 * @return array The parsed result as an array.
	 */
	public static function parse( $reference ): array {
		if ( is_array( $reference ) ) {
			return self::factory( $reference );
		}

		return self::parse_string( $reference );
	}


	/**
	 * Parse the given array by calling the normalize_array method.
	 *
	 * @param array $references The array to be parsed.
	 *
	 * @return array The parsed array.
	 */
	public static function factory( array $references ): array {
        // Add source field if not present
        if ( !isset( $references['source'] ) ) {
            $references['source'] = 'bible-brains';
        }
        $books = container()->get( Books::class );
        $references['book'] = $books->to_code( $references['book'] );

        return $references;
	}

	/**
	 * Parse the given string reference and return the normalized array representation.
	 *
	 * @param string $reference The string reference to be parsed.
	 *
	 * @return array The normalized array representation of the parsed string reference.
	 */
	public static function parse_string( string $reference ): array {
		$reference = self::normalize( $reference );

		//Extract the verse from the string reference
        if ( str_contains( $reference, ":" ) ) {
            [$reference_without_verses, $verses] = array_pad( explode( ':', $reference ?? "", 2 ), 2, null );
        } else {
            $reference_without_verses = $reference;
            $verses = null;
        }

        $reference_without_verses = explode( ' ', $reference_without_verses ?? "" );

		//Convert to an array
		$verses = array_pad( explode( '-', $verses ?? "", 2 ), 2, null );

        //If there is only one verse, set the end verse to the start verse
		if ( ! $verses[1] ) {
			$verses[1] = $verses[0];
		}

		$chapter = "1";

		//Extract the chapter from the string reference
		foreach ( $reference_without_verses as $i => $part ) {
			if ( $i && is_numeric( $part ) ) {
				$chapter = $part;
				unset( $reference_without_verses[ $i ] );
			}
		}

		//Assume the book is the remaining parts of the string reference are the book
		$book = implode( ' ', $reference_without_verses );

        return self::factory( [
            'book'        => $book,
            'chapter'     => $chapter,
            'verse_start' => $verses[0],
            'verse_end'   => $verses[1],
        ] );
	}

    /**
     * Normalize the given string reference by replacing double spaces with single space,
     * replacing '–' with '-', removing leading and trailing white spaces, and replacing '—' with '-'.
     *
     * @param string $reference The string reference to be normalized.
     *
     * @return string The normalized string reference.
     */
    private static function normalize( string $reference ): string
    {
        $reference = str_replace( '  ', ' ', $reference );
        $reference = str_replace( '–', '-', $reference );
        $reference = trim( $reference );

        return str_replace( '—', '-', $reference );
    }
}
