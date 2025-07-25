<?php

namespace CodeZone\Bible\Sources\Aggregators;

use CodeZone\Bible\Sources\Aggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsBibleNormalizer;
use CodeZone\Bible\Sources\Normalizer;
use CodeZone\Bible\Sources\USX\Normalizers\USXBibleNormalizer;
use CodeZone\Bible\Sources\USX\USXBibles;

/**
 * Class BibleAggregator
 *
 * This class is responsible for aggregating Bible data from two different sources:
 * `BibleBrainsBibles` and `USXBibles`. The aggregated data is normalized using
 * the respective normalizer for each source.
 */
class BibleAggregator extends Aggregator
{
    const COMPARE_KEY = "name";
    private BibleBrainsBibles $bb_bibles;
    private USXBibles $usx_bibles;
    protected Normalizer $bb_normalizer;
    protected Normalizer $usx_normalizer;

    public function __construct(
        BibleBrainsBibles $bibles,
        USXBibles $usx_bibles,
        BibleBrainsBibleNormalizer $bb_normalizer,
        USXBibleNormalizer $usx_normalizer
    ) {
        $this->bb_bibles = $bibles;
        $this->usx_bibles = $usx_bibles;
        $this->bb_normalizer = $bb_normalizer;
        $this->usx_normalizer = $usx_normalizer;
    }

    /**
     * Retrieves all data based on the provided parameters.
     *
     * @param array $params Optional parameters to refine the data retrieval.
     * @return array The aggregated results from multiple sources.
     */
    public function all( array $params = [] ): array
    {
        return $this->aggregate(
            $this->usx_bibles->all(),
            $this->bb_bibles->all( $params )
        );
    }

    /**
     * Retrieves data for the specified language, utilizing additional parameters if provided.
     *
     * @param int|string $language_id The ID or identifier of the language.
     * @param array $params Optional parameters to customize the data retrieval.
     * @return array The aggregated data for the specified language from multiple sources.
     */
    public function for_language( $language_id, $params = [] ): array
    {
        $bible_brains_bibles = $this->bb_bibles->for_language( $language_id, $params );
        $language_code = null;
        if ( count( $bible_brains_bibles ) && is_numeric( $language_id ) ) {
           $language_code = $bible_brains_bibles[0]['iso'] ?? null;
        }
        return $this->aggregate(
            $this->usx_bibles->for_language( $language_code ?? $language_id ),
            $bible_brains_bibles
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
    public function find( $id, array $params ): ?array
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
    public function language_default( $language_id ): ?array {
        $bb_bible = $this->bb_bibles->language_defualt( $language_id );
        $language_code = null;
        if ( $bb_bible && is_numeric( $language_id ) ) {
            $language_code = $bb_bible['iso'] ?? null;
        }
        $usx_bibles = array_values( $this->usx_bibles->for_language( $language_code ?? $language_id ) );

        return $this->aggregate_item( $usx_bibles[0] ?? null, $bb_bible );
    }
}
