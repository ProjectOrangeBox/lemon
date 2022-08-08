<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use SplFileInfo;
use SplFileObject;
use dmyers\orange\disc\exceptions\FileException;

class File extends SplFileInfo
{
	const JSONFLAGS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

	protected $fileObject = null;

	/**
	 * Method __call
	 *
	 * @param $name $name [explicite description]
	 * @param $arguments $arguments [explicite description]
	 *
	 * @return void
	 */
	public function __call($name, $arguments)
	{
		$this->requireOpenFile(); /* throws error on fail */

		if (method_exists($this->fileObject, $name)) {
			return $this->fileObject->$name(...$arguments);
		}

		trigger_error(sprintf('Call to undefined function: %s::%s().', get_class($this), $name), E_USER_ERROR);
	}

	/**
	 * Method open
	 *
	 * @param string $mode [explicite description]
	 *
	 * @return self
	 */
	public function open(string $mode = 'r'): self
	{
		$path = Disc::resolve($this->getPathname());

		if (is_dir($path)) {
			throw new FileException(Disc::resolve($this->getPathname(), true) . ' is a Directory');
		}

		if (in_array($mode, ['r', 'r+'])) {
			Disc::fileRequired($path);
		} else {
			Disc::autoGenMissingDirectory($path);
		}

		/* close properly */
		if ($this->fileObject) {
			$this->fileObject = null;
		}

		$this->fileObject = new SplFileObject($path, $mode);

		return $this;
	}

	/**
	 * Method create
	 *
	 * @param string $mode [explicite description]
	 *
	 * @return self
	 */
	public function create(string $mode = 'w'): self
	{
		return $this->open($mode);
	}

	/**
	 * Method append
	 *
	 * @param string $mode [explicite description]
	 *
	 * @return self
	 */
	public function append(string $mode = 'a'): self
	{
		return $this->open($mode);
	}

	/**
	 * Method close
	 *
	 * @return self
	 */
	public function close(): self
	{
		$this->requireOpenFile(); /* throws error on fail */

		$this->fileObject = null;

		return $this;
	}

	/**
	 * Method write
	 * 
	 * Write to file
	 *
	 * @param string $string [explicite description]
	 * @param ?int $length [explicite description]
	 *
	 * @return void
	 */
	public function write(string $string, ?int $length = null) /* int|false */
	{
		$this->requireOpenFile(); /* throws error on fail */

		return ($length) ? $this->fileObject->fwrite($string, $length) : $this->fileObject->fwrite($string);
	}

	/**
	 * Method writeLine
	 * 
	 * Write to file with line feed
	 *
	 * @param string $string [explicite description]
	 * @param string $lineEnding [explicite description]
	 *
	 * @return void
	 */
	public function writeLine(string $string, string $lineEnding = null)
	{
		$lineEnding = ($lineEnding) ?? PHP_EOL;

		return $this->write($string . $lineEnding);
	}

	/**
	 * Method character
	 * 
	 * Read single character from file
	 *
	 * @return void
	 */
	public function character() /* string|false */
	{
		return $this->characters(1);
	}

	/**
	 * Method characters
	 * 
	 * Read 1 or more characters from file
	 *
	 * @param int $length [explicite description]
	 *
	 * @return void
	 */
	public function characters(int $length) /* string|false */
	{
		return $this->fileObject->fread($length);
	}

	/**
	 * Method line
	 * 
	 * Read line from file
	 * auto detecting line ending
	 *
	 * @return string
	 */
	public function line(): string
	{
		$this->requireOpenFile(); /* throws error on fail */

		return $this->fileObject->fgets();
	}

	/**
	 * Method readCsvRow
	 *
	 * @param string $separator [explicite description]
	 * @param " $enclosure [explicite description]
	 * @param string $escape [explicite description]
	 *
	 * @return array
	 */
	public function readCsvRow(string $separator = ",", string $enclosure = '"', string $escape = "\\"): array
	{
		$this->requireOpenFile(); /* throws error on fail */

		return $this->fileObject->fgetcsv($separator, $enclosure, $escape);
	}

