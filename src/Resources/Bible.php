<?php

namespace CodeZone\Bible\Resources;

use CodeZone\Bible\Exceptions\BiblePluginException;
use CodeZone\Bible\Sources\Aggregators\BibleAggregator;
use CodeZone\Bible\Sources\Aggregators\ContentAggregator;
use CodeZone\Bible\Traits\Optionable;

/**
 * Class Bible
 *
 * This class serves as a manager for interacting with Bible data from multiple sources
 * (USX bibles and Bible Brains bibles) and provides functionality for finding, searching,
 * and merging bible data. Transformers are used to process and prepare data appropriately
 * for each source.
 */
class Bible
{
    use Optionable;

    protected BibleAggregator $aggregator;
    protected ContentAggregator $contentAggregator;


    /**
     * Constructs a new instance of the class with the provided BibleAggregator.
     *
     * @param BibleAggregator $aggregator The Bible aggregator instance used for managing bibles.
     * @return void
     */
    public function __construct( BibleAggregator $aggregator, ContentAggregator $contentAggregator ) {
        $this->aggregator = $aggregator;
        $this->contentAggregator = $contentAggregator;
    }

    /**
     * Finds an item by its ID and optional parameters.
     *
     * @param mixed $id The unique identifier of the item to find.
     * @param array $params Optional. Additional parameters for customizing the search.
     * @return array|null Returns the found item as an array, or null if no item is found.
     */
    public function find( $id, $params = [] ): ?array
    {
        return $this->aggregator->find( $id, $params );
    }

    /**
     * Finds a Bible by code or falls back to the default for the language.
     *
     * @param string|null $code
     * @param int|null $language_id
     * @param array $query
     * @return array
     * @throws BiblePluginException
     */
    public function resolve( array $query = [] ): ?array {
        $code = $query['code'] ?? null;
        $language_id = $query['language_id'] ?? null;

        if ( empty( $code ) && empty( $language_id ) ) {
            throw new BiblePluginException( esc_html( __( 'Either a bible ID or a language ID must be provided.', 'bible-plugin' ) ) );
        }

        if ( empty( $code ) ) {
            return $this->aggregator->language_default( $language_id );
        }

        $result = $this->find( $code, $query );

        if ( empty( $result ) ) {
            $result = $this->aggregator->language_default( $language_id );
        }

        return $result;
    }

    /**
     * Finds multiple items based on the provided IDs and parameters.
     *
     * @param array $ids An array of IDs to retrieve items for.
     * @param mixed $params Optional. Additional parameters for customizing the retrieval process.
     * @return array|null An array of found items, or null if no items are found.
     */
    public function find_many( array $ids, $params = [] ): ?array
    {
        $records = [];

        foreach ( $ids as $id ) {
            $item = $this->find( $id, $params );
            if ( $item ) {
                $records[] = $item;
            }
        }

        return $records;
    }

    /**
     * Searches for bibles using the provided query and parameters.
     *
     * @param mixed $query The search query used to find matching bibles.
     * @param array $params Optional. Additional parameters for customizing the search.
     * @return array An array of matching bibles.
     */
    public function search( $query, $params = [] ): array
    {
       return $this->aggregator->search( $query, $params );
    }

    /**
     *
     */
    public function all( $params = [] ): array
    {
        $language_id = $params['language_id'] ?? null;
        unset( $params['language_id'] );
        if ( !empty( $language_id ) ) {
            return $this->aggregator->for_language( $language_id, $params);
        }
        return $this->aggregator->all( $params );
    }

    /**
     * Retrieves the content of specified scripture verses within a bible.
     *
     * @param mixed $bible The bible identifier or object to retrieve content from.
     * @param mixed $book The book identifier or object within the bible.
     * @param int $chapter The chapter number to retrieve content from.
     * @param int $verse_start The starting verse number.
     * @param int $verse_end The ending verse number.
     * @return array An array containing the requested scripture content.
     */
    public function content( $bible, $book, $chapter, $verse_start, $verse_end ): array {
        return $this->contentAggregator->content( $bible, $book, $chapter, $verse_start, $verse_end );
    }
}
