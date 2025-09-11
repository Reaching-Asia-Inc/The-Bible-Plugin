<?php

namespace CodeZone\Bible\Sources\Aggregators;

use CodeZone\Bible\Sources\Aggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsContentNormalizer;
use CodeZone\Bible\Sources\Normalizer;
use CodeZone\Bible\Sources\Paratext\Normalizers\ParatextContentNormalizer;
use CodeZone\Bible\Sources\Paratext\ParatextBibles;

/**
 * Class ContentAggregator
 *
 * Aggregates content from multiple Bible sources.
 * It interacts with BibleBrainsBibles and USXBiblesSetting to fetch content
 * while utilizing normalizers for standardizing content before aggregation.
 */
class ContentAggregator extends Aggregator
{
    private BibleBrainsBibles $bb_bibles;
    private ParatextBibles $paratext_bibles;
    protected Normalizer $bb_normalizer;
    protected Normalizer $paratext_normalizer;

    /**
     * Constructor method to initialize dependencies.
     *
     * @param BibleBrainsBibles $bibles Instance of the BibleBrainsBibles class for managing Bible-related operations.
     * @param ParatextBibles $paratext_bibles Instance of the USXBiblesSetting class for handling Paratext Bible data.
     * @param BibleBrainsContentNormalizer $bb_normalizer Instance of the BibleBrainsContentNormalizer class for normalizing content for BibleBrains resources.
     * @param ParatextContentNormalizer $paratext_normalizer Instance of the ParatextBibleNormalizer class for normalizing Paratext Bible content.
     *
     * @return void
     */
    public function __construct(
        BibleBrainsBibles $bibles,
        ParatextBibles $paratext_bibles,
        BibleBrainsContentNormalizer $bb_normalizer,
        ParatextContentNormalizer $paratext_normalizer
    ) {
        $this->bb_bibles = $bibles;
        $this->paratext_bibles = $paratext_bibles;
        $this->bb_normalizer = $bb_normalizer;
        $this->paratext_normalizer = $paratext_normalizer;
    }

    /**
     * Retrieves content from both Paratext and BibleBrains sources for a given Bible, book, chapter, and specified verse range.
     *
     * @param string $bible The identifier of the Bible to fetch content for.
     * @param string $book The book within the Bible for which content is being requested.
     * @param int $chapter The chapter within the book for which content is being fetched.
     * @param int $verse_start The starting verse of the range for content retrieval.
     * @param int $verse_end The ending verse of the range for content retrieval.
     *
     * @return array Aggregated content from both Paratext and BibleBrains sources.
     */
    public function content( $bible, $book, $chapter, $verse_start, $verse_end ): array
    {
        $paratext = $this->paratext_bibles->paratext( $bible );
        $paratext_content = $paratext ? $paratext->content( $book, $chapter, $verse_start, $verse_end ) : null;
        $bb_content = $this->bb_bibles->content( $bible, $book, $chapter, $verse_start, $verse_end );
        return $this->choose(
            $paratext_content && count( $paratext_content ) ? $this->normalize_paratext_items( $paratext_content ) : null,
            $this->normalize_bb_items( $bb_content ),
        );
    }
}
