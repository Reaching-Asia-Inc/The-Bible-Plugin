<?php

namespace Tests\Reproduction;

use CodeZone\Bible\Resources\Bible;
use CodeZone\Bible\Sources\Aggregators\BibleAggregator;
use CodeZone\Bible\Sources\Aggregators\LanguageAggregator;
use CodeZone\Bible\Sources\BibleBrains\BibleBrainsBibles;
use CodeZone\Bible\Sources\USX\USXBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;

class LanguageLoadingTest extends TestCase
{
    /**
     * @test
     */
    public function it_fails_to_load_bibles_for_uppercase_language_name()
    {
        $bible_resource = container()->get(Bible::class);

        // This simulates what happens when the API is called with language=ENGLISH
        // We expect it to return bibles if resolved correctly, but currently it likely fails
        // because "ENGLISH" is passed directly to Bible Brains.

        $results = $bible_resource->all(['language_id' => 'ENGLISH']);

        // If it's not working, $results will be empty or only contain USX bibles if matched
        // But since we are in a test environment, we might not have any real data.

        // Let's mock the aggregator instead to see the call.
    }

    /**
     * @test
     */
    public function it_resolves_language_name_in_aggregator()
    {
        $bb_bibles = $this->createMock(BibleBrainsBibles::class);
        $usx_bibles = $this->createMock(USXBibles::class);
        $bb_normalizer = $this->createMock(\CodeZone\Bible\Sources\BibleBrains\Normalizers\BibleBrainsBibleNormalizer::class);
        $usx_normalizer = $this->createMock(\CodeZone\Bible\Sources\USX\Normalizers\USXBibleNormalizer::class);

        // We need to inject LanguageAggregator or use a real one if possible
        // But BibleAggregator currently doesn't take LanguageAggregator.

        $aggregator = new BibleAggregator($bb_bibles, $usx_bibles, $bb_normalizer, $usx_normalizer);

        // Mocking for_language to see what it receives
        $bb_bibles->expects($this->atLeastOnce())
            ->method('for_language')
            ->with($this->equalTo('ENGLISH'))
            ->willReturn([]);

        $aggregator->for_language('ENGLISH');
    }
}