	/**
	 * Method writeCsvRow
	 *
	 * @param array $fields [explicite description]
	 * @param string $separator [explicite description]
	 * @param " $enclosure [explicite description]
	 * @param string $escape [explicite description]
	 * @param string $eol [explicite description]
	 *
	 * @return void
	 */
	public function writeCsvRow(array $fields, string $separator = ",", string $enclosure = "\"", string $escape = "\\", string $eol = "\n")
	{
		$this->requireOpenFile(); /* throws error on fail */

		return $this->fileObject->fputcsv($fields, $separator, $enclosure, $escape, $eol);
	}

	/**
	 * Method lock
	 * 
	 * Lock file
	 *
	 * @param int $operation [explicite description]
	 * @param int $wouldBlock [explicite description]
	 *
	 * @return bool
	 */
	public function lock(int $operation, int &$wouldBlock = null): bool
	{
		$this->requireOpenFile(); /* throws error on fail */

		return $this->fileObject->flock($operation, $wouldBlock);
	}

	/**
	 * Method position
	 *
	 * @param int $position [explicite description]
	 *
	 * @return int
	 */
	public function position(int $position = null): int
	{
		$this->requireOpenFile(); /* throws error on fail */

		return ($position) ? $this->fileObject->fseek($this->handle, $position) : $this->fileObject->ftell($this->handle);
	}

	/**
	 * Method flush
	 *
	 * @return bool
	 */
	public function flush(): bool
	{
		$this->requireOpenFile(); /* throws error on fail */

		return $this->fileObject->fflush();
	}

	/**
	 * Method isDirectory
	 *
	 * @return bool
	 */
	public function isDirectory(): bool
	{
		return $this->isDir();
	}

	/**
	 * Method filename
	 *
	 * @param string $suffix [explicite description]
	 *
	 * @return string
	 */
	public function filename(string $suffix = null): string
	{
		return ($suffix) ? $this->getBasename($suffix) : $this->getFilename();
	}

	/**
	 * dirname — Returns a parent directory's path
	 *
	 * @param string $path [path to file/directory]
	 * @param int $levels The number of parent directories to go up.
	 *
	 * @return string
	 */
	public function directory(): string
	{
		return Disc::resolve($this->getPath(), true);
	}

	/**
	 * filesize — Gets file size
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public function size(): int
	{
		return $this->getSize();
	}

	/**
	 * fileatime — Gets last access time of file
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return int
	 */
	public function accessTime(string $dateFormat = null) /* int|string */
	{
		return self::formatTime($this->getATime(), $dateFormat);
	}

	/**
	 * filectime — Gets inode change time of file
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return int
	 */
	public function changeTime(string $dateFormat = null) /* int|string */
	{
		return self::formatTime($this->getCTime(), $dateFormat);
	}

	/**
	 * filemtime — Gets file modification time
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return int
	 */
	public function modificationTime(string $dateFormat = null) /* int|string */
	{
		return self::formatTime($this->getMTime(), $dateFormat);
	}

	/**
	 * filegroup — Gets file group
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public function group(bool $details = false) /* array|int|false */
	{
		$id = $this->getGroup();

		return ($id && $details) ? posix_getgrgid($id) : $id;
	}

	/**
	 * fileowner — Gets file owner
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return int
	 */
	public function owner(bool $details = false) /* array|int|false */
	{
		$id = $this->getOwner();

		return ($id && $details) ? posix_getpwuid($id) : $id;
	}

	/**
	 * fileperms — Gets file permissions
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return int
	 */
	public function permissions(int $options = 0)
	{
		return ($options) ? Disc::formatPermissions($this->getPerms(), $options) : $this->getPerms();
	}

	/**
	 * Method changePermissions
	 *
	 * @param string $requiredPath [explicite description]
	 * @param int $mode [explicite description]
	 *
	 * @return bool
	 */
	public function changePermissions(int $mode): bool
	{
		return \chmod($this->getRealPath(), $mode);
	}

	/**
	 * Method changeGroup
	 *
	 * @param string $requiredPath [explicite description]
	 * @param $group [explicite description]
	 *
	 * @return bool
	 */
	public function changeGroup($group): bool
	{
		return \chgrp($this->getRealPath(), $group);
	}

