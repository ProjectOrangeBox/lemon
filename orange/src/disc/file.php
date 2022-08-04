<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use SplFileObject;
use dmyers\orange\disc\exceptions\FileException;

class File extends SplFileObject
{
	public function __construct(string $path)
	{
		$path = Disc::resolve($path);

		parent::__construct($path);
	}

	public function write(string $string, ?int $length = null) /* int|false */
	{
		$length = ($length) ?? strlen($string);

		return $this->fwrite($string, $length);
	}

	public function writeLine(string $string, string $lineEnding = null)
	{
		$lineEnding = ($lineEnding) ?? chr(10);

		return $this->write($string . $lineEnding);
	}

	public function character() /* string|false */
	{
		return $this->fgetc();
	}

	public function characters(int $length) /* string|false */
	{
		return $this->fread($length);
	}

	public function line(): string
	{
		return $this->fgets();
	}

	public function readCsvRow(string $separator = ",", string $enclosure = '"', string $escape = "\\"): array
	{
		return $this->fgetcsv($separator, $enclosure, $escape);
	}

	public function writeCsvRow(array $fields, string $separator = ",", string $enclosure = "\"", string $escape = "\\", string $eol = "\n")
	{
		return $this->fputcsv($fields, $separator, $enclosure, $escape, $eol);
	}

	public function lock(int $operation, int &$wouldBlock = null): bool
	{
		return $this->flock($operation, $wouldBlock);
	}

	public function position(int $position = null): int
	{
		return ($position) ? $this->fseek($this->handle, $position) : $this->ftell($this->handle);
	}

	public function flush(): bool
	{
		return $this->fflush();
	}

	public function isDirectory(): bool
	{
		return $this->isDir();
	}

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

	public function move(string $newname): self
	{
		return Disc::rename($this, $newname);
	}

	public function rename(string $newname): self
	{
		return self::move($newname);
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
} /* end class */
