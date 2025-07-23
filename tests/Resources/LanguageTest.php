<?php

namespace Tests\Resources;

use CodeZone\Bible\Resources\Language;
use Tests\TestCase;
use function CodeZone\Bible\container;

/**
 * @group resources
 * @group languages
 */
class LanguageTest extends TestCase {
    /**
     * @test
     */
    public function it_can_retrieve_all_languages() {
        $languages = container()->get(Language::class);
        $result = $languages->all();

        // Verify we get an array of languages
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check the structure of a language
        $language = $result[0];
        $this->assertIsArray($language);
        $this->assertArrayHasKey('id', $language);
        $this->assertArrayHasKey('name', $language);
        $this->assertArrayHasKey('source', $language);

        // Verify source field is set
        $this->assertContains($language['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_can_find_a_language_by_id() {
        $languages = container()->get(Language::class);
        $result = $languages->find(6414); // English

        // Verify we get a language
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('source', $result);

        // Verify it's the correct language
        $this->assertEquals(6414, $result['id']);
        $this->assertContains($result['source'], ['bible-brains', 'usx']);
    }

    /**
     * @test
     */
    public function it_returns_null_for_nonexistent_language() {
        $languages = container()->get(Language::class);
        $result = $languages->find(999999); // Nonexistent language ID

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_search_for_languages() {
        $languages = container()->get(Language::class);
        $result = $languages->search('English');

        // Verify we get an array of languages
        $this->assertIsArray($result);

        // If results are found, check their structure
        if (!empty($result)) {
            $language = $result[0];
            $this->assertIsArray($language);
            $this->assertArrayHasKey('id', $language);
            $this->assertArrayHasKey('name', $language);
            $this->assertArrayHasKey('source', $language);

            // Verify source field is set
            $this->assertContains($language['source'], ['bible-brains', 'usx']);
        }
    }

    /**
     * @test
     */
    public function it_can_transform_languages_to_options() {
        $languages = container()->get(Language::class);
        $allLanguages = $languages->all();
        $options = $languages->as_options($allLanguages);

        // Verify we get an array of options
        $this->assertIsArray($options);

        // If options are found, check their structure
        if (!empty($options)) {
            $option = $options[0];
            $this->assertIsArray($option);
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('itemText', $option);
        }
    }

    /**
     * @test
     */
    public function it_can_map_a_language_to_option_format() {
        $languages = container()->get(Language::class);
        $language = [
            'id' => 6414,
            'name' => 'English',
            'source' => 'bible-brains'
        ];

        $option = $languages->map_option($language);

        // Verify the option structure
        $this->assertIsArray($option);
        $this->assertArrayHasKey('value', $option);
        $this->assertArrayHasKey('itemText', $option);

        // Verify the option values
        $this->assertEquals('6414', $option['value']);
        $this->assertEquals('English', $option['itemText']);
    }
}
