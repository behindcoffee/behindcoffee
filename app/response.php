<?php

use Helper\View;

abstract class Response {

    protected
		$headers = array('Content-Type' => 'application/json', 'Accept'=> '*/*');

    protected function respond($object) {
		if (!headers_sent()) {
			foreach($this->headers as $key => $val) {
				header("$key: $val");
			}
		}

		if (function_exists('header_remove')) {
			header_remove('X-Powered-By'); // PHP 5.3+
		} else {
			@ini_set('expose_php', 'off');
		}

		echo json_encode($object);
	}

    protected function render($file) {

		// Generate CSRF token
		\Helper\CSRF::generateToken();

		echo View::instance()->render($file, "text/html", null, 0);
	}

}
