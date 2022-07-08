<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ViewNotFound;

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

	public function flushOutput(): self
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

	public function setOutput(string $html): self
	{
		$this->output[$this->htmlKey] = $html;

		return $this;
	}

	public function appendOutput(?string $html): self
	{
		$this->output[$this->htmlKey] .= $html;

		return $this;
	}

	public function getOutput(): string
	{
		return (isset($this->output[$this->htmlKey])) ? $this->output[$this->htmlKey] : '';
	}

	public function contentType(string $contentType): self
	{
		$this->contentType = $contentType;

		$this->updateContentHeader();

		return $this;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function header(string $header, string $key = null): self
	{
		$this->headers[$key] = $header;

		return $this;
	}

	public function getHeaders(): array
	{
		return array_values($this->headers);
	}

	public function sendHeaders(): self
	{
		foreach ($this->getHeaders() as $header) {
			header($header);
		}

		return $this;
	}

	public function charSet(string $charSet): self
	{
		$this->charSet = $charSet;

		$this->updateContentHeader();

		return $this;
	}

	public function getCharSet(): string
	{
		return $this->charSet;
	}

	public function responseCode(int $code): self
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

	public function view($_mvc_view_name, $_mvc_view_data = [])
	{
		/* what file are we looking for? */
		$_mvc_view_file = rtrim(container()->config['path']['views'], '/') . '/' . $_mvc_view_name . '.php';

		/* is it there? if not return nothing */
		if (!file_exists($_mvc_view_file)) {
			/* file not found so bail */
			throw new ViewNotFound($_mvc_view_name);
		}

		/* extract out view data and make it in scope */
		extract($_mvc_view_data, EXTR_OVERWRITE);

		/* start output cache */
		ob_start();

		/* load in view (which now has access to the in scope view data */
		require $_mvc_view_file;

		/* capture cache and return */
		return ob_get_clean();
	}

	protected function updateContentHeader(): void
	{
		$this->header('Content-Type: ' . $this->contentType . '; charset=' . $this->charSet, 'Content-Type');
	}
} /* end class */