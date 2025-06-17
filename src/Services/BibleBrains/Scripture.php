<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;
use CodeZone\Bible\CodeZone\WPSupport\Options\OptionsInterface as Options;;

/**
 * The Scripture class is responsible for handling scripture references and retrieving scripture content from a Bible object.
 *
 * @package YourPackage
 */
class Scripture {
	/**
	 * The Bibles service.
	 *
	 * @var Bibles
	 */
	private $bibles;

	/**
	 * The Books service.
	 *
	 * @var Books
	 */
	private $books;

	/**
	 * The FileSets service.
	 *
	 * @var FileSets
	 */
	private $file_sets;

	/**
	 * The Reference service.
	 *
	 * @var Reference
	 */
	private $reference;

	/**
	 * The MediaTypes service.
	 *
	 * @var MediaTypes
	 */
	private $media_types;

	/**
	 * The Language service.
	 *
	 * @var Language
	 */
	private $language;

	/**
	 * The Options service.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructs a new instance of the class.
	 *
	 * @param Bibles $bibles The Bibles service.
	 * @param Books $books The Books service.
	 * @param FileSets $file_sets The FileSets service.
	 * @param Reference $reference The Reference service.
	 * @param MediaTypes $media_types The MediaTypes service.
	 * @param Language $language The Language service.
	 */
	public function __construct(
		Bibles $bibles,
		Books $books,
		FileSets $file_sets,
		Reference $reference,
		MediaTypes $media_types,
		Language $language,
		Options $options
	) {
		$this->bibles      = $bibles;
		$this->books       = $books;
		$this->file_sets   = $file_sets;
		$this->reference   = $reference;
		$this->media_types = $media_types;
		$this->language    = $language;
		$this->options     = $options;
	}

	/**
	 * Search for verses in the Bible using various parameters.
	 *
	 * @param array $parameters An associative array of search parameters.
	 *                          - language: The language to search in. Defaults to null.
	 *                          - fileset: The fileset to search in. Defaults to null.
	 *                          - bible: The specific Bible to search in. Defaults to null.
	 *                          - book: The specific book of the Bible to search in. Defaults to null.
	 *                          - chapter: The specific chapter of the Bible to search in. Defaults to null.
	 *                          - media_type: The media type to search for. Defaults to 'text'.
	 *                          - verse_start: The starting verse to search from. Defaults to null.
	 *                          - verse_end: The ending verse to search to. Defaults to null.
	 *
	 * @return array The search results as*@throws BibleBrainsException If an invalid media type is specified.
	 * @throws BibleBrainsException
	 */
	public function search( array $parameters = [] ): array {
		$parameters = $this->normalize_query( $parameters );

		return $this->query( $parameters );
	}

    /**
     * Normalizes the query parameters by merging defaults with provided input.
     *
     * @param array $parameters An associative array of query parameters to normalize.
     *                          - language: The language to apply. Defaults to null.
     *                          - bible: The specific Bible to use. Defaults to null.
     *                          - book: The specific book of the Bible. Defaults to null.
     *                          - chapter: The chapter within the book. Defaults to null.
     *                          - verse_start: The starting verse number. Defaults to null.
     *                          - verse_end: The ending verse number. Defaults to null.
     *
     * @return array The normalized array of parameters after merging with defaults
     *               and parsing reference details.
     */
	private function normalize_query( $parameters ): array {
		$parameters = array_merge( [
			'language'    => null,
			'bible'       => null,
			'book'        => null,
			'chapter'     => null,
			'verse_start' => null,
			'verse_end'   => null,
		], $parameters );
;

		return array_merge( $parameters, $this->reference->parse( $parameters ) );
	}

    /**
     * Determine the appropriate fileset to search in based on the given parameters.
     *
     * @param array $parameters Search parameters
     * @return array The fileset as an array.
     * @throws BibleBrainsException If an invalid media type is specified or if there are any other errors.
     */
    private function query(array $parameters): array
    {
        $parameters = $this->normalize_query($parameters);

        // Get language and bible data
        $language = $this->resolve_language($parameters);
        $bible = $this->resolve_bible($parameters, $language);
        $book = $this->resolve_book($parameters, $bible);

        if ($book) {
            // Update parameters with resolved book ID
            $parameters['book'] = $book['book_id'];
        }


        // Process media types
        $media = $this->process_media_types($parameters, $language, $bible, $book);

        return array_merge(
            $parameters,
            [
                'media'    => $media,
                'language' => $language,
                'bible'    => $bible,
                'book'     => $book
            ]
        );
    }

    /**
     * Resolve language from parameters
     *
     * @param array $parameters
     * @return array
     */
    private function resolve_language(array $parameters): array
    {
        return $this->language->find_or_resolve($parameters['language'] ?? null);
    }