	/**
	 * Method changeOwner
	 *
	 * @param string $requiredPath [explicite description]
	 * @param $user [explicite description]
	 *
	 * @return bool
	 */
	public function changeOwner($user): bool
	{
		return \chown($this->getRealPath(), $user);
	}

	/**
	 * filetype — Gets file type
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public function type() /* string|false */
	{
		return $this->getType();
	}

	/**
	 * info — Gives information about a file
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public function info(?string $option = null, $arg1 = null) /* array|false */
	{
		$info = [];

		$info += \stat($this->getRealPath());
		$info += \pathInfo($this->getRealPath());

		$info['dirname'] = Disc::resolve($info['dirname'], true);

		$info['type'] = $this->getType();

		$dateFormat = ($arg1) ? $arg1 : 'r';

		$info['atime_display'] = $this->accessTime($dateFormat);
		$info['mtime_display'] = $this->modificationTime($dateFormat);
		$info['ctime_display'] = $this->changeTime($dateFormat);

		$permissions = $this->getPerms();

		$info['permissions_display'] = Disc::formatPermissions($permissions, 3);
		$info['permissions_t'] = Disc::formatPermissions($permissions, 1);
		$info['permissions_ugw'] = Disc::formatPermissions($permissions, 2);


		$info['uid_display'] = $this->owner(true)['name'];
		$info['gid_display'] = $this->group(true)['name'];

		$info['size_display'] = Disc::formatSize($this->size());

		$info['isDirectory'] = (bool)$this->isDirectory();
		$info['isWritable'] = (bool)$this->isWritable();
		$info['isReadable'] = (bool)$this->isReadable();
		$info['isFile'] = (bool)$this->isFile();

		$info['root'] = Disc::getRoot();

		if ($option) {
			if (!in_array($option, $info)) {
				throw new FileException('Unknown option ' . $option);
			}

			$info = $info[$option];
		}

		return $info;
	}

	/**
	 * file — Reads entire file into an array
	 *
	 * @param string $path [path to file/directory]
	 * @param int $flags
	 *
	 * @return mixed
	 */
	public function asArray(int $flags = 0): array
	{
		return \file($this->getRealPath(), $flags);
	}

	/**
	 * Reads a file and writes it to the output buffer.
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public function echo(): int
	{
		return \readfile($this->getRealPath());
	}

	public function touch(): bool
	{
		return \touch($this->getRealPath());
	}

	public function move(string $name): self
	{
		return Disc::renameFile($this, $name);
	}

	public function rename(string $name): self
	{
		return self::move($name);
	}

	public function remove(): bool
	{
		return \unlink($this->getRealPath());
	}

	public function copy(string $destination): bool
	{
		Disc::autoGenMissingDirectory($destination);

		return \copy($this->getRealPath(), Disc::resolve($destination));
	}

	public function formatTime($timestamp, string $dateFormat) /* int|string */
	{
		return ($timestamp && $dateFormat) ? date($dateFormat, $timestamp) : $timestamp;
	}

	public function convertToStringPhp($input): string
	{
		$string = '';

		if (\is_array($input) || \is_object($input)) {
			$string = '<?php return ' . \str_replace(['Closure::__set_state', 'stdClass::__set_state'], '(object)', \var_export($input, true)) . ';';
		} elseif (\is_scalar($input)) {
			$string = '<?php return "' . \str_replace('"', '\"', $input) . '";';
		} else {
			throw new FileException('Unknown input type.');
		}

		return $string;
	}

	public function convertToStringJson($input, bool $pretty = false, ?int $flags = null, ?int $depth = 512): string
	{
		$flags = ($flags) ?? self::JSONFLAGS;
		$depth = ($depth) ?? 512;

		if ($pretty) {
			$flags = $flags | JSON_PRETTY_PRINT;
		}

		return json_encode($input, $flags, $depth);
	}

