<?php

namespace Tests\Paratext;

use CodeZone\Bible\Sources\Paratext\ParatextExtractor;
use Tests\TestCase;

/**
 * @group services
 * @group paratext
 */
class ParatextExtractorTest extends TestCase
{
    /** @test */
    public function it_accepts_usx_directories()
    {
        $extractor = new ParatextExtractor();
        $dir = dirname( __DIR__ ) . '/fixtures/paratext/SUS';

        // Sanity check: directory and at least one .usfm file exist
        $this->assertDirectoryExists( $dir, 'USX fixture directory not found' );
        $has_usx = false;
        $rii = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ) );
        foreach ( $rii as $file ) {
            if ( $file->isFile() && strtolower( $file->getExtension() ) === 'usx' ) {
                $has_usx = true;
                break;
            }
        }
        $this->assertTrue( $has_usx, 'Expected at least one .usx file in fixtures' );

        // The extractor check should return true for USFM-only directories
        $this->assertTrue( $extractor->check( $dir ) );
    }
}
