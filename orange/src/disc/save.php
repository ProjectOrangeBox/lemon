<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use SplFileInfo;
use dmyers\orange\disc\exceptions\discException;

class Save extends SplFileInfo
{
	const JSONFLAGS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

	public function __construct(string $path, $input = null, ?int $chmod = null, $arg1 = null)
	{
		$path = Disc::resolve($path);

		parent::__construct($path);

		if ($input) {
			switch (\pathinfo($path, PATHINFO_EXTENSION)) {
				case 'ini':
					$bytes = $this->ini((array)$input, $chmod);
					break;
				case 'json':
					$arg1 = ($arg1) ?? false;
					$bytes = $this->json($input, $arg1, null, null, $chmod);
					break;
				case 'php':
					$bytes = $this->php($input, $chmod);
					break;
				default:
					$bytes = $this->content($input, (int)$arg1, $chmod);
			}
		}
	}

	/**
	 * Method varPhp
	 *
	 * @param $input $input [explicite description]
	 *
	 * @return string
	 */
	public function exportPhp($input): string
	{
		$string = '';

		if (\is_array($input) || \is_object($input)) {
			$string = '<?php return ' . \str_replace(['Closure::__set_state', 'stdClass::__set_state'], '(object)', \var_export($input, true)) . ';';
		} elseif (\is_scalar($input)) {
			$string = '<?php return "' . \str_replace('"', '\"', $input) . '";';
		} else {
			throw new discException('Unknown input type.');
		}

		return $string;
	}

	/**
	 * Method varJson
	 *
	 * @param $input $input [explicite description]
	 * @param bool $pretty [explicite description]
	 * @param ?int $flags [explicite description]
	 * @param ?int $depth [explicite description]
	 *
	 * @return string
	 */
	public function exportJson($input, bool $pretty = false, ?int $flags = null, ?int $depth = 512): string
	{
		$flags = ($flags) ?? self::JSONFLAGS;
		$depth = ($depth) ?? 512;

		if ($pretty) {
			$flags = $flags | JSON_PRETTY_PRINT;
		}

		return json_encode($input, $flags, $depth);
	}

	/**
	 * Method varIni
	 *
	 * @param array $array [explicite description]
	 * @param array $parent [explicite description]
	 *
	 * @return string
	 */
	public function exportIni(array $array, array $parent = []): string
	{
		$ini = '';

		foreach ($array as $key => $value) {
			if (\is_array($value)) {
				//subsection case
				//merge all the sections into one array...
				$subsection = \array_merge((array) $parent, (array) $key);
				//add section information to the output
				$ini .= '[' . \join('.', $subsection) . ']' . PHP_EOL;
				//recursively traverse deeper
				$ini .= $this->exportIni($value, $subsection);
			} else {
				//plain key->value case
				$ini .= "$key=$value" . PHP_EOL;
			}
		}

		return $ini;
	}

	/**
	 * Method putPhp
	 *
	 * @param string $path [explicite description]
	 * @param $data $data [explicite description]
	 * @param ?int $chmod [explicite description]
	 *
	 * @return int
	 */
	public function php($data, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		$bytes = Disc::changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->exportPhp($data)), $chmod);

		/* if it's cached we need to flush it out so the old one isn't loaded */
		Disc::removePhpFileFromOpcache($path);

		return $bytes;
	}

	/**
	 * Method putJson
	 *
	 * @param string $path [explicite description]
	 * @param $jsonObj $jsonObj [explicite description]
	 * @param bool $pretty [explicite description]
	 * @param ?int $flags [explicite description]
	 * @param ?int $depth [explicite description]
	 * @param ?int $chmod [explicite description]
	 *
	 * @return int
	 */
	public function json($jsonObj, bool $pretty = false, ?int $flags = null, ?int $depth = 512, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		return Disc::changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->exportJson($jsonObj, $pretty, $flags, $depth)), $chmod);
	}

	/**
	 * Method putIni
	 *
	 * @param string $path [explicite description]
	 * @param array $array [explicite description]
	 * @param ?int $chmod [explicite description]
	 *
	 * @return int
	 */
	public function ini(array $array, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		return Disc::changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->exportIni($array)), $chmod);
	}

	public function content($content, ?int $flags = 0, ?int $chmod = null): int
	{
		$path = $this->getPathname();
		$bytes = 0;

		/* if they aren't using any special flags just make it atomic that way locks aren't needed or partially written files aren't read */
		if ($flags > 0) {
			Disc::autoGenMissingDirectory($path);

			$bytes = \file_put_contents($path, $content, $flags);
		} else {
			/* if no flags provided do it the atomic way */
			$bytes = Disc::atomicSaveContent($path, $content);
		}

		return Disc::changeModeOnBytes($path, $bytes, $chmod);
	}
} /* end class */
