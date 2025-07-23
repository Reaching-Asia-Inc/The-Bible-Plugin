<?php

namespace CodeZone\Bible\Resources;

use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;
use CodeZone\Bible\Services\Translations;
use CodeZone\Bible\Sources\Aggregators\LanguageAggregator;
use CodeZone\Bible\Traits\Optionable;

/**
 * Class Language
 *
 * Represents a language and provides methods for resolving the language code,
 * finding supported languages, and resolving the default language.
 *
 * @package YourPackage
 */
class Language {
    protected LanguageAggregator $aggregator;

    use Optionable;

    /**
     * Constructs a new instance of the class with the provided dependencies.
     *
     * @param Options $options The configuration options for the instance.
     * @param LanguageAggregator $aggregator The language aggregator to manage language-related data.
     * @param Translations $translations The translations object to handle translation operations.
     * @return void
     */
	public function __construct( LanguageAggregator $aggregator ) {
		$this->aggregator   = $aggregator;
	}

    /**
     * Retrieves all records based on the provided parameters.
     *
     * @param array $params Optional parameters to filter or modify the retrieved records.
     * @return mixed A collection of all available records matching the given parameters.
     */
    public function all($params = []) {
        return $this->aggregator->all($params);
    }

    /**
     * Finds and retrieves a record corresponding to the given identifier.
     *
     * @param mixed $id The identifier of the record to be retrieved.
     * @return mixed The record found or null if no record matches the identifier.
     */
    public function find( $id ) {
        return $this->aggregator->find( $id );
    }


    /**
     * Executes a search operation using the specified query and parameters.
     *
     * @param mixed $query The search query to be executed.
     * @param array $params Additional parameters to customize the search behavior.
     * @return mixed The result of the search operation.
     */
    public function search($query, $params = []) {
        return $this->aggregator->search($query, $params);
    }
}
