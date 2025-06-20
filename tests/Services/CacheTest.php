<?php

namespace Tests\Services;

use CodeZone\Bible\Services\Cache;
use Tests\TestCase;

class CacheTest extends TestCase
{
    /**
     * @test
     */
    public function it_scopes_keys_with_prefix()
    {
        $cache = new Cache();
        $key = 'test_key';
        $scoped_key = $cache->scope_key( $key );

        $this->assertEquals( 'bible_plugin_test_key', $scoped_key );
    }

    /**
     * @test
     */
    public function it_can_set_and_get_values()
    {
        $cache = new Cache();
        $key = 'test_value';
        $value = 'test_data';

        // Set the value
        $result = $cache->set( $key, $value );
        $this->assertTrue( $result );

        // Get the value
        $retrieved = $cache->get( $key );
        $this->assertEquals( $value, $retrieved );
    }

    /**
     * @test
     */
    public function it_can_delete_values()
    {
        $cache = new Cache();
        $key = 'test_delete';
        $value = 'delete_me';

        // Set the value
        $cache->set( $key, $value );

        // Delete the value
        $result = $cache->delete( $key );
        $this->assertTrue( $result );

        // Verify it's gone
        $retrieved = $cache->get( $key );
        $this->assertFalse( $retrieved );
    }

    /**
     * @test
     */
    public function it_can_flush_all_values()
    {
        global $wpdb;

        // First, add some test transients
        set_transient( 'bible_plugin_test1', 'value1' );
        set_transient( 'bible_plugin_test2', 'value2' );
        set_transient( 'other_plugin_test', 'value3' ); // This one shouldn't be deleted

        // Verify transients were created
        $this->assertNotFalse( get_transient( 'bible_plugin_test1' ) );
        $this->assertNotFalse( get_transient( 'bible_plugin_test2' ) );
        $this->assertNotFalse( get_transient( 'other_plugin_test' ) );

        // Run the flush operation
        $cache = new Cache();
        $cache->flush();

        // Verify our plugin's transients were deleted
        $this->assertFalse( get_transient( 'bible_plugin_test1' ) );
        $this->assertFalse( get_transient( 'bible_plugin_test2' ) );

        // Verify other plugin's transient wasn't affected
        $this->assertNotFalse( get_transient( 'other_plugin_test' ) );

        // Clean up
        delete_transient( 'other_plugin_test' );
    }
}
