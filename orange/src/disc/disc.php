<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use dmyers\orange\disc\File;
use dmyers\orange\disc\Directory;
use dmyers\orange\disc\exceptions\DiscException;

/**
 * File System Functions
 *
 * File System Abstraction which automatically
 * works in a given root path
 *
 * Can be added with composer by adding a composer.json file with:
 *
 *"autoload": {
 *   "files": ["src/Disc.php"]
 * }
 */
class Disc
{
	protected static $rootPath = '';
	protected static $rootLength = 0;
	protected static $autoGenerateDirectories = true;

	/**
	 * set application root directory
	 *
	 * @param string $path [path to root directory]
	 * @return void
	 */
	public static function root(string $path, bool $chdir = true): void
	{
		/* Returns canonicalized absolute pathname */
		$realpath = \realpath($path);

		if (!$realpath) {
			throw new discException('"' . $path . '" is not a valid directory.');
		}

		/* save it */
		self::$rootPath = $realpath;

		/* calculate it once here */
		self::$rootLength = \strlen($realpath);

		/* change directory to it */
		if ($chdir) {
			\chdir(self::$rootPath);
		}

		/* default true they can change it to false if they need to with the autoGenerateDirectories function */
		self::autoGenerateDirectories(true);
	}

	public static function getRoot(): string
	{
		return self::$rootPath;
	}

	public static function autoGenerateDirectories(bool $bool = true): void
	{
		self::$autoGenerateDirectories = $bool;
	}

	/**
	 * Format a given path so it's based on the applications root folder __ROOT__.
	 *
	 * Either add or remove __ROOT__ from path
	 *
	 * @param string $path [path to file/directory]
	 * @param bool $remove false [remove the root path]
	 *
	 * @return string
	 */
	public static function resolve(string $path, bool $remove = false, bool $required = false): string
	{
		if (empty(self::$rootPath)) {
			throw new discException(__METHOD__ . ' root path is not defined. Use disc::root(...).');
		}

		/* strip it if root path is already present */
		$path = (\substr($path, 0, self::$rootLength) == self::$rootPath) ? \substr($path, self::$rootLength) : $path;

		$short = DIRECTORY_SEPARATOR . \trim($path, DIRECTORY_SEPARATOR);
		$long = self::$rootPath . $short;

		if ($required && !\file_exists($long)) {
			throw new discException('No such file or directory. ' . $short);
		}

		/* now resolve - stripped or added? */
		return ($remove) ? $short : $long;
	}

	public static function fileRequired(string $path): void
	{
		$path = self::resolve($path);

		if (!\file_exists($path) && !\is_file($path)) {
			throw new discException('No such file. ' . self::resolve($path, true));
		}
	}

	public static function directoryRequired(string $path): void
	{
		$path = self::resolve($path);

		if (!\file_exists($path) && !\is_dir($path)) {
			throw new discException('No such directory. ' . self::resolve($path, true));
		}
	}

	public static function stripRootPath($files, bool $remove = true)
	{
		/* strip the root path */
		if (is_array($files)) {
			foreach ($files as $index => $file) {
				$files[$index] = self::resolve($file, $remove);
			}
		} else {
			$files = self::resolve($files, $remove);
		}

		return $files;
	}

	public static function exists(string $path): bool
	{
		return \file_exists(self::resolve($path));
	}

	public static function require(string $requiredPath) /* mixed|bool */
	{
		return require self::resolve($requiredPath, false, true);
	}

	public static function requireOnce(string $requiredPath) /* mixed|bool */
	{
		return require_once self::resolve($requiredPath, false, true);
	}

	public static function include(string $requiredPath) /* mixed|bool */
	{
		return include self::resolve($requiredPath, false, true);
	}

	public static function includeOnce(string $requiredPath) /* mixed|bool */
	{
		return include_once self::resolve($requiredPath, false, true);
	}

	public static function directory(string $path): Directory
	{
		$path = self::resolve($path);

		if (is_file($path)) {
			throw new discException(self::resolve($path, true) . ' is a File.');
		}

		return new Directory($path);
	}

	public static function file(string $path): File
	{
		$path = self::resolve($path);

		if (is_dir($path)) {
			throw new discException(self::resolve($path, true) . ' is a Directory.');
		}

		return new File($path);
	}

	public static function formatSize(int $bytes): string
	{
		$i = floor(log($bytes, 1024));

		return round($bytes / pow(1024, $i), [0, 0, 2, 2, 3][$i]) . ['B', 'kB', 'MB', 'GB', 'TB'][$i];
	}

