<?php

namespace CodeZone\Bible\Aggregators;

use CodeZone\Bible\ApiServices\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Normalizers\BibleBrains\BibleBrainsBookNormalizer;
use CodeZone\Bible\Normalizers\Usx\USXBookNormalizer;
use CodeZone\Bible\Services\USXBibles;

class BookAggregator extends Aggregator
{
    public function __construct(
        BibleBrainsBibles         $bb_bibles,
        USXBibles                 $usx_bibles,
        BibleBrainsBookNormalizer $bb_normalizer,
        USXBookNormalizer         $usx_normalizer
    ) {
        $this->bb_bibles = $bb_bibles;
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
    public function all( string $bible, array $params = [] ): array
    {
        $usx = $this->usx_bibles->usx( $bible );
        return $this->choose(
            $usx ? $usx->books() : null,
            $this->bb_bibles->books( $bible, $params )
        );
    }

    /**
     * Searches for data based on the provided query and parameters.
     *
     * @param string $query The search keyword or phrase.
     * @param array $params Additional parameters to refine the search.
     * @return array The aggregated search results from multiple sources.
     */
    public function find( string $book, string $bible ): ?array
    {
        $usx = $this->usx_bibles->usx( $bible );
        return $this->choose(
            $usx ? $usx->book( $book ) : null,
            $this->bb_bibles->book( $bible, $book )
        );
    }
}
