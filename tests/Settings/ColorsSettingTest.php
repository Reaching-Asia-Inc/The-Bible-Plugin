<?php

namespace Tests\Settings;

use CodeZone\Bible\Settings\ColorsSetting;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group settings
 */
class ColorsSettingTest extends TestCase
{
    /**
     * Test that the ColorsSetting formats colors correctly.
     * @test
     */
    public function it_formats_colors_correctly() {
        $setting = container()->get( ColorsSetting::class );

        // Test with empty input
        $result = $setting->format( null );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'accent', $result );
        $this->assertArrayHasKey( 'accent_steps', $result );

        // Test with legacy format (indexed array)
        $legacy_colors = [ '#3490dc', 5 ];
        $result = $setting->format( $legacy_colors );
        $this->assertEquals( '#3490dc', $result['accent'] );
        $this->assertEquals( 5, $result['accent_steps'] );

        // Test with complete input
        $complete_colors = [
            'accent' => '#ff0000',
            'accent_steps' => 3
        ];
        $result = $setting->format( $complete_colors );
        $this->assertEquals( '#ff0000', $result['accent'] );
        $this->assertEquals( 3, $result['accent_steps'] );
    }
}