    /**
     * Resolve Bible from parameters and language
     *
     * @param array $parameters
     * @param array $language
     * @return array
     */
    private function resolve_bible(array $parameters, array $language): array
    {
        $bible = ($parameters['bible'] ?? null)
            ? $this->bibles->find($parameters['bible'])["data"]
            : null;

        if (!$bible) {
            $bible = $this->bibles->find_or_default($language['bibles'], $language['value'])["data"];
        }

        return $bible;
    }

    /**
     * Resolve book from parameters and bible
     *
     * @param array $parameters
     * @param array $bible
     * @return array
     * @throws BibleBrainsException
     */
    private function resolve_book(array $parameters, array $bible): array
    {
        $book = $this->books->pluck($parameters['book'], $bible['books']);
        if (!$book) {
            throw new BibleBrainsException(
                esc_attr("Bible, {$bible['name']}, does not contain {$parameters['book']}.")
            );
        }
        return $book;
    }

    /**
     * Get media types from language or options
     *
     * @param array $language
     * @return array
     */
    private function get_media_types(array $language): array
    {
        $media_types = $language['media_types'] ?? $this->options->get('media_types');
        if (is_string($media_types)) {
            return explode(',', $media_types);
        }
        return $media_types;
    }

    /**
     * Process media types and get content
     *
     * @param array $parameters
     * @param array $language
     * @param array $bible
     * @param array $book
     * @return array
     */
    private function process_media_types(array $parameters, array $language, array $bible, array $book): array
    {
        $media = [];
        $media_types = $this->get_media_types($language);

        foreach ($media_types as $media_type_key) {
            $media_content = $this->process_media_type(
                $media_type_key,
                $parameters,
                $bible,
                $book
            );
            if ($media_content) {
                $media[$media_type_key] = $media_content;
            }
        }

        return $media;
    }

    /**
     * Process individual media type
     *
     * @param string $media_type_key
     * @param array $parameters
     * @param array $bible
     * @param array $book
     * @return array|null
     */
    private function process_media_type(
        string $media_type_key,
        array $parameters,
        array $bible,
        array $book
    ): ?array {
        try {
            $media_type = $this->media_types->find($media_type_key);
            $fileset = $this->file_sets->pluck($bible, $book, $media_type['fileset_types']);

            if (!$fileset) {
                return null;
            }

            return array_merge(
                $media_type,
                [
                    'content' => $this->fetch_content(
                        array_merge(
                            $parameters,
                            ['fileset' => $fileset['id']]
                        )
                    ),
                    'fileset' => $fileset,
                ]
            );
        } catch (\Exception $e) {
            return null; // Skip invalid media types
        }
    }


    /**
	 * Search for verses in the Bible using the given reference and additional parameters.
	 *
	 * @param string $reference The reference to search for in the Bible.
	 * @param array $parameters An associative array of additional search parameters.
	 *                          - language: The language to search in. Defaults to null.
	 *                          - fileset: The fileset to search in. Defaults to null.
	 *                          - bible: The specific Bible to search in. Defaults to null.
	 *                          - book: The specific book of the Bible to search in. Defaults to null.
	 *                          - chapter: The specific chapter of the Bible to search in. Defaults to null.
	 *                          - media_types: The media types to search for. Defaults to 'text'.
	 *                          - verse_start: The starting verbiblese to search from. Defaults to null.
	 *                          - verse_end: The ending verse to search to. Defaults to null.
	 *
	 * @return array The search results as an associative array.
	 * @throws BibleBrainsException If an invalid media type is specified during the search.
	 */
	public function by_reference( string $reference = "", array $parameters = [] ): array {
		$reference  = $this->reference->parse( $reference );
		$parameters = $this->normalize_query( array_merge( $reference, $parameters ) );

		return $this->query( $parameters );
	}

	/**
	 * Fetches the content of Bible verses based on the specified parameters.
	 *
	 * @param array $parameters An associative array of parameters specifying the content to fetch.
	 *                          - fileset: The fileset from which to fetch the content.
	 *                          - book: The book of the Bible from which to fetch the content.
	 *                          - chapter: The chapter of the Bible from which to fetch the content.
	 *                          - verse_start: The starting verse of the content to fetch.
	 *                          - verse_end: The ending verse of the content to fetch.
	 *
	 * @return array An array containing the fetched content.
	 * @throws BibleBrainsException If an error occurs while fetching the content.
	 */
	private function fetch_content( array $parameters = [] ): array {

		try {
			return $this->bibles->content(
				$parameters['fileset'],
				$parameters['book'],
				$parameters['chapter'],
				$parameters['verse_start'],
				$parameters["verse_end"]
			);
		} catch ( \Exception $e ) {
			return [];
		}
	}
}
