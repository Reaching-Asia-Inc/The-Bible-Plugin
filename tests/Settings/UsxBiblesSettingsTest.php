<?php

namespace Settings;

use CodeZone\Bible\Settings\USXBiblesSetting;
use CodeZone\Bible\Sources\USX\USX;
use CodeZone\Bible\Sources\USX\USXBibles;
use Tests\TestCase;
use function CodeZone\Bible\container;

class UsxBiblesSettingsTest extends TestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a mock that factory() will return
        $this->mocked_usx_instance = $this->createMock( USX::class );

        // 2. Create a mock USX "factory" that returns the mocked instance
        $this->usx = $this->createMock( USX::class );
        $this->usx->method( 'factory' )->willReturn( $this->mocked_usx_instance );

        // 3. Inject into the service
        $this->service = container()->get( USXBiblesSetting::class );

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
            return $option === 'usx_bibles' ? $this->test_bibles : $default;
        });

        \Patchwork\redefine('CodeZone\Bible\set_plugin_option', function ( $option, $value ) {
            if ( $option === 'usx_bibles' ) {
                $this->test_bibles = $value;
                return true;
            }
            return false;
        });
    }

    /**
     * @test
     */
    public function it_can_add_a_new_bible()
    {
        $new_bible = [
            'id' => 'ESV',
            'name' => 'English Standard Version',
            'abbreviation' => 'ESV',
            'language' => 'eng',
            'scope' => 'Bible',
            'path' => '/path/to/esv',
            'added_at' => '2023-01-03 00:00:00'
        ];

        // Add the new bible
        $result = $this->service->add( 'ESV', $new_bible );

        // Verify that the bible was added
        $this->assertTrue( $result );
        $this->assertCount( 3, $this->test_bibles );
        $this->assertArrayHasKey( 'ESV', $this->test_bibles );
        $this->assertEquals( 'English Standard Version', $this->test_bibles['ESV']['name'] );

        // Test adding a bible with an existing key
        $result = $this->service->add( 'ESV', $new_bible );
        $this->assertFalse( $result );
    }

    /**
     * @test
     */
    public function it_can_update_an_existing_bible()
    {
        $updated_data = [
            'name' => 'Updated King James Version',
            'scope' => 'OT'
        ];

        // Update the bible
        $result = $this->service->update( 'KJV', $updated_data );

        // Verify that the bible was updated
        $this->assertTrue( $result );
        $this->assertEquals( 'Updated King James Version', $this->test_bibles['KJV']['name'] );
        $this->assertEquals( 'OT', $this->test_bibles['KJV']['scope'] );
        $this->assertEquals( 'KJV', $this->test_bibles['KJV']['abbreviation'] ); // Unchanged

        // Test updating a non-existent bible
        $result = $this->service->update( 'non_existent', $updated_data );
        $this->assertFalse( $result );
    }

    /**
     * @test
     */
    public function it_can_delete_a_bible()
    {
        // Delete the bible
        $result = $this->service->delete( 'KJV' );

        // Verify that the bible was deleted
        $this->assertTrue( $result );
        $this->assertCount( 1, $this->test_bibles );
        $this->assertArrayNotHasKey( 'KJV', $this->test_bibles );

        // Test deleting a non-existent bible
        $result = $this->service->delete( 'non_existent' );
        $this->assertFalse( $result );
    }
}
