<?php

namespace CodeZone\Bible\Aggregators;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsLanguages;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsLanguageNormalizer;
use CodeZone\Bible\Normalizers\Normalizer;
use CodeZone\Bible\Normalizers\Usx\USXLanguageNormalizer;
use CodeZone\Bible\Services\USXLanguages;

/**
 * Handles the aggregation of language data from multiple sources.
 */
class LanguageAggregator extends Aggregator
{
    private BibleBrainsLanguages $bb_languages;
    private USXLanguages $usx_languages;
    protected Normalizer $bb_normalizer;
    protected Normalizer $usx_normalizer;

    /**
     * Initializes the class with the provided language handlers.
     *
     * @param BibleBrainsLanguages $languages Instance of the BibleBrainsLanguages handler.
     * @param USXLanguages $usx_languages Instance of the USXLanguages handler.
     * @return void
     */
    public function __construct(
        BibleBrainsLanguages $languages,
        USXLanguages $usx_languages,
        USXLanguageNormalizer $usx_normalizer,
        BibleBrainsLanguageNormalizer $bb_normalizer
    ) {
        $this->bb_languages = $languages;
        $this->usx_languages = $usx_languages;
        $this->bb_normalizer = $bb_normalizer;
        $this->usx_normalizer = $usx_normalizer;
    }

    /**
     * Retrieves and aggregates all language data from different sources.
     *
     * @param array $params Optional parameters to filter or modify the retrieved data.
     * @return array Aggregated list of languages from the specified sources.
     */
    public function all( array $params = [] ) {
       $usx_languages = $this->usx_languages->all();
       $bb_languages = $this->bb_languages->all( $params );

       return $this->aggregate( $usx_languages, $bb_languages );
    }

    /**
     * Finds the specified entity by its identifier and aggregates data from multiple sources.
     *
     * @param mixed $id The identifier of the entity to find.
     * @return mixed The aggregated result from the retrieved data.
     */
    public function find( $id ) {
        $usx_language = $this->usx_languages->find( $id );
        $bb_language = $this->bb_languages->find( $id );

        return $this->aggregate_item( $usx_language, $bb_language );
    }

    /**
     * Searches for languages in both usx_languages and bb_languages data sources.
     *
     * @param string $query The search query used to perform the lookup.
     * @param array $params Optional parameters to filter or modify the search results.
     * @return array The aggregated search results from both data sources.
     */
    public function search(string $query, array $params = []) {
        $usx_languages = $this->usx_languages->search( $query );
        $bb_languages = $this->bb_languages->search( $query, $params );

        return $this->aggregate( $usx_languages, $bb_languages );
    }
}
