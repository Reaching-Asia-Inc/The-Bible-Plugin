<?php

namespace CodeZone\Bible\Sources\Aggregators;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Sources\Aggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsBookNormalizer;
use CodeZone\Bible\Sources\USX\Normalizers\USXBookNormalizer;
use CodeZone\Bible\Sources\USX\USXBibles;

class BookAggregator extends Aggregator
{
    const COMPARE_KEY = "id";

    public function __construct(
        BibleBrainsBibles $bb_bibles,
        USXBibles $usx_bibles,
        BibleBrainsBookNormalizer $bb_normalizer,
        USXBookNormalizer $usx_normalizer
    ) {
        $this->bb_bibles = $bb_bibles;
        $this->usx_bibles = $usx_bibles;
        $this->bb_normalizer = $bb_normalizer;
        $this->usx_normalizer = $usx_normalizer;
    }

    /**
     * Retrieves all books from the specified Bible, combining results from different sources.
     *
     * @param string $bible The identifier of the Bible to retrieve books from.
     * @param array $params Optional parameters to filter or modify the retrieved data.
     * @return array A collection of books aggregated from multiple sources.
     * @throws BiblePluginException
     */
    public function all( string $bible, array $params = [] ): array
    {
        $usx = $this->usx_bibles->usx( $bible );
        return $this->aggregate(
            $usx ? $usx->books() : [],
            $this->bb_bibles->books( $bible, $params )
        );
    }

    /**
     * Finds and aggregates information about the specified book and bible.
     *
     * @param string $book The name of the book to find.
     * @param string $bible The identifier of the bible to use.
     * @return array|null Returns an aggregated array of book data, or null if not found.
     * @throws BiblePluginException
     */
    public function find( string $book, string $bible ): ?array
    {
        $usx = $this->usx_bibles->usx( $bible );
        return $this->aggregate_item(
            $usx ? $usx->book( $book ) : null,
            $this->bb_bibles->book( $bible, $book )
        );
    }
}
