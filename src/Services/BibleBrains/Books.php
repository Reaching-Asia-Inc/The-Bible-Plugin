<?php

namespace CodeZone\Bible\Services\BibleBrains;

use CodeZone\Bible\Exceptions\BibleBrainsException;
use CodeZone\Bible\Services\BibleBrains\Api\Bibles;

/**
 * Books service class for interacting with the Bible Brains books.
 */
class Books {

	/**
	 * The Bible Brains bible service
	 *
	 * @var Bibles
	 */
	private $bibles;

	private $old_testament = [
		'GEN' => 'Genesis',
		'EXO' => 'Exodus',
		'LEV' => 'Leviticus',
		'NUM' => 'Numbers',
		'DEU' => 'Deuteronomy',
		'JOS' => 'Joshua',
		'JDG' => 'Judges',
		'RUT' => 'Ruth',
		'1SA' => '1 Samuel',
		'2SA' => '2 Samuel',
		'1KI' => '1 Kings',
		'2KI' => '2 Kings',
		'1CH' => '1 Chronicles',
		'2CH' => '2 Chronicles',
		'EZR' => 'Ezra',
		'NEH' => 'Nehemiah',
		'EST' => 'Esther',
		'JOB' => 'Job',
		'PSA' => 'Psalms',
		'PRO' => 'Proverbs',
		'ECC' => 'Ecclesiastes',
		'SNG' => 'Song of Solomon',
		'ISA' => 'Isaiah',
		'JER' => 'Jeremiah',
		'LAM' => 'Lamentations',
		'EZK' => 'Ezekiel',
		'DAN' => 'Daniel',
		'HOS' => 'Hosea',
		'JOL' => 'Joel',
		'AMO' => 'Amos',
		'OBA' => 'Obadiah',
		'JON' => 'Jonah',
		'MIC' => 'Micah',
		'NAM' => 'Nahum',
		'HAB' => 'Habakkuk',
		'ZEP' => 'Zephaniah',
		'HAG' => 'Haggai',
		'ZEC' => 'Zechariah',
		'MAL' => 'Malachi'
	];

	private $new_testament = [
		'MAT' => 'Matthew',
		'MRK' => 'Mark',
		'LUK' => 'Luke',
		'JHN' => 'John',
		'ACT' => 'Acts',
		'ROM' => 'Romans',
		'1CO' => '1 Corinthians',
		'2CO' => '2 Corinthians',
		'GAL' => 'Galatians',
		'EPH' => 'Ephesians',
		'PHP' => 'Philippians',
		'COL' => 'Colossians',
		'1TH' => '1 Thessalonians',
		'2TH' => '2 Thessalonians',
		'1TI' => '1 Timothy',
		'2TI' => '2 Timothy',
		'TIT' => 'Titus',
		'PHI' => 'Philemon',
		'HEB' => 'Hebrews',
		'JAS' => 'James',
		'1PE' => '1 Peter',
		'2PE' => '2 Peter',
		'1JN' => '1 John',
		'2JN' => '2 John',
		'3JN' => '3 John',
		'JUD' => 'Jude',
		'REV' => 'Revelation'
	];

	public function guess_testament( $abbr ) {
		return array_key_exists( $abbr, $this->old_testament ) ? 'OT' : 'NT';
	}

	public function __construct( Bibles $bibles ) {
		$this->bibles = $bibles;
	}

	/**
	 * Retrieves all books from the specified bible version.
	 *
	 * @param string|array $bible The version of the bible to retrieve the books from.
	 *                            Can be either the version ID or an associative array containing the books.
	 *                            Defaults to 'ENGESV'.
	 *
	 * @return array The array containing all the books from the specified bible version.
	 * @throws BibleBrainsException
	 */
	public function all( $bible = 'ENGESV' ): array {
		if ( is_array( $bible ) ) {
			return $bible['books'];
		}

		return $this->bibles->books( $bible );
	}

	/**
	 * Find a book within the given Bible.
	 *
	 * @param string $book The book to find.
	 * @param string|array $bible The Bible to search in. Defaults to 'ENGESV'.
	 *
	 * @return ?array Returns an array representing the found book, or null if not found.
	 * @throws BibleBrainsException
	 */
	public function find( string $book, string $bible = 'ENGESV' ): ?array {
		$books = $this->all( $bible );

		return $this->pluck( $book, $books );
	}

    /**
     * Pluck a specific book from an array of books.
     *
     * @param string $book The book to pluck.
     * @param array $books The array of books to search in.
     *
     * @return array Returns an array representing the plucked book.
     */
    public function pluck(string $book, array $books): array
    {
        foreach ($books as $b) {
            if ($b['book_id'] === strtoupper($book)
                || $b['name'] === ucfirst($book)
                || $b['name_short'] === ucfirst($book)) {
                return $b;
            }
        }
        return [];
    }

	/**
	 * Normalize a book within the given Bible.
	 *
	 * @param string $book The book to normalize.
	 * @param string|array $bible The Bible to search in. Defaults to 'ENGESV'.
	 *
	 * @return string|array Returns the normalized book if found, otherwise returns the original book.
	 * @throws BibleBrainsException
	 */
	public function normalize( string $book, $bible = 'ENGESV' ) {
		$data = $this->find( $book, $bible );

		if ( ! $data ) {
			return $book;
		}

        return $data['book_id'] ?? $book;
    }
}
