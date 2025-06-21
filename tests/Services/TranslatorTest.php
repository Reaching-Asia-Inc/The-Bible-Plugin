<?php

namespace Tests\Services;

use CodeZone\Bible\Services\Translations;
use Tests\TestCase;
use function CodeZone\Bible\container;
use function CodeZone\Bible\get_plugin_option;
use function CodeZone\Bible\set_plugin_option;

/**
 * Class BibleBrainsSettingsTest
 *
 * This class is responsible for testing the BibleBrains settings page.
 *
 * @test
 * @group services
 * @group translations
 */
class TranslatorTest extends TestCase {
	/**
	 * @test
	 */
	public function it_custom_translates() {
		set_plugin_option( 'translations', [
			'Hello World' => 'Hola Mundo',
		] );

		$this->assertEquals( 'Hola Mundo', _x( 'Hello World', 'reader', 'bible-plugin' ) );
	}

	/**
	 * @test
	 */
	public function it_fetches_translatable_strings() {
		$translations = container()->get( Translations::class );
		$strings      = $translations->strings();
		$this->assertContains( 'Bible', $strings );
	}

	/**
	 * @test
	 */
    public function it_has_string_options()
    {
        $translations = container()->get( Translations::class );
        $strings = $translations->options();

        $this->assertNotNull(array_filter($strings, function ( $option ) {
            return $option['itemText'] === 'Bible' && $option['value'] === 'Bible';
        })[0] ?? null);
    }
}