	public function convertToStringIni(array $array, array $parent = []): string
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
				$ini .= $this->convertToStringIni($value, $subsection);
			} else {
				//plain key->value case
				$ini .= "$key=$value" . PHP_EOL;
			}
		}

		return $ini;
	}

	public function export($data, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null): self
	{
		switch ($this->getExtension()) {
			case 'ini':
				$this->exportIni($data, $arg1);
				break;
			case 'json':
				$this->exportJson($data, $arg1, $arg2, $arg3, $arg4);
				break;
			case 'php':
				$this->exportPhp($data, $arg1);
				break;
			default:
				$this->exportContent($data, $arg1, $arg2);
		}

		return $this;
	}

	public function exportPhp($data, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		$bytes = $this->changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->convertToStringPhp($data)), $chmod);

		/* if it's cached we need to flush it out so the old one isn't loaded */
		Disc::removePhpFileFromOpcache($path);

		return $bytes;
	}

	public function exportJson($jsonObj, ?bool $pretty = false, ?int $flags = null, ?int $depth = 512, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		$pretty = ($pretty) ?? false;
		$depth = ($depth) ?? 512;

		return $this->changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->convertToStringJson($jsonObj, $pretty, $flags, $depth)), $chmod);
	}

	public function exportIni(array $array, ?int $chmod = null): int
	{
		$path = $this->getPathname();

		return $this->changeModeOnBytes($path, Disc::atomicSaveContent($path, $this->convertToStringIni($array)), $chmod);
	}

	public function exportContent($content, ?int $flags = 0, ?int $chmod = null): int
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

		return $this->changeModeOnBytes($path, $bytes, $chmod);
	}

	public function changeModeOnBytes(string $path, int $bytes, ?int $chmod): int
	{
		if ($bytes && $chmod) {
			\chmod($path, $chmod);
		}

		return $bytes;
	}

	/**
	 * Method import
	 *
	 * @param $arg1 $arg1 [explicite description]
	 * @param $arg2 $arg2 [explicite description]
	 * @param $arg3 $arg3 [explicite description]
	 *
	 * @return void
	 */
	public function import($arg1 = null, $arg2 = null, $arg3 = null)
	{
		switch ($this->getExtension()) {
			case 'ini':
				$contents = $this->importIni($arg1, $arg2);
				break;
			case 'json':
				$contents = $this->importJson($arg1, $arg2, $arg3);
				break;
			case 'php':
				$contents = $this->importPhp();
				break;
			default:
				$contents = $this->content();
		}

		return $contents;
	}

	/**
	 * Method importPhp
	 *
	 * @return void
	 */
	public function importPhp()
	{
		$path = $this->getPathname();

		return include $path;
	}

	/**
	 * Method importJson
	 *
	 * @param bool $associative [explicite description]
	 * @param int $depth [explicite description]
	 * @param int $flags [explicite description]
	 *
	 * @return void
	 */
	public function importJson(bool $associative = false, int $depth = 512, int $flags = 0)
	{
		$path = $this->getPathname();

		$associative = ($associative) ?? false;
		$depth = ($depth) ?? 512;
		$flags = ($flags) ?? 0;

		$json = json_decode(file_get_contents($path), $associative, $depth, $flags);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new FileException('JSON file "' . Disc::resolve($path, true) . '" is not valid JSON.');
		}

		return $json;
	}

	/**
	 * Method importIni
	 *
	 * @param bool $processSections [explicite description]
	 * @param int $scannerMode [explicite description]
	 *
	 * @return void
	 */
	public function importIni(bool $processSections = null, int $scannerMode = null)
	{
		$path = $this->getPathname();

		$processSections = ($processSections) ?? true;
		$scannerMode = ($scannerMode) ?? INI_SCANNER_NORMAL;

		$ini = false;

		$ini = \parse_ini_file($path, $processSections, $scannerMode);

		if (!$ini) {
			throw new FileException('INI file "' . Disc::resolve($path, true) . '" is not valid.');
		}

		return $ini;
	}

	/**
	 * Method content
	 *
	 * @return string
	 */
	public function content(): string
	{
		return \file_get_contents($this->getPathname());
	}

	/**
	 * Method requireOpenFile
	 *
	 * @return void
	 */
	protected function requireOpenFile()
	{
		if (!$this->fileObject) {
			throw new FileException('no file open');
		}
	}
} /* end class */
