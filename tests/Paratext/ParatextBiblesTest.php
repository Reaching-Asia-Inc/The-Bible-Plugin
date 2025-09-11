<?php

namespace Tests\Paratext;

use CodeZone\Bible\Settings\LocalBiblesSetting;
use CodeZone\Bible\Sources\Paratext\Paratext;
use CodeZone\Bible\Sources\Paratext\ParatextBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group services
 * @group paratext
 */
class ParatextBiblesTest extends TestCase
{
    /**
     * @var ParatextBibles
     */
    protected $service;

    /**
     * @var Paratext|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $usx;

    /**
     * @var array
     */
    protected $test_bibles = [];
    protected $mocked_paratext_instance;
    protected $paratext;
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a mock that factory() will return
        $this->mocked_paratext_instance = $this->createMock( Paratext::class );

        // 2. Create a mock Paratext "factory" that returns the mocked instance
        $this->paratext = $this->createMock( Paratext::class );
        $this->paratext->method( 'factory' )->willReturn( $this->mocked_paratext_instance );

        // 3. Inject into the service
        $this->service = new ParatextBibles( $this->paratext, container()->get( LocalBiblesSetting::class ) );

        $this->test_bibles = [
            'KJV' => [
                'id' => 'KJV',
                'name' => 'King James Version',
                'abbreviation' => 'KJV',
                'language' => 'eng',
                'scope' => 'Bible',
                'path' => '/path/to/KJV',
                'added_at' => '2023-01-01 00:00:00'
            ],
            'NIV' => [
                'id' => 'NIV',
                'name' => 'New International Version',
                'abbreviation' => 'NIV',
                'language' => 'eng',
                'scope' => 'Bible',
                'path' => '/path/to/NIV',
                'added_at' => '2023-01-02 00:00:00'
            ]
        ];

        // 5. Patch plugin option functions
        \Patchwork\redefine('CodeZone\Bible\get_plugin_option', function ( $option, $default = null ) {
            return $option === 'local_bibles' ? $this->test_bibles : $default;
        });

        \Patchwork\redefine('CodeZone\Bible\set_plugin_option', function ( $option, $value ) {
            if ( $option === 'local_bibles' ) {
                $this->test_bibles = $value;
                return true;
            }
            return false;
        });
    }

    /**
     * @test
     */
    public function it_can_get_all_bibles()
    {
        $bibles = $this->service->all();

        $this->assertIsArray( $bibles );
        $this->assertCount( 2, $bibles );
        $this->assertArrayHasKey( 'KJV', $bibles );
        $this->assertArrayHasKey( 'NIV', $bibles );
    }

    /**
     * @test
     */
    public function it_can_find_a_bible_by_key()
    {
        $bible = $this->service->find( 'KJV' );

        $this->assertIsArray( $bible );
        $this->assertEquals( 'King James Version', $bible['name'] );
        $this->assertEquals( 'KJV', $bible['abbreviation'] );
        $this->assertNull( $this->service->paratext( 'non_existent' ) );
    }

    /**
     * @test
     */
    public function it_can_get_a_paratext_instance_for_a_bible()
    {
        // Expect methods on the mocked instance (not the factory base)
        $this->mocked_paratext_instance->expects( $this->never() )->method( 'set_directory' ); // It's no longer called
        $this->mocked_paratext_instance->method( 'get_translation_name' )->willReturn( 'King James Version' );

        $paratext = $this->service->paratext( 'KJV' );

        $this->assertSame( $this->mocked_paratext_instance, $paratext );
        $this->assertNull( $this->service->paratext( 'non_existent' ) );
    }

    /**
     * @test
     */
    public function it_can_extract_and_add_a_bible_from_zip_success()
    {
        $this->mocked_paratext_instance->expects( $this->once() )
            ->method( 'extract_zip' )
            ->with( '/path/to/zip/file.zip', 'extracted' )
            ->willReturn( '/path/to/extracted' );

        $this->mocked_paratext_instance->expects( $this->once() )
            ->method( 'get_translation_name' )
            ->willReturn( 'Test Bible' );

        $this->mocked_paratext_instance->expects( $this->once() )
            ->method( 'get_translation_abbreviation' )
            ->willReturn( 'test' );

        $this->mocked_paratext_instance->expects( $this->once() )
            ->method( 'get_language_code' )
            ->willReturn( 'eng' );

        $result = $this->service->extract_and_add( '/path/to/zip/file.zip', 'test', 'extracted' );
        $this->assertEquals( 'test', $result );
        $this->assertArrayHasKey( 'test', $this->test_bibles );
        $this->assertEquals( 'Test Bible', $this->test_bibles['test']['name'] );
    }
}
