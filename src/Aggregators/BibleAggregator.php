<?php

namespace CodeZone\Bible\Aggregators;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsBibleNormalizer;
use CodeZone\Bible\Normalizers\Normalizer;
use CodeZone\Bible\Normalizers\Usx\USXBibleNormalizer;
use CodeZone\Bible\Services\USXBibles;

/**
 * Class BibleAggregator
 *
 * This class is responsible for aggregating Bible data from two different sources:
 * `BibleBrainsBibles` and `USXBibles`. The aggregated data is normalized using
 * the respective normalizer for each source.
 */
class BibleAggregator extends Aggregator
{
    const COMPARE_KEY = "code";
    private BibleBrainsBibles $bb_bibles;
    private USXBibles $usx_bibles;
    protected Normalizer $bb_normalizer;
    protected Normalizer $usx_normalizer;

    public function __construct(
        BibleBrainsBibles $bibles,
        USXBibles $usx_bibles,
        BibleBrainsBibleNormalizer $bb_normalizer,
        USXBibleNormalizer $usx_normalizer,
    ) {
        $this->bb_bibles = $bibles;
        $this->usx_bibles = $usx_bibles;
        $this->bb_normalizer = $bb_normalizer;
        $this->usx_normalizer = $usx_normalizer;
    }

    /**
     * Searches for data based on the provided query and parameters.
     *
     * @param string $query The search keyword or phrase.
     * @param array $params Additional parameters to refine the search.
     * @return array The aggregated search results from multiple sources.
     */
    public function all( array $params = [] ): array
    {
        return $this->aggregate(
            $this->usx_bibles->all(),
            $this->bb_bibles->all( $params )
        );
    }

    public function for_language( $language_id, $params = [] ): array
    {
        return $this->aggregate(
            $this->usx_bibles->for_language( $language_id ),
            $this->bb_bibles->for_language( $language_id, $params )
        );
    }

    /**
     * Searches for data based on the provided query and parameters.
     *
     * @param string $query The search keyword or phrase.
     * @param array $params Additional parameters to refine the search.
     * @return array The aggregated search results from multiple sources.
     */
    public function search( string $query, array $params ): array
    {
    return $this->aggregate(
        $this->usx_bibles->search( $query ),
        $this->bb_bibles->search( $query, $params )
    );
    }

    /**
     * Retrieves an item based on the provided identifier and parameters.
     *
     * @param mixed $id The identifier of the item to find.
     * @param array $params Additional parameters to refine the retrieval.
     * @return array The aggregated data of the found item from multiple sources.
     */
    public function find( $id, array $params ): array
    {
        return $this->aggregate_item(
            $this->usx_bibles->find( $id ),
            $this->bb_bibles->find( $id, $params )
        );
    }

    /**
     * Retrieves the default language data for the specified language ID.
     *
     * @param int $language_id The ID of the language for which the default data is requested.
     * @return array An array containing the data with source information.
     */
    public function language_default( $language_id ): array {
        $usx_bibles = $this->usx_bibles->for_language( $language_id );
        $bb_bibles = $this->bb_bibles->language_defualt( $language_id );

        return $this->aggregate_item( $usx_bibles[0] ?? null, $bb_bibles );
    }

    /**
     * Retrieves the content of a specific Bible passage.
     *
     * @param mixed $fileset The identifier of the Bible fileset.
     * @param mixed $book The book of the Bible.
     * @param mixed $chapter The chapter within the book.
     * @param mixed $verse_start The starting verse of the passage.
     * @param mixed $verse_end The ending verse of the passage.
     * @return array The content of the specified Bible passage.
     */
    public function content( $fileset, $book, $chapter, $verse_start, $verse_end ): array
    {
        if ( $fileset === 'text_plain' ) {
            $usx = $this->usx_bibles->usx( $fileset );
            $usx_bibles = $usx ? $usx->content( $book, $chapter, $verse_start, $verse_end ) : null;
        }
        $this->bb_bibles->content( $fileset, $book, $chapter, $verse_start, $verse_end );
    }
}
