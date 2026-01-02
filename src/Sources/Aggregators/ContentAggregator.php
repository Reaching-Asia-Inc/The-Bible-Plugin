<?php

namespace CodeZone\Bible\Sources\Aggregators;

use CodeZone\Bible\Sources\Aggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsContentNormalizer;
use CodeZone\Bible\Sources\Normalizer;
use CodeZone\Bible\Sources\USX\Normalizers\USXContentNormalizer;
use CodeZone\Bible\Sources\USX\USXBibles;

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
    private USXBibles $usx_bibles;
    protected Normalizer $bb_normalizer;
    protected Normalizer $usx_normalizer;

    /**
     * Constructor method to initialize dependencies.
     *
     * @param BibleBrainsBibles $bibles Instance of the BibleBrainsBibles class for managing Bible-related operations.
     * @param USXBibles $usx_bibles Instance of the USXBiblesSetting class for handling USX Bible data.
     * @param BibleBrainsContentNormalizer $bb_normalizer Instance of the BibleBrainsContentNormalizer class for normalizing content for BibleBrains resources.
     * @param USXContentNormalizer $usx_normalizer Instance of the USXBibleNormalizer class for normalizing USX Bible content.
     *
     * @return void
     */
    public function __construct(
        BibleBrainsBibles $bibles,
        USXBibles $usx_bibles,
        BibleBrainsContentNormalizer $bb_normalizer,
        USXContentNormalizer $usx_normalizer
    ) {
        $this->bb_bibles = $bibles;
        $this->usx_bibles = $usx_bibles;
        $this->bb_normalizer = $bb_normalizer;
        $this->usx_normalizer = $usx_normalizer;
    }

    /**
     * Retrieves content from both USX and BibleBrains sources for a given Bible, book, chapter, and specified verse range.
     *
     * @param string $bible The identifier of the Bible to fetch content for.
     * @param string $book The book within the Bible for which content is being requested.
     * @param int $chapter The chapter within the book for which content is being fetched.
     * @param int $verse_start The starting verse of the range for content retrieval.
     * @param int $verse_end The ending verse of the range for content retrieval.
     *
     * @return array Aggregated content from both USX and BibleBrains sources.
     */
    public function content( $bible, $book, $chapter, $verse_start, $verse_end ): array
    {
        $usx = $this->usx_bibles->usx( $bible );
        $usx_content = $usx ? $usx->content( $book, $chapter, $verse_start, $verse_end ) : null;
        $bb_content = $this->bb_bibles->content( $bible, $book, $chapter, $verse_start, $verse_end );
        return $this->choose(
            $usx_content && count( $usx_content ) ? $this->normalize_usx_items( $usx_content ) : null,
            $this->normalize_bb_items( $bb_content ),
        );
    }
}
