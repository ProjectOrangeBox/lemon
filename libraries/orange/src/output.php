<?php

declare(strict_types=1);

namespace dmyers\orange;

use dmyers\orange\exceptions\ViewNotFound;

class Output
{
	const HTML = 'text/html';
	const JSON = 'application/json';
	const JSONOPTIONS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

	protected $code = 200;
	protected $contentType = 'text/html'; /* default to html */
	protected $charSet = 'utf-8';
	protected $headers = [];
	protected $output = '';
	protected $config = null;
	protected $input = null;

	public function __construct(array $config, input &$input)
	{
		$this->config = $config;
		$this->input = $input;

		if ($config['cors']) {
			$this->handleCrossOriginResourceSharing();
		}

		$this->contentType = (isset($config['contentType'])) ? $config['contentType'] : $this->contentType;
		$this->charSet = (isset($config['charSet'])) ? $config['charSet'] : $this->charSet;
	}

	public function flushOutput(): self
	{
		$this->output = '';

		return $this;
	}

	public function setOutput(?string $html): self
	{
		$this->output = ($html === null) ? '' : $html;

		return $this;
	}

	public function appendOutput(?string $html): self
	{
		if (is_string($html)) {
			$this->output .= $html;
		}

		return $this;
	}

	public function getOutput(): string
	{
		return $this->output;
	}

	public function contentType(string $contentType): self
	{
		$this->contentType = $contentType;

		return $this;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function header(string $header, string $key = null): self
	{
		$key = ($key === null) ? $header : $key;

		$this->headers[$key] = $header;

		return $this;
	}

	public function getHeaders(): array
	{
		$this->updateContentHeader();

		return array_values($this->headers);
	}

	public function sendHeaders(): self
	{
		$this->updateContentHeader();

		foreach ($this->getHeaders() as $header) {
			header($header);
		}

		return $this;
	}

	public function charSet(string $charSet): self
	{
		$this->charSet = $charSet;

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

	public function sendResponseCode(): self
	{
		http_response_code($this->code);

		return $this;
	}

	public function send()
	{
		echo $this->sendResponseCode()->sendHeaders()->getOutput();
	}

	public function view($viewNameInternal, $viewDataInternal = []): string
	{
		/* what file are we looking for? */
		$viewFileInternal = rtrim($this->config['views'], '/') . '/' . $viewNameInternal . '.php';

		/* is it there? if not return nothing */
		if (!file_exists($viewFileInternal)) {
			/* file not found so bail */
			throw new ViewNotFound($viewNameInternal);
		}

		/* extract out view data and make it in scope */
		extract($viewDataInternal, EXTR_OVERWRITE);

		/* start output cache */
		ob_start();

		/* load in view (which now has access to the in scope view data */
		require $viewFileInternal;

		/* capture cache and return */
		return ob_get_clean();
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

	protected function updateContentHeader(): void
	{
		$this->header('Content-Type: ' . $this->contentType . '; charset=' . $this->charSet, 'Content-Type');
	}
} /* end class */
