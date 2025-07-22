<?php

namespace CodeZone\Bible\Services;

/**
 * Class representing a collection of books from the Old Testament and New Testament.
 */
class Books
{
    /**
     * Array of Old Testament books with their abbreviations as keys
     *
     * @var array
     */
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

    /**
     * Array of New Testament books with their abbreviations as keys
     *
     * @var array
     */
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

    /**
     * Retrieves a merged array of both the Old Testament and the New Testament.
     *
     * @return array The combined array of the Old and New Testaments.
     */
    public function all(): array {
        return array_merge( $this->old_testament, $this->new_testament );
    }

    /**
     * Guess if a book abbreviation belongs to Old or New Testament
     *
     * @param string $code The book abbreviation to check
     * @return string Returns 'OT' for Old Testament or 'NT' for New Testament
     */
    public function guess_testament( $code ): string
    {
        if($this->is_nt($code)) {
            return 'NT';
        }

        return "OT";
    }

    /**
     * Determines if a given code exists in the Old Testament.
     *
     * @param string $code The code to check in the Old Testament.
     * @return bool True if the code exists in the Old Testament, false otherwise.
     */
    public function is_ot( $code ): bool {
        return array_key_exists( $code, $this->old_testament );
    }

    /**
     * Checks if the given code exists in the New Testament array.
     *
     * @param string $code The code to look up in the New Testament array.
     * @return bool True if the code exists in the New Testament, false otherwise.
     */
    public function is_nt( $code ): bool {
        return array_key_exists( $code, $this->new_testament );
    }

    /**
     * Converts the given name to its corresponding code by flipping the key-value pairs
     * of a combined array and retrieving the value associated with the name.
     *
     * @param string $name The name to be converted to a code.
     * @return mixed The code associated with the provided name, or null if the name does not exist.
     */
    public function to_code( $name ): ?string {
        $all = $this->all();
        //Check if it's already a code
        if (isset($all[strtoupper($name)])) {
            return $name;
        }
        $all = array_flip( $all );
        return $all[ucwords($name)] ?? $name;
    }

    /**
     * Retrieves the name corresponding to the given code from the combined array.
     *
     * @param string|int $code The code used to look up the name in the merged array.
     * @return mixed The name associated with the provided code.
     */
    public function to_name( $code ): ?string {
        $all = $this->all();
        if (isset($all[strtoupper($code)])) {
            return $all[strtoupper($code)];
        }
        //Check if it's already a name
        $all = array_flip( $all );
        if (isset($all[ucwords($code)])) {
            return ucwords($code);
        }
        return $code;
    }
}
