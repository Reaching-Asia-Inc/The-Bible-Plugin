<?php

namespace CodeZone\Bible\Sources\Aggregators;

use CodeZone\Bible\Sources\Aggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsLanguages;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsLanguageNormalizer;
use CodeZone\Bible\Sources\Normalizer;
use CodeZone\Bible\Sources\Paratext\Normalizers\ParatextLanguageNormalizer;
use CodeZone\Bible\Sources\Paratext\ParatextLanguages;

/**
 * Handles the aggregation of language data from multiple sources.
 */
class LanguageAggregator extends Aggregator
{
    private BibleBrainsLanguages $bb_languages;
    private ParatextLanguages $paratext_languages;
    protected Normalizer $bb_normalizer;
    protected Normalizer $paratext_normalizer;

    /**
     * Initializes the class with the provided language handlers.
     *
     * @param BibleBrainsLanguages $languages Instance of the BibleBrainsLanguages handler.
     * @param ParatextLanguages $paratext_languages Instance of the ParatextLanguages handler.
     * @return void
     */
    public function __construct(
        BibleBrainsLanguages $languages,
        ParatextLanguages $paratext_languages,
        ParatextLanguageNormalizer $paratext_normalizer,
        BibleBrainsLanguageNormalizer $bb_normalizer
    ) {
        $this->bb_languages = $languages;
        $this->paratext_languages = $paratext_languages;
        $this->bb_normalizer = $bb_normalizer;
        $this->paratext_normalizer = $paratext_normalizer;
    }

    /**
     * Aggregates and normalizes data from usx_data and bb_data sources and optionally merges them based on a comparison key.
     *
     * @param array $paratext_data The data set from the usx_data source to be aggregated.
     * @param array $bb_data The data set from the bb_data source to be aggregated.
     * @param string|null $compare Optional comparison key used for merging the data sets.
     * @return array The aggregated and merged result from both data sources.
     */
    public function aggregate( array $paratext_data, array $bb_data, ?string $compare = null ): array
    {
        // Delegate to the base implementation to keep Paratext as primary
        return parent::aggregate( $paratext_data, $bb_data, $compare );
    }

    /**
     * Retrieves and aggregates all language data from different sources.
     *
     * @param array $params Optional parameters to filter or modify the retrieved data.
     * @return array Aggregated list of languages from the specified sources.
     */
    public function all( array $params = [] ) {
       $paratext_languages = $this->paratext_languages->all();
       $bb_languages = $this->bb_languages->all( $params );

       return $this->aggregate( $paratext_languages, $bb_languages );
    }

    /**
     * Finds the specified entity by its identifier and aggregates data from multiple sources.
     *
     * @param mixed $id The identifier of the entity to find.
     * @return mixed The aggregated result from the retrieved data.
     */
    public function find( $id ) {
        $paratext_language = $this->paratext_languages->find( $id );
        $bb_language = $this->bb_languages->find( $id );

        return $this->aggregate_item( $paratext_language, $bb_language );
    }

    /**
     * Searches for languages in both usx_languages and bb_languages data sources.
     *
     * @param string $query The search query used to perform the lookup.
     * @param array $params Optional parameters to filter or modify the search results.
     * @return array The aggregated search results from both data sources.
     */
    public function search( string $query, array $params = [] ) {
        $paratext_languages = $this->paratext_languages->search( $query );
        $bb_languages = $this->bb_languages->search( $query, $params );

        return $this->aggregate( $paratext_languages, $bb_languages );
    }
}
