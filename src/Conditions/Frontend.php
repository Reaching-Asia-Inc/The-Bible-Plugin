<?php

namespace CodeZone\Bible\Conditions;

use CodeZone\Bible\CodeZone\Router\Conditions\Condition;

/**
 * Class Frontend
 *
 * @implements Condition
 */
class Frontend implements Condition {

	/**
	 * Test if the path is a frontend path.
	 *
	 * @return bool Returns true if the path is a frontend path.
	 */
	public function test(): bool {
		return ! is_admin();
	}
}
