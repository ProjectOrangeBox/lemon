<?php

declare(strict_types=1);

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
	const JSONFLAGS = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;

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
			throw new \FileSystemException(__METHOD__ . ' "' . $path . '" is not a valid directory.');
		}

		self::$rootPath = $realpath;

		/* calculate it once here */
		self::$rootLength = \strlen($realpath);

		if ($chdir) {
			\chdir(self::$rootPath);
		}

		/* default true */
		self::autoGenerateDirectories(true);
	}

	/**
	 * Method getRoot
	 *
	 * @return string
	 */
	public static function getRoot(): string
	{
		return self::$rootPath;
	}

	/**
	 * Method autoGenerateDirectories
	 *
	 * @param bool $bool [explicite description]
	 *
	 * @return void
	 */
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
	public static function resolve(string $path, bool $remove = false): string
	{
		if (!self::$rootPath) {
			throw new \FileSystemException(__METHOD__ . ' root path is not defined. Use disc::root(...).');
		}

		/* strip it if root path is already present */
		$path = (\substr($path, 0, self::$rootLength) == self::$rootPath) ? \substr($path, self::$rootLength) : $path;

		/* now resolve - stripped or added? */
		return ($remove) ? \rtrim($path, DIRECTORY_SEPARATOR) : self::$rootPath . DIRECTORY_SEPARATOR . \trim($path, DIRECTORY_SEPARATOR);
	}

	/**
	 * Method stripRootPath
	 *
	 * @param string|array $files [array of file pathes or single file path]
	 * @param bool $remove [add or remove path to passed]
	 *
	 * @return string|array
	 */
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

	/**
	 * Method required
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function required(string $path): bool
	{
		$success = \file_exists(self::resolve($path));

		if (!$success) {
			throw new \FileSystemException('No such file or directory. ' . $path);
		}

		return $success;
	}

	/**
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function require(string $requiredPath) /* mixed|bool */
	{
		self::required($requiredPath);

		return require self::resolve($requiredPath);
	}

	/**
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function requireOnce(string $requiredPath) /* mixed|bool */
	{
		self::required($requiredPath);

		return require_once self::resolve($requiredPath);
	}

	/**
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function include(string $requiredPath) /* mixed|bool */
	{
		self::required($requiredPath);

		return include self::resolve($requiredPath);
	}

	/**
	 * Method includeOnce
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return mixed
	 */
	public static function includeOnce(string $requiredPath) /* mixed|bool */
	{
		self::required($requiredPath);

		return include_once self::resolve($requiredPath);
	}

	/**
	 * Find pathnames matching a pattern
	 *
	 * @param string $pattern
	 * @param int $flags
	 * 
	 * @return array
	 */
	public static function list(string $pattern, int $flags = 0): array
	{
		self::required(dirname($pattern));

		return self::stripRootPath(\glob(self::resolve($pattern), $flags));
	}

	/**
	 * internal recursive loop for globr
	 *
	 * @param string $pattern
	 * @param int $flags
	 * 
	 * @return array
	 */
	public static function listAll(string $pattern, int $flags = 0): array
	{
		self::required(dirname($pattern));

		return self::stripRootPath(self::_listRecursive(self::resolve($pattern), $flags));
	}

	/**
	 * Returns trailing name component of path
	 *
	 * @param string $path [path to file/directory]
	 * @param string $suffix
	 * 
	 * @return string
	 */
	public static function filename(string $path, string $suffix = ''): string
	{
		return \basename($path, $suffix);
	}

	/**
	 * dirname ??? Returns a parent directory's path
	 *
	 * @param string $path [path to file/directory]
	 * @param int $levels The number of parent directories to go up.
	 * 
	 * @return string
	 */
	public static function directory(string $path, int $levels = 1): string
	{
		return \dirname($path, $levels);
	}

	/**
	 * Method isDir
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function isDirectory(string $path): bool
	{
		return \is_dir(self::resolve($path));
	}

	/**
	 * Method isWriteable
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function isWriteable(string $path): bool
	{
		return \is_writable(self::resolve($path));
	}

	/**
	 * Method isFile
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function isFile(string $path): bool
	{
		return \is_file(self::resolve($path));
	}

	/**
	 * Method isReadable
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function isReadable(string $path): bool
	{
		return \is_readable(self::resolve($path));
	}

	/**
	 * filesize ??? Gets file size
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function size(string $requiredPath, bool $formated = false) /* string|int */
	{
		self::required($requiredPath);

		$bytes = \filesize(self::resolve($requiredPath));

		if ($formated) {
			$bytes = self::bytesToString($bytes);
		}

		return $bytes;
	}

	/**
	 * fileatime ??? Gets last access time of file
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return int
	 */
	public static function accessTime(string $requiredPath, string $dateFormat = null) /* int|string */
	{
		return self::_time($requiredPath, $dateFormat, 'fileatime');
	}

	/**
	 * filectime ??? Gets inode change time of file
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return int
	 */
	public static function changeTime(string $requiredPath, string $dateFormat = null) /* int|string */
	{
		return self::_time($requiredPath, $dateFormat, 'filectime');
	}

	/**
	 * filemtime ??? Gets file modification time
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return int
	 */
	public static function modificationTime(string $requiredPath, string $dateFormat = null) /* int|string */
	{
		return self::_time($requiredPath, $dateFormat, 'filemtime');
	}

	/**
	 * filegroup ??? Gets file group
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function group(string $requiredPath, bool $details = false) /* array|int|false */
	{
		self::required($requiredPath);

		$id = \filegroup(self::resolve($requiredPath));

		return ($id && $details) ? posix_getgrgid($id) : $id;
	}

	/**
	 * fileowner ??? Gets file owner
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return int
	 */
	public static function owner(string $requiredPath, bool $details = false) /* array|int|false */
	{
		self::required($requiredPath);

		$id = \fileowner(self::resolve($requiredPath));

		return ($id && $details) ? posix_getpwuid($id) : $id;
	}

	/**
	 * fileperms ??? Gets file permissions
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return int
	 */
	public static function permissions(string $requiredPath, int $options = 0)
	{
		self::required($requiredPath);

		$success = \fileperms(self::resolve($requiredPath));

		if ($options) {
			$success = self::permissionsFormatted($success, $options);
		}

		return $success;
	}

	/**
	 * Method changeMode
	 *
	 * @param string $path [path to file/directory]
	 * @param int $mode [explicite description]
	 *
	 * @return bool
	 */
	public static function changePermissions(string $requiredPath, int $mode): bool
	{
		self::required($requiredPath);

		return \chmod(self::resolve($requiredPath), $mode);
	}

	/**
	 * Method changeFileGroup
	 *
	 * @param string $path [path to file/directory]
	 * @param $group $group [explicite description]
	 *
	 * @return bool
	 */
	public static function changeGroup(string $requiredPath, $group): bool
	{
		self::required($requiredPath);

		return \chgrp(self::resolve($requiredPath), $group);
	}

	/**
	 * Method changeFileOwner
	 *
	 * @param string $path [path to file/directory]
	 * @param $user $user [explicite description]
	 *
	 * @return bool
	 */
	public static function changeOwner(string $requiredPath, $user): bool
	{
		self::required($requiredPath);

		return \chown(self::resolve($requiredPath), $user);
	}

	/**
	 * fileinode ??? Gets file inode
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function inode(string $requiredPath) /* int|false */
	{
		self::required($requiredPath);

		return \fileinode(self::resolve($requiredPath));
	}

	/**
	 * filetype ??? Gets file type
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function type(string $requiredPath) /* string|false */
	{
		self::required($requiredPath);

		return \filetype(self::resolve($requiredPath));
	}

	/**
	 * info ??? Gives information about a file
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function info(string $requiredPath = '/', ?string $option = null, $arg1 = null) /* array|false */
	{
		self::required($requiredPath);

		$info = \stat(self::resolve($requiredPath));
		$info += \pathInfo(self::resolve($requiredPath));


		$info['dirname'] = self::resolve($info['dirname'], true);
		$info['type'] = \filetype(self::resolve($requiredPath));

		$dateFormat = ($arg1) ? $arg1 : 'r';
		$info['atime_display'] = date($dateFormat, $info['atime']);
		$info['mtime_display'] = date($dateFormat, $info['mtime']);
		$info['ctime_display'] = date($dateFormat, $info['ctime']);

		$info['permissions_display'] = self::permissions($requiredPath, 3);
		$info['permissions_t'] = self::permissions($requiredPath, 1);
		$info['permissions_ugw'] = self::permissions($requiredPath, 2);

		$info['uid_display'] = self::owner($requiredPath, true)['name'];
		$info['gid_display'] = self::group($requiredPath, true)['name'];

		$info['size_display'] = self::size($requiredPath, true);

		$info['isDirectory'] = (bool)self::isDirectory($requiredPath);
		$info['isWritable'] = (bool)self::isWriteable($requiredPath);
		$info['isReadable'] = (bool)self::isReadable($requiredPath);
		$info['isFile'] = (bool)self::isFile($requiredPath);

		$info['root'] = self::$rootPath;

		if ($option) {
			if (!in_array($option, $info)) {
				throw new FileSystemException('Unknown option ' . $option);
			}

			$info = $info[$option];
		}

		return $info;
	}

	/**
	 * Returns information about a file path
	 *
	 * @param string $path [path to file/directory]
	 * @param int $options
	 * 
	 * @return mixed
	 */
	public static function pathInfo(string $path, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME) /* array|string */
	{
		$pathinfo = \pathinfo($path, $options);

		/* resolve path */
		if (\is_array($pathinfo) && isset($pathinfo['dirname'])) {
			$pathinfo['dirname'] = self::resolve($pathinfo['dirname'], true);
		} elseif ($options == PATHINFO_DIRNAME) {
			$pathinfo = self::resolve($pathinfo, true);
		}

		return $pathinfo;
	}


	/**
	 * Method fileExists
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return bool
	 */
	public static function exists(string $path): bool
	{
		return \file_exists(self::resolve($path));
	}

	/**
	 * file ??? Reads entire file into an array
	 *
	 * @param string $path [path to file/directory]
	 * @param int $flags
	 * 
	 * @return mixed
	 */
	public static function readAsArray(string $requiredPath, int $flags = 0): array
	{
		self::required($requiredPath);

		return \file(self::resolve($requiredPath), $flags);
	}

	/**
	 * Reads a file and writes it to the output buffer.
	 *
	 * @param string $path [path to file/directory]
	 * 
	 * @return mixed
	 */
	public static function echo(string $requiredPath): int
	{
		self::required($requiredPath);

		return \readfile(self::resolve($requiredPath));
	}

	/**
	 * fopen ??? Opens file or URL
	 *
	 * @param string $path [path to file/directory]
	 * @param string $mode
	 * 
	 * @return resource
	 */
	public static function open(string $path, string $mode = 'r') /* resource|false */
	{
		if (in_array($mode, ['r', 'r+'])) {
			self::required($path);
		} else {
			self::autoGenMissingDirectory($path);
		}

		return \fopen(self::resolve($path), $mode);
	}

	/**
	 * Method create
	 *
	 * @param string $path [explicite description]
	 * @param string $mode [explicite description]
	 *
	 * @return void
	 */
	public static function create(string $path, string $mode = 'w') /* resource|false */
	{
		return self::open($path, $mode);
	}

	protected static function requiredStream($handle): void
	{
		if (\get_resource_type($handle) != 'stream') {
			throw new FileSystemStreamException();
		}
	}

	/**
	 * Method fclose
	 *
	 * @param $handle $handle [explicite description]
	 *
	 * @return void
	 */
	public static function close($handle): bool
	{
		self::requiredStream($handle);

		return \fclose($handle);
	}

	/**
	 * Method fwrite
	 *
	 * @param $handle $handle [explicite description]
	 * @param string $string [explicite description]
	 * @param int $length [explicite description]
	 *
	 * @return int
	 */
	public static function write($handle, string $string, ?int $length = null) /* int|false */
	{
		self::requiredStream($handle);

		$length = ($length) ?? strlen($string);

		return \fwrite($handle, $string, $length);
	}

	/**
	 * Method feof
	 *
	 * @param $stream $stream [explicite description]
	 *
	 * @return bool
	 */
	public static function eof($handle): bool
	{
		self::requiredStream($handle);

		return \feof($handle);
	}

	/**
	 * Method fgetc
	 *
	 * @param $stream $stream [explicite description]
	 *
	 * @return void
	 */
	public static function character($handle)
	{
		self::requiredStream($handle);

		return \fgetc($handle);
	}

	/**
	 * Method getCharacters
	 *
	 * @param $handle $handle [explicite description]
	 * @param int $length [explicite description]
	 *
	 * @return void
	 */
	public static function characters($handle, $length = false)
	{
		self::requiredStream($handle);

		return ($length > 0) ? \fread($handle, $length) : \fgets($handle);
	}

	/**
	 * Method fgetcsv
	 *
	 * @param $stream $stream [explicite description]
	 * @param int $length [explicite description]
	 * @param string $separator [explicite description]
	 * @param " $enclosure [explicite description]
	 * @param string $escape [explicite description]
	 *
	 * @return array
	 */
	public static function csvRow($handle, int $length = 0, string $separator = ",", string $enclosure = '"', string $escape = "\\"): array
	{
		self::requiredStream($handle);

		return \fgetcsv($handle, $length, $separator, $enclosure, $escape);
	}

	/**
	 * Method flock
	 *
	 * @param $stream $stream [explicite description]
	 * @param int $operation [explicite description]
	 * @param int $wouldBlock [explicite description]
	 *
	 * @return bool
	 */
	public static function lock($handle, int $operation, int &$wouldBlock = null): bool
	{
		self::requiredStream($handle);

		return \flock($handle, $operation, $wouldBlock);
	}

	/**
	 * Method ftell
	 *
	 * @param $stream $stream [explicite description]
	 *
	 * @return int
	 */
	public static function position($handle): int
	{
		self::requiredStream($handle);

		return \ftell($handle);
	}

	public static function createMissingDirectory(string $path, int $mode = 0777): bool
	{
		return self::createDirectory(dirname($path), $mode, true);
	}

	/**
	 * mkdir ??? Makes directory
	 *
	 * @param string $path
	 * @param int $mode
	 * @param bool $recursive
	 * @return bool
	 */
	public static function createDirectory(string $path, int $mode = 0777, bool $recursive = true): bool
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

	/**
	 * Method touch
	 *
	 * @param string $path [explicite description]
	 *
	 * @return bool
	 */
	public static function touch(string $path): bool
	{
		self::autoGenMissingDirectory($path);

		return \touch(self::resolve($path));
	}

	/**
	 * rename ??? Renames a file or directory
	 *
	 * @param string $oldname
	 * @param string $newname
	 * @return bool
	 */
	public static function move(string $oldname, string $newname): bool
	{
		self::required($oldname);

		self::autoGenMissingDirectory($newname);

		return \rename(self::resolve($oldname), self::resolve($newname));
	}

	public static function rename(string $oldname, string $newname): bool
	{
		return self::move($oldname, $newname);
	}

	/**
	 * Method remove
	 *
	 * @param string $path [explicite description]
	 *
	 * @return bool
	 */
	public static function remove(string $path): bool
	{
		/* exists is tested in each function */
		return self::isDirectory($path) ? self::removeDirectory($path) : self::removeFile($path);
	}

	/**
	 * Method rmdirRecursive
	 *
	 * @param string $path [explicite description]
	 *
	 * @return bool
	 */
	public static function removeDirectory(string $path): bool
	{
		$success = false;

		if (self::exists($path)) {
			$path = self::resolve($path);

			$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

			foreach ($files as $fileinfo) {
				if ($fileinfo->isDir()) {
					self::removeDirectory($fileinfo->getRealPath());
				} else {
					\unlink($fileinfo->getRealPath());
				}
			}

			$success = \rmdir($path);
		}

		return $success;
	}

	public static function removeFile(string $path): bool
	{
		return self::exists($path) ? \unlink(self::resolve($path)) : false;
	}

	/**
	 * copy ??? Copies file
	 *
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	public static function copy(string $source, string $destination): bool
	{
		return (self::isDirectory($source)) ? self::copyDirectory($source, $destination) : self::copyFile($source, $destination);
	}

	/**
	 * Method copyFile
	 *
	 * @param string $source [explicite description]
	 * @param string $dest [explicite description]
	 *
	 * @return bool
	 */
	public static function copyFile(string $source, string $destination): bool
	{
		self::required($source);

		self::autoGenMissingDirectory($destination);

		return \copy(self::resolve($source), self::resolve($destination));
	}

	/**
	 * Method copyFolder
	 *
	 * @param string $source [explicite description]
	 * @param string $dest [explicite description]
	 *
	 * @return bool
	 */
	public static function copyDirectory(string $source, string $destination): bool
	{
		self::required($source);

		$source = self::resolve($source);
		$destination = self::resolve($destination);

		$dir = \opendir($source);

		if (!is_dir($destination)) {
			mkdir($destination);
		}

		while (false !== ($file = \readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (\is_dir($source . '/' . $file)) {
					self::copyDirectory($source . '/' . $file, $destination . '/' . $file);
				} else {
					\copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}

		\closedir($dir);

		return true;
	}

	/**
	 * Method varPhp
	 *
	 * @param $input $input [explicite description]
	 *
	 * @return string
	 */
	public static function varPhp($input): string
	{
		$string = '';

		if (\is_array($input) || \is_object($input)) {
			$string = '<?php return ' . \str_replace(['Closure::__set_state', 'stdClass::__set_state'], '(object)', \var_export($input, true)) . ';';
		} elseif (\is_scalar($input)) {
			$string = '<?php return "' . \str_replace('"', '\"', $input) . '";';
		} else {
			throw new \FileSystemException('Unknown input type.');
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
	public static function varJson($input, bool $pretty = false, ?int $flags = null, ?int $depth = 512): string
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
	public static function varIni(array $array, array $parent = []): string
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
				$ini .= self::varIni($value, $subsection);
			} else {
				//plain key->value case
				$ini .= "$key=$value" . PHP_EOL;
			}
		}

		return $ini;
	}


	/**
	 * Reads entire file into a string
	 *
	 * @param string $path
	 * @return string
	 */
	public static function loadContent(string $requiredPath): string
	{
		self::required($requiredPath);

		return \file_get_contents(self::resolve($requiredPath));
	}

	/**
	 * New
	 *
	 * var_import_file ??? import a var_export_file(...) file
	 *
	 * @param string $path
	 * @return mixed
	 * @throws Exception
	 */
	public static function loadPhp(string $requiredPath)
	{
		self::required($requiredPath);

		return include self::resolve($requiredPath);
	}

	/**
	 * Method getJson
	 *
	 * @param string $path [explicite description]
	 *
	 * @return void
	 */
	public static function loadJson(string $requiredPath)
	{
		self::required($requiredPath);

		$json = json_decode(self::loadContent($requiredPath), true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \FileSystemException('JSON file "' . $requiredPath . '" is not valid JSON.');
		}

		return $json;
	}

	/**
	 * parse_ini_file ??? Parse a configuration file
	 *
	 * @param string $path
	 * @param bool $process_sections create a multidimensional array
	 * @param int $scanner_mode INI_SCANNER_NORMAL, INI_SCANNER_RAW, INI_SCANNER_TYPED
	 * @return mixed
	 */
	public static function loadIni(string $requiredPath, bool $processSections = false, int $scannerMode = INI_SCANNER_NORMAL) /* mixed */
	{
		self::required($requiredPath);

		$ini = false;

		$ini = \parse_ini_file(self::resolve($requiredPath), $processSections, $scannerMode);

		if (!$ini) {
			throw new \FileSystemException('INI file "' . $requiredPath . '" is not valid.');
		}

		return $ini;
	}

	/**
	 * Method get
	 *
	 * @param string $path [explicite description]
	 * @param $arg1 $arg1 [explicite description]
	 * @param $arg2 $arg2 [explicite description]
	 *
	 * @return void
	 */
	public static function load(string $path, $arg1 = null, $arg2 = null) /* mixed */
	{
		$output = null;

		switch (\pathinfo($path, PATHINFO_EXTENSION)) {
			case 'ini':
				$arg1 = ($arg1) ?? true;
				$arg2 = ($arg2) ?? INI_SCANNER_TYPED;

				$output = self::loadIni($path, $arg1, $arg2);
				break;
			case 'json':
				$output = self::loadJson($path);
				break;
			case 'php':
				$output = self::loadPhp($path);
				break;
			default:
				$output = self::loadContent($path);
		}

		return $output;
	}


	/**
	 * file_put_contents ??? Write data to a file
	 *
	 * This should have thrown an error before not being able to write a file_exists
	 * This writes the file in a atomic fashion unless you use $flags
	 *
	 * @param string $path
	 * @param mixed $content
	 * @param int $flags
	 * @return mixed returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	public static function saveContent(string $path, $content, ?int $flags = 0, ?int $chmod = null): int
	{
		$bytes = 0;

		/* if they aren't using any special flags just make it atomic that way locks aren't needed or partially written files aren't read */
		if ($flags > 0) {
			self::autoGenMissingDirectory($path);

			$bytes = \file_put_contents(self::resolve($path), $content, $flags);
		} else {
			/* if no flags provided do it the atomic way */
			$bytes = self::atomicSaveContent($path, $content);
		}

		return self::changeModeOnBytes($path, $bytes, $chmod);
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
	public static function savePhp(string $path, $data, ?int $chmod = null): int
	{
		$bytes = self::changeModeOnBytes($path, self::atomicSaveContent($path, self::varPhp($data)), $chmod);

		/* if it's cached we need to flush it out so the old one isn't loaded */
		self::removePhpFileFromOpcache($path);

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
	public static function saveJson(string $path, $jsonObj, bool $pretty = false, ?int $flags = null, ?int $depth = 512, ?int $chmod = null): int
	{
		return self::changeModeOnBytes($path, self::atomicSaveContent($path, self::varJson($jsonObj, $pretty, $flags, $depth)), $chmod);
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
	public static function saveIni(string $path, array $array, ?int $chmod = null): int
	{
		return self::changeModeOnBytes($path, self::atomicSaveContent($path, self::varIni($array)), $chmod);
	}

	/**
	 * Method put
	 *
	 * @param string $path [explicite description]
	 * @param $input $input [explicite description]
	 * @param int $chmod [explicite description]
	 * @param $arg1 $arg1 [explicite description]
	 *
	 * @return int
	 */
	public static function save(string $path, $input, int $chmod = null, $arg1 = null): int
	{
		switch (\pathinfo($path, PATHINFO_EXTENSION)) {
			case 'ini':
				$bytes = self::saveIni($path, (array)$input, $chmod);
				break;
			case 'json':
				$arg1 = ($arg1) ?? false;
				$bytes = self::saveJson($path, $input, $arg1, null, null, $chmod);
				break;
			case 'php':
				$bytes = self::savePhp($path, $input, $chmod);
				break;
			default:
				$bytes = self::saveContent($path, $input, (int)$arg1, $chmod);
		}

		return $bytes;
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
			throw new \FileSystemException($directory . ' is not writable.');
		}

		/* create a temporary file with unique file name and prefix */
		$temporaryFile = \tempnam($directory, 'afpc_');

		/* did we get a temporary filename */
		if ($temporaryFile === false) {
			throw new \FileSystemException('Could not create temporary file ' . $temporaryFile . '.');
		}

		/* write to the temporary file */
		$bytes = \file_put_contents($temporaryFile, $content, LOCK_EX);

		/* did we write anything? */
		if ($bytes === false) {
			throw new \FileSystemException('No bytes written by file_put_contents');
		}

		/* move it into place - this is the atomic function */
		if (\rename($temporaryFile, $path) === false) {
			throw new \FileSystemException('Could not rename temporary file ' . $temporaryFile . ' ' . $path . '.');
		}

		/* return the number of bytes written */
		return $bytes;
	}

	/**
	 * Method changeModeOnBytes
	 *
	 * @param string $path [path to file/directory]
	 * @param int $bytes [bytes written]
	 * @param ?int $chmod [explicite description]
	 *
	 * @return int
	 */
	public static function changeModeOnBytes(string $path, int $bytes, ?int $chmod): int
	{
		if ($bytes && $chmod) {
			self::changePermissions($path, $chmod);
		}

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

	/**
	 * Method object
	 *
	 * @param string $path [path to file/directory]
	 *
	 * @return SplFileObject
	 */
	public static function object(string $path): SplFileObject
	{
		return new SplFileObject(self::resolve($path));
	}

	/**
	 * Method sizeFormatted
	 *
	 * @param int $bytes [explicite description]
	 *
	 * @return string
	 */
	public static function bytesToString(int $bytes): string
	{
		if ($bytes >= 1073741824) {
			$fileSize = round($bytes / 1024 / 1024 / 1024, 1) . 'GB';
		} elseif ($bytes >= 1048576) {
			$fileSize = round($bytes / 1024 / 1024, 1) . 'MB';
		} elseif ($bytes >= 1024) {
			$fileSize = round($bytes / 1024, 1) . 'KB';
		} else {
			$fileSize = $bytes . ' bytes';
		}

		return $fileSize;
	}

	/**
	 * Method permissionsFormatted
	 *
	 * @param int $mode [explicite description]
	 * @param int $option [explicite description]
	 *
	 * @return string
	 */
	public static function permissionsFormatted(int $mode, int $option = 3): string
	{
		$info = '';

		if (1 & $option) {
			switch ($mode & 0xF000) {
				case 0xC000: // socket
					$info = 's';
					break;
				case 0xA000: // symbolic link
					$info = 'l';
					break;
				case 0x8000: // regular
					$info = 'r';
					break;
				case 0x6000: // block special
					$info = 'b';
					break;
				case 0x4000: // directory
					$info = 'd';
					break;
				case 0x2000: // character special
					$info = 'c';
					break;
				case 0x1000: // FIFO pipe
					$info = 'p';
					break;
				default: // unknown
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

	protected static function autoGenMissingDirectory(string $requiredPath)
	{
		if (self::$autoGenerateDirectories) {
			self::createMissingDirectory($requiredPath);
		}
	}

	/**
	 * Method _time
	 *
	 * @param string $requiredPath [explicite description]
	 * @param string $dateFormat [explicite description]
	 * @param string $function [explicite description]
	 *
	 * @return void
	 */
	protected static function _time(string $requiredPath, string $dateFormat, string $function) /* int|string */
	{
		self::required($requiredPath);

		$timestamp = $function(self::resolve($requiredPath));

		return ($timestamp && $dateFormat) ? date($dateFormat, $timestamp) : $timestamp;
	}

	/**
	 * Method _listRecursive
	 *
	 * @param string $pattern [explicite description]
	 * @param int $flags [explicite description]
	 *
	 * @return array
	 */
	protected static function _listRecursive(string $pattern, int $flags = 0): array
	{
		$files = \glob($pattern, $flags);

		foreach (\glob(\dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $directory) {
			/* recursive loop */
			$files = \array_merge($files, self::_listRecursive($directory . DIRECTORY_SEPARATOR . \basename($pattern), $flags));
		}

		return $files;
	}
} /* end class */

class FileSystemException extends \Exception
{
}

class FileSystemStreamException extends \FileSystemException
{
}
