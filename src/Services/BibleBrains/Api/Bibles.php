<?php

namespace CodeZone\Bible\Services\BibleBrains\Api;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Reference;

class Bibles extends ApiService {
    protected $endpoint = 'bibles';
    protected array $default_options = [
        'limit' => 500,
    ];

    /**
     * Finds a Bible by code or falls back to the default for the language.
     *
     * @param string|null $code
     * @param int|null $language_id
     * @param array $query
     * @return array
     * @throws BibleBrainsException
     */
    public function find_or_default( $code = null, $language_id = null, array $query = [] ): array {
        if ( empty( $code ) && empty( $language_id ) ) {
            throw new BibleBrainsException( esc_html( __( 'Either a bible ID or a language ID must be provided.', 'bible-plugin' ) ) );
        }

        if ( empty( $code ) ) {
            return $this->default_for_language( $language_id );
        }

        $result = $this->find( $code, $query );

        if ( empty( $result['data'] ) ) {
            $result = $this->default_for_language( $language_id );
        }

        return $result;
    }

    /**
     * Gets books from a Bible code.
     *
     * @param string $code
     * @param array $query
     * @return array
     * @throws BibleBrainsException
     */
    public function books( $code, $query = [] ): array {
        return $this->find( $code, $query )['data']['books'] ?? [];
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
     * Returns Bibles filtered by language code.
     *
     * @param string $language_code
     * @param array $query
     * @return array
     * @throws BibleBrainsException
     */
    public function for_language( string $language_code, array $query = [] ): array {
        $query = array_merge( $query, [ 'language_code' => $language_code ] );
        return $this->all( $query );
    }

    /**
     * Returns Bibles for multiple language codes.
     *
     * @param array $language_codes
     * @param array $query
     * @return array
     * @throws BibleBrainsException
     */
    public function for_languages( array $language_codes, array $query = [] ): array {
        $result = [ 'data' => [] ];
        foreach ( $language_codes as $language_code ) {
            $data = $this->for_language( $language_code, $query )['data'] ?? [];
            $result['data'] = array_merge( $result['data'], $data );
        }
        return $result;
    }

    /**
     * Gets default Bible for a single language.
     *
     * @param string $language_id
     * @return array
     * @throws BibleBrainsException
     */
    public function default_for_language( string $language_id ): array {
        $bible = $this->for_language( $language_id );
        return $this->find( $bible['data'][0]['abbr'] );
    }

    /**
     * Gets default Bibles for multiple languages.
     *
     * @param array $language_codes
     * @return array
     * @throws BibleBrainsException
     */
    public function default_for_languages( array $language_codes ): array {
        $result = [ 'data' => [] ];
        foreach ( $language_codes as $language_code ) {
            $language = $this->default_for_language( $language_code );
            $result['data'][] = $language['data'];
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
     * @throws BibleBrainsException
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
     * @throws BibleBrainsException
     */
    public function reference( $reference, $fileset ): array {
        [$book, $chapter, $verse_start, $verse_end] = Reference::spread( $reference );
        return $this->get("{$this->endpoint}/filesets/{$fileset}/{$book}/{$chapter}", [
            'verse_start' => $verse_start,
            'verse_end'   => $verse_end
        ]);
    }
}
