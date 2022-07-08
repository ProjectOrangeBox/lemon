<?php

declare(strict_types=1);

namespace dmyers\orange;

class Output
{
	protected $code = 200;
	protected $contentType = 'text/html'; /* default to html */
	protected $charSet = 'utf-8';
	protected $headers = [];
	protected $htmlKey = 'html';
	protected $output = [];
	protected $jsonOptions = JSON_PRETTY_PRINT;
	protected $config = null;
	protected $input = null;

	public function __construct(array $config, input &$input)
	{
		$this->config = $config;
		$this->input = $input;

		if ($config['cors']) {
			$this->handleCrossOriginResourceSharing();
		}

		$this->contentType($config['contentType']);
		$this->charSet($config['charSet']);

		$this->output[$this->htmlKey] = '';
	}

	public function flushOutput(): output
	{
		$this->output = [];

		return $this;
	}

	public function handleCrossOriginResourceSharing()
	{
		/* Handle CORS */

		/* Allow from any origin */
		if ($this->input->server('http_origin')) {
			header('Access-Control-Allow-Origin: ' . $this->input->server('http_origin'));
			header('Access-Control-Allow-Credentials: true');
			/* cache for 1 day */
			header('Access-Control-Max-Age: 86400');
		}

		/* Access-Control headers are received during OPTIONS requests */
		if (strtoupper($this->input->server('request_method')) == 'OPTIONS') {
			if ($this->input->server('http_access_control_request_method')) {
				// may also be using PUT, PATCH, HEAD etc
				header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
			}

			if ($this->input->server('http_access_control_request_headers')) {
				header('Access-Control-Allow-Headers: ' . $this->input->server('http_access_control_request_headers'));
			}

			header('Content-Length: 0');
			header('Content-Type: text/plain');

			exit(0);
		}
	}

	public function __set($name, $value)
	{
		$this->output[$name] = $value;
	}

	public function __get($name)
	{
		return (isset($this->output[$name])) ? $this->output[$name] : null;
	}

	public function setOutput(string $html): output
	{
		$this->output[$this->htmlKey] = $html;

		return $this;
	}

	public function appendOutput(?string $html): output
	{
		$this->output[$this->htmlKey] .= $html;

		return $this;
	}

	public function getOutput(): string
	{
		return (isset($this->output[$this->htmlKey])) ? $this->output[$this->htmlKey] : '';
	}

	public function contentType(string $contentType): output
	{
		$this->contentType = $contentType;

		$this->updateContentHeader();

		return $this;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function header(string $header, string $key = null): output
	{
		$this->headers[$key] = $header;

		return $this;
	}

	public function getHeaders(): array
	{
		return array_values($this->headers);
	}

	public function sendHeaders(): output
	{
		foreach ($this->getHeaders() as $header) {
			header($header);
		}

		return $this;
	}

	public function charSet(string $charSet): output
	{
		$this->charSet = $charSet;

		$this->updateContentHeader();

		return $this;
	}

	public function getCharSet(): string
	{
		return $this->charSet;
	}

	public function responseCode(int $code): output
	{
		$this->code = $code;

		return $this;
	}

	public function getResponseCode(): int
	{
		return $this->code;
	}

	public function send(bool $exit = false)
	{
		http_response_code($this->getResponseCode());

		$this->sendHeaders();

		echo $this->__toString();

		if ($exit) {
			exit();
		}
	}

	public function __toString()
	{
		return ($this->contentType == 'application/json') ? json_encode($this->output, $this->jsonOptions) : $this->getOutput();
	}

	protected function updateContentHeader(): void
	{
		$this->header('Content-Type: ' . $this->contentType . '; charset=' . $this->charSet, 'Content-Type');
	}
} /* end class */