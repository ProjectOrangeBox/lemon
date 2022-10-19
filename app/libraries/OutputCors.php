<?php

declare(strict_types=1);

namespace app\libraries;

use dmyers\orange\Log;

class OutputCors
{
	public function handleCrossOriginResourceSharing()
	{
		logMsg('Event: ' . __FUNCTION__, Log::DEBUG);

		$config = container()->config->output;

		if ($config['cors']) {
			/* Handle CORS */
			$input = container()->input;

			/* Allow from any origin */
			if ($input->server('http_origin')) {
				header('Access-Control-Allow-Origin: ' . $input->server('http_origin'));
				header('Access-Control-Allow-Credentials: true');
				/* cache for 1 day */
				header('Access-Control-Max-Age: 86400');
			}

			/* Access-Control headers are received during OPTIONS requests */
			if (strtoupper($input->server('request_method')) == 'OPTIONS') {
				if ($input->server('http_access_control_request_method')) {
					// may also be using PUT, PATCH, HEAD etc
					header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
				}

				if ($input->server('http_access_control_request_headers')) {
					header('Access-Control-Allow-Headers: ' . $input->server('http_access_control_request_headers'));
				}

				header('Content-Length: 0');
				header('Content-Type: text/plain');

				exit(0);
			}
		}
	}
} /* end class */