	public static function formatPermissions(int $mode, int $option = 3): string
	{
		$info = '';

		if (1 & $option) {
			switch ($mode & 0xF000) {
					// socket
				case 0xC000:
					$info = 's';
					break;
					// symbolic link
				case 0xA000:
					$info = 'l';
					break;
					// regular
				case 0x8000:
					$info = 'r';
					break;
					// block special
				case 0x6000:
					$info = 'b';
					break;
					// directory
				case 0x4000:
					$info = 'd';
					break;
					// character special
				case 0x2000:
					$info = 'c';
					break;
					// FIFO pipe
				case 0x1000:
					$info = 'p';
					break;
					// unknown
				default:
					$info = 'u';
			}
		}

		if (2 & $option) {
			// Owner
			$info .= (($mode & 0x0100) ? 'r' : '-');
			$info .= (($mode & 0x0080) ? 'w' : '-');
			$info .= (($mode & 0x0040) ? (($mode & 0x0800) ? 's' : 'x') : (($mode & 0x0800) ? 'S' : '-'));

			// Group
			$info .= (($mode & 0x0020) ? 'r' : '-');
			$info .= (($mode & 0x0010) ? 'w' : '-');
			$info .= (($mode & 0x0008) ? (($mode & 0x0400) ? 's' : 'x') : (($mode & 0x0400) ? 'S' : '-'));

			// World
			$info .= (($mode & 0x0004) ? 'r' : '-');
			$info .= (($mode & 0x0002) ? 'w' : '-');
			$info .= (($mode & 0x0001) ? (($mode & 0x0200) ? 't' : 'x') : (($mode & 0x0200) ? 'T' : '-'));
		}

		return $info;
	}

	public static function makeDirectory(string $path, int $mode = 0777, bool $recursive = true): bool
	{
		$path = self::resolve($path);

		if (!\file_exists($path)) {
			$umask = \umask(0);
			$bool = \mkdir($path, $mode, $recursive);
			\umask($umask);
		} else {
			$bool = true;
		}

		return $bool;
	}

	public static function autoGenMissingDirectory(string $requiredPath)
	{
		if (self::$autoGenerateDirectories) {
			self::makeDirectory(dirname($requiredPath));
		}
	}

	public static function renameFile(File $fileObj, string $newname): File
	{
		$newPath = self::resolve($newname);

		self::autoGenMissingDirectory($newPath);

		$oldPath = $fileObj->getRealPath();

		$fileObj = null; /* close */

		\rename($oldPath, $newPath);

		return self::file($newname);
	}

	public static function renameDirectory(Directory $dirObj, string $newname): Directory
	{
		$oldPath = $dirObj->getRealPath();

		$dirObj = null; /* close */

		$newPath = self::resolve($newname);

		\rename($oldPath, $newPath);

		return self::directory($newPath);
	}

	/**
	 * New (but used automatically by file_put_contents when no flags are used)
	 *
	 * atomicFilePutContents - atomic file_put_contents
	 *
	 * @param string $path
	 * @param mixed $content
	 * @return int returns the number of bytes that were written to the file.
	 */
	public static function atomicSaveContent(string $path, $content): int
	{
		/* create absolute path */
		$path = self::resolve($path);

		self::autoGenMissingDirectory($path);

		/* get the path where you want to save this file so we can put our file in the same directory */
		$directory = \dirname($path);

		/* is this directory writeable */
		if (!is_writable($directory)) {
			throw new discException($directory . ' is not writable.');
		}

		/* create a temporary file with unique file name and prefix */
		$temporaryFile = \tempnam($directory, 'afpc_');

		/* did we get a temporary filename */
		if ($temporaryFile === false) {
			throw new discException('Could not create temporary file ' . $temporaryFile . '.');
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($temporaryFile, $content, LOCK_EX);

		/* did we write anything? */
		if ($bytes === false) {
			throw new discException('No bytes written by file_put_contents');
		}

		/* move it into place - this is the atomic function */
		if (\rename($temporaryFile, $path) === false) {
			throw new discException('Could not rename temporary file ' . $temporaryFile . ' ' . $path . '.');
		}

		/* return the number of bytes written */
		return $bytes;
	}

	/**
	 * Method removePhpFileFromOpcache
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function removePhpFileFromOpcache(string $path): bool
	{
		return (\function_exists('opcache_invalidate')) ? \opcache_invalidate(self::resolve($path), true) : true;
	}
} /* end class */
