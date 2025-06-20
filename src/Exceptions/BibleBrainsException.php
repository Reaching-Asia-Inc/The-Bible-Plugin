<?php

namespace CodeZone\Bible\Exceptions;

use Exception;

/**
 * Class BibleBrainsException
 *
 * Custom exception class for BibleBrains application
 */
class BibleBrainsException extends \Exception {
    /**
     * Constructs a new exception instance.
     *
     * @param string $message The exception message. Default is an empty string.
     * @param int $code The exception code. Default is 0.
     * @param Exception|null $previous The previous exception used for exception chaining. Default is null.
     *
     * @return void
     */
	public function __construct( $message = "", $code = 0, ?Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Returns the object as a JSON string representation.
	 *
	 * @return string The JSON string representation of the object.
	 */
	public function __toString() {
		return json_encode( $this->toArray() );
	}

	/**
	 * Returns the object as an array representation.
	 *
	 * @return array The array representation of the object.
	 */
	public function __toArray() {
		return $this->toArray();
	}

	/**
	 * Returns the object as an array representation.
	 *
	 * @return array The array representation of the object.
	 */
	public function toArray() {
		return [
			'error' => $this->message,
			'code'  => $this->code
		];
	}

	/**
	 * Returns the object as a JSON string representation.
	 *
	 * @return string The JSON string representation of the object.
	 */
	public function __toJSON() {
		return json_encode( $this->__toArray() );
	}
}
