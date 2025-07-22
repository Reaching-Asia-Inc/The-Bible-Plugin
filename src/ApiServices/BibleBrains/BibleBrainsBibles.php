<?php

namespace CodeZone\Bible\ApiServices\BibleBrains;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Services\References;

class BibleBrainsBibles extends BibleBrainsService {
    protected $endpoint = 'bibles';
    protected array $default_options = [
        'limit' => 500,
    ];

    /**
     * Gets books from a Bible code.
     *
     * @param string $code
     * @param array $query
     * @return array
     * @throws BiblePluginException
     */
    public function books( $code, $query = [] ): array {
        return $this->find( $code, $query )['books'] ?? [];
    }

    /**
     * Gets books from a Bible code.
     *
     * @param string $code
     * @param array $query
     * @return array
     * @throws BiblePluginException
     */
    public function book( $bible, $code, $query = [] ): ?array {
        $book = $this->books( $bible, $query );
        foreach ( $book as $book_data ) {
            if ( $book_data['book_id'] === $code
            || $book_data['name'] === $code
            || ($book_data['name_short'] ?? null) === $code) {
                return $book_data;
            }
        }
        return null;
    }

    /**
     * Maps a Bible record to an option format.
     *
     * @param array $record
     * @return array
     */
    public function map_option( array $record ): array {
        return [
            'value'    => $record['abbr'] ?? $record['id'] ?? null,
            'itemText' => $record['name'] ?? null
        ];
    }

    /**
     * Returns BibleBrainsBibles filtered by language code.
     *
     * @param string $language_code
     * @param array $query
     * @return array
     * @throws BiblePluginException
     */
    public function for_language( string $language_code, array $query = [] ): array {
        $query = array_merge( $query, [ 'language_code' => $language_code ] );
        return $this->all( $query );
    }

    /**
     * Returns BibleBrainsBibles for multiple language codes.
     *
     * @param array $language_codes
     * @param array $query
     * @return array
     * @throws BiblePluginException
     */
    public function for_languages( array $language_codes, array $query = [] ): array {
        $result = [];
        foreach ( $language_codes as $language_code ) {
            $data = $this->for_language( $language_code, $query ) ?? [];
            $result[] = array_merge( $result, $data );
        }
        return $result;
    }

    /**
     * Gets default Bible for a single language.
     *
     * @param string $language_id
     * @return array
     * @throws BiblePluginException
     */
    public function language_defualt( string $language_id ): array {
        $bible = $this->for_language( $language_id );
        return $this->find( $bible[0]['abbr'] );
    }

    /**
     * Gets default BibleBrainsBibles for multiple languages.
     *
     * @param array $language_codes
     * @return array
     * @throws BiblePluginException
     */
    public function languages_default( array $language_codes ): array {
        $result = [];
        foreach ( $language_codes as $language_code ) {
            $language = $this->language_defualt( $language_code );
            $result[] = $language;
        }
        return $result;
    }

    /**
     * Gets passage content from a fileset.
     *
     * @param string $fileset
     * @param string $book
     * @param int $chapter
     * @param int $verse_start
     * @param int $verse_end
     * @return array
     * @throws BiblePluginException
     */
    public function content( $fileset, $book, $chapter, $verse_start, $verse_end ): array {
        return $this->get($this->endpoint . "/filesets/{$fileset}/{$book}/{$chapter}", [
            'verse_start' => $verse_start,
            'verse_end'   => $verse_end
        ]);
    }

    /**
     * Gets a passage from a reference string and fileset.
     *
     * @param string $reference
     * @param string $fileset
     * @return array
     * @throws BiblePluginException
     */
    public function reference( $reference, $fileset ): array {
        [$book, $chapter, $verse_start, $verse_end] = References::spread( $reference );
        return $this->get("{$this->endpoint}/filesets/{$fileset}/{$book}/{$chapter}", array_filter([
            'verse_start' => $verse_start,
            'verse_end'   => $verse_end
        ]));
    }
}
