<?php

namespace CodeZone\Bible\Controllers\Settings;

use CodeZone\Bible\Services\RequestInterface as Request;
use function CodeZone\Bible\view;

class SupportController {
	/**
	 * Show the general settings admin tab
	 */
	public function show( Request $request ) {
		$tab = "support";

		return view( "settings/support", compact( 'tab' ) );
	}
}
