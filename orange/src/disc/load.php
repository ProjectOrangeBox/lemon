<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use SplFileInfo;
use dmyers\orange\disc\exceptions\DiscException;

class Load extends SplFileInfo
{
	protected $loaded = null;

	public function __construct(string $path, ?bool $processFile = true, $arg1 = null, $arg2 = null)
	{
		$path = Disc::resolve($path);

		parent::__construct($path);

		if ($processFile) {
			switch (\pathinfo($path, PATHINFO_EXTENSION)) {
				case 'ini':
					$arg1 = ($arg1 === null) ? true : $arg1;
					$arg2 = ($arg2 === null) ? INI_SCANNER_TYPED : $arg2;

					$this->loaded = $this->ini($arg1, $arg2);
					break;
				case 'json':
					$this->loaded = $this->json();
					break;
				case 'php':
					$this->loaded = $this->php();
					break;
				default:
					$this->loaded = $this->content();
			}
		}
	}

	public function loaded() /* mixed */
	{
		return $this->loaded;
	}

	public function php()
	{
		$requiredPath = $this->getPathname();

		return include $requiredPath;
	}

	public function json()
	{
		$requiredPath = $this->getPathname();

		$json = json_decode($this->content(), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new discException('JSON file "' . $requiredPath . '" is not valid JSON.');
		}

		return $json;
	}

	public function ini(bool $processSections = false, int $scannerMode = INI_SCANNER_NORMAL) /* mixed */
	{
		$requiredPath = $this->getPathname();

		$ini = false;

		$ini = \parse_ini_file($requiredPath, $processSections, $scannerMode);

		if (!$ini) {
			throw new discException('INI file "' . $requiredPath . '" is not valid.');
		}

		return $ini;
	}

	public function content(): string
	{
		$requiredPath = $this->getPathname();

		return \file_get_contents($requiredPath);
	}
} /* end class */
