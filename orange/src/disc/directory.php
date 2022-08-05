<?php

declare(strict_types=1);

namespace dmyers\orange\disc;

use SplFileInfo;
use dmyers\orange\disc\exceptions\DirectoryException;

class Directory extends SplFileInfo
{
	public function list(string $pattern = '*', int $flags = 0): array
	{
		Disc::required($this->getPathname());

		return Disc::stripRootPath(\glob($this->getPathname() . '/' . $pattern, $flags));
	}

	public function listAll(string $pattern = '*', int $flags = 0): array
	{
		Disc::required($this->getPathname());

		return Disc::stripRootPath(self::listRecursive($this->getPathname() . '/' . $pattern, $flags));
	}

	public function remove(bool $removeDirectory = true): bool
	{
		$path = $this->getPathname();

		if (!is_dir($path)) {
			throw new DirectoryException('Directory Not Found');
		}

		self::recursiveRemove($path);

		if ($removeDirectory) {
			\rmdir($path);
		}

		return true; /* ?? */
	}

	protected function recursiveRemove(string $path)
	{
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $fileinfo) {
			if ($fileinfo->isDir()) {
				self::recursiveRemove($fileinfo->getRealPath());
			} else {
				\unlink($fileinfo->getRealPath());
			}
		}
	}

	public function copy(string $destination): bool
	{
		$source = $this->getPathname();

		if (!is_dir($source)) {
			throw new DirectoryException('Directory Not Found');
		}

		$destination = Disc::resolve($destination);

		$dir = \opendir($source);

		if (!is_dir($destination)) {
			mkdir($destination);
		}

		while (false !== ($file = \readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (\is_dir($source . '/' . $file)) {
					self::copy($source . '/' . $file, $destination . '/' . $file);
				} else {
					\copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}

		\closedir($dir);

		return true;
	}

	public function create(int $mode = 0777, bool $recursive = true): bool
	{
		$path = $this->getPathname();

		if (!\file_exists($path)) {
			$umask = \umask(0);
			$bool = \mkdir($path, $mode, $recursive);
			\umask($umask);
		} else {
			$bool = true;
		}

		return $bool;
	}

	public function listRecursive(string $pattern, int $flags = 0): array
	{
		$files = \glob($pattern, $flags);

		foreach (\glob(\dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $directory) {
			/* recursive loop */
			$files = \array_merge($files, self::listRecursive($directory . DIRECTORY_SEPARATOR . \basename($pattern), $flags));
		}

		return $files;
	}
} /* end class */
